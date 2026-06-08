<?php
require_once 'db.php';

$search = $_GET['search'] ?? '';
$company = $_GET['company'] ?? '';

$query = "SELECT r.id, r.empleado_id, r.hora_entrada AS entrada, r.hora_salida AS salida, u.full_name, u.company, r.entrada_estacion, r.salida_estacion, u.hora_entrada AS hora_esperada
          FROM registros r 
          JOIN users u ON r.empleado_id = u.employee_id 
          WHERE DATE(r.hora_entrada) = CURDATE()";

$params = [];
$types = "";

if ($search) {
    $query .= " AND (u.full_name LIKE ? OR r.empleado_id LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ss";
}
if ($company) {
    $query .= " AND u.company = ?";
    $params[] = $company;
    $types .= "s";
}

$query .= " ORDER BY entrada DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while ($row = $result->fetch_assoc()) {
    $puntualidad = 'A Tiempo';
    $companyLower = strtolower(trim($row['company'] ?? ''));
    if ($companyLower === 'la casita') {
        if ($row['entrada']) {
            if ($row['salida']) {
                $horas_trabajadas = (strtotime($row['salida']) - strtotime($row['entrada'])) / 3600;
                if ($horas_trabajadas < 7.75) {
                    if ($horas_trabajadas < 7.0) {
                        $puntualidad = 'Falta';
                    } else {
                        $puntualidad = 'Retardo';
                    }
                }
            } else {
                $dia_entrada = date('Y-m-d', strtotime($row['entrada']));
                if ($dia_entrada === date('Y-m-d')) {
                    $puntualidad = 'A Tiempo';
                } else {
                    $puntualidad = 'Falta';
                }
            }
        }
    } else if ($row['entrada']) {
        $hora_entrada_real = strtotime($row['entrada']);
        $hora_esperada_str = $row['hora_esperada'] ?? '08:00:00';
        $hora_esperada_ts = strtotime(date('Y-m-d ', $hora_entrada_real) . $hora_esperada_str);
        
        $diff_segundos = $hora_entrada_real - $hora_esperada_ts;
        $diff_minutos = $diff_segundos / 60;

        if ($diff_minutos > 16) $puntualidad = 'Falta';
        else if ($diff_minutos > 6) $puntualidad = 'Retardo';
    }
    $row['puntualidad'] = $puntualidad;
    $data[] = $row;
}

if (isset($_GET['mode']) && $_GET['mode'] === 'dashboard') {
    // Calcular retardos de la semana
    $week_q = "SELECT r.hora_entrada, r.hora_salida, u.hora_entrada AS hora_esperada, u.company 
               FROM registros r 
               JOIN users u ON r.empleado_id = u.employee_id 
               WHERE r.hora_entrada >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    $week_res = $conn->query($week_q);
    $week_delays = 0;
    while($row_w = $week_res->fetch_assoc()) {
        $companyLower = strtolower(trim($row_w['company'] ?? ''));
        if ($companyLower === 'la casita') {
            if ($row_w['hora_salida']) {
                $horas_trabajadas = (strtotime($row_w['hora_salida']) - strtotime($row_w['hora_entrada'])) / 3600;
                if ($horas_trabajadas < 7.75 && $horas_trabajadas >= 7.0) {
                    $week_delays++;
                }
            }
        } else {
            $real_w = strtotime($row_w['hora_entrada']);
            $esp_w = $row_w['hora_esperada'] ?? '08:00:00';
            $esp_ts_w = strtotime(date('Y-m-d ', $real_w) . $esp_w);
            if (($real_w - $esp_ts_w) / 60 > 6) $week_delays++;
        }
    }

    echo json_encode([
        "today_count" => count($data),
        "week_delays" => $week_delays,
        "recent" => array_slice($data, 0, 5)
    ]);
} else {
    echo json_encode($data);
}
?>
