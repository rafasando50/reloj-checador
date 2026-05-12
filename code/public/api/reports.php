<?php
require_once 'db.php';

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$empId = $_GET['employee_id'] ?? '';

if (!$start || !$end) {
    $start = date('Y-m-01');
    $end = date('Y-m-d');
}

$query = "SELECT r.id, r.empleado_id, r.hora_entrada AS entrada, r.hora_salida AS salida, u.full_name 
          FROM registros r 
          LEFT JOIN users u ON r.empleado_id = u.employee_id 
          WHERE r.hora_entrada >= ? AND r.hora_entrada <= ?";

$params = [$start . ' 00:00:00', $end . ' 23:59:59'];
$types = "ss";

if ($empId) {
    $query .= " AND r.empleado_id = ?";
    $params[] = $empId;
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
    if ($row['entrada']) {
        $hora = strtotime($row['entrada']);
        $mins = (int)date('H', $hora) * 60 + (int)date('i', $hora);
        if ($mins > (8 * 60 + 16)) $puntualidad = 'Falta';
        else if ($mins > (8 * 60 + 6)) $puntualidad = 'Retardo';
    }
    $row['puntualidad'] = $puntualidad;
    $data[] = $row;
}

echo json_encode($data);
?>
