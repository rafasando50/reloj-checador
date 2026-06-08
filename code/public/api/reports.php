<?php
require_once 'db.php';

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$search = $_GET['search'] ?? '';
$company = $_GET['company'] ?? '';

if (!$start || !$end) {
    $start = date('Y-m-01');
    $end = date('Y-m-d');
}

$query = "SELECT r.id, r.empleado_id, r.hora_entrada AS entrada, r.hora_salida AS salida, u.full_name, u.company, r.entrada_estacion, r.salida_estacion, u.hora_entrada AS hora_esperada 
          FROM registros r 
          LEFT JOIN users u ON r.empleado_id = u.employee_id 
          WHERE r.hora_entrada >= ? AND r.hora_entrada <= ?";

$params = [$start . ' 00:00:00', $end . ' 23:59:59'];
$types = "ss";

if ($search) {
    $query .= " AND (u.full_name LIKE ? OR u.employee_id LIKE ?)";
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
$stmt->bind_param($types, ...$params);
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

echo json_encode($data);
?>
