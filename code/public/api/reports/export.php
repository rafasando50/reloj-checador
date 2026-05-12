<?php
require_once '../db.php';

$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$empId = $_GET['employee_id'] ?? '';
$company = $_GET['company'] ?? '';

if (!$start || !$end) {
    $start = date('Y-m-01');
    $end = date('Y-m-d');
}

$query = "SELECT r.empleado_id, u.full_name, u.company, r.hora_entrada, r.hora_salida 
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

if ($company) {
    $query .= " AND u.company = ?";
    $params[] = $company;
    $types .= "s";
}

$query .= " ORDER BY r.hora_entrada DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

// Configurar cabeceras para descarga de CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=reporte_asistencia_' . $start . '_a_' . $end . '.csv');

$output = fopen('php://output', 'w');

// Añadir BOM para que Excel reconozca UTF-8 correctamente
fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

// Cabeceras del CSV
fputcsv($output, ['ID Empleado', 'Nombre Completo', 'Empresa', 'Entrada', 'Salida', 'Puntualidad']);

while ($row = $result->fetch_assoc()) {
    $puntualidad = 'A Tiempo';
    if ($row['hora_entrada']) {
        $hora = strtotime($row['hora_entrada']);
        $mins = (int)date('H', $hora) * 60 + (int)date('i', $hora);
        if ($mins > (8 * 60 + 16)) $puntualidad = 'Falta';
        else if ($mins > (8 * 60 + 6)) $puntualidad = 'Retardo';
    }
    
    fputcsv($output, [
        $row['empleado_id'],
        $row['full_name'],
        $row['company'],
        $row['hora_entrada'],
        $row['hora_salida'] ?: 'En Turno',
        $puntualidad
    ]);
}

fclose($output);
exit;
