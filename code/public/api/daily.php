<?php
require_once 'db.php';

$empId = $_GET['employee_id'] ?? '';
$company = $_GET['company'] ?? '';

$query = "SELECT r.id, r.empleado_id, r.hora_entrada AS entrada, r.hora_salida AS salida, u.full_name, u.company 
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
