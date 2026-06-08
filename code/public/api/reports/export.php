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

$query = "SELECT r.empleado_id, u.full_name, u.company, r.hora_entrada, r.hora_salida, u.hora_entrada AS hora_esperada 
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
    $companyLower = strtolower(trim($row['company'] ?? ''));
    if ($companyLower === 'la casita') {
        if ($row['hora_entrada']) {
            if ($row['hora_salida']) {
                $horas_trabajadas = (strtotime($row['hora_salida']) - strtotime($row['hora_entrada'])) / 3600;
                if ($horas_trabajadas < 7.75) {
                    if ($horas_trabajadas < 7.0) {
                        $puntualidad = 'Falta';
                    } else {
                        $puntualidad = 'Retardo';
                    }
                }
            } else {
                $dia_entrada = date('Y-m-d', strtotime($row['hora_entrada']));
                if ($dia_entrada === date('Y-m-d')) {
                    $puntualidad = 'A Tiempo';
                } else {
                    $puntualidad = 'Falta';
                }
            }
        }
    } else if ($row['hora_entrada']) {
        $hora_entrada_real = strtotime($row['hora_entrada']);
        $hora_esperada_str = $row['hora_esperada'] ?? '08:00:00';
        $hora_esperada_ts = strtotime(date('Y-m-d ', $hora_entrada_real) . $hora_esperada_str);
        
        $diff_segundos = $hora_entrada_real - $hora_esperada_ts;
        $diff_minutos = $diff_segundos / 60;

        if ($diff_minutos > 16) $puntualidad = 'Falta';
        else if ($diff_minutos > 6) $puntualidad = 'Retardo';
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
