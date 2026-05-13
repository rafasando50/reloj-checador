<?php
require_once 'db.php';

$empId = $_GET['employee_id'] ?? '';
$company = $_GET['company'] ?? '';

$query = "SELECT r.id, r.empleado_id, r.hora_entrada AS entrada, r.hora_salida AS salida, u.full_name, u.company, r.entrada_estacion, r.salida_estacion, u.hora_entrada AS hora_esperada
          FROM registros r 
          JOIN users u ON r.empleado_id = u.employee_id 
          WHERE DATE(r.hora_entrada) = CURDATE()";

$params = [];
$types = "";

if ($empId) {
    $query .= " AND r.empleado_id = ?";
    $params[] = $empId;
    $types .= "s";
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
    if ($row['entrada']) {
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
