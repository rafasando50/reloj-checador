<?php
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$qrString = $data['empleado_id'] ?? '';
$tipo = $data['tipo'] ?? '';

if (empty($qrString)) {
    echo json_encode(['error' => 'QR vacío']);
    http_response_code(400);
    exit;
}

$parts = explode('|', $qrString);
if (count($parts) !== 3 || $parts[2] !== 'EINSUR2026') {
    echo json_encode(['error' => 'QR inválido']);
    http_response_code(400);
    exit;
}

$qrTimestamp = (int)$parts[1];
$ahora = time() * 1000; // Milisegundos

if (!$qrTimestamp || ($ahora - $qrTimestamp) > 15000) {
    echo json_encode(['error' => 'El QR ha expirado (Captura no válida)']);
    http_response_code(400);
    exit;
}

$employee_id = $parts[0];
$estacion = $data['estacion'] ?? 'Remoto';

// Verificar empleado
$stmt = $conn->prepare("SELECT full_name FROM users WHERE employee_id = ? AND active = 1");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$resEmp = $stmt->get_result();

if ($resEmp->num_rows === 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Empleado no encontrado o inactivo']);
    exit;
}

$user = $resEmp->fetch_assoc();
$name = $user['full_name'];

if ($tipo === 'Entrada') {
    $hoy = date('Y-m-d 00:00:00');
    $stmt = $conn->prepare("SELECT id FROM registros WHERE empleado_id = ? AND hora_entrada >= ? AND hora_salida IS NULL");
    $stmt->bind_param("ss", $employee_id, $hoy);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Ya registrado hoy']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO registros (empleado_id, hora_entrada, entrada_estacion) VALUES (?, NOW(), ?)");
    $stmt->bind_param("ss", $employee_id, $estacion);
    $stmt->execute();
} else {
    // Verificar si tiene una entrada hoy que no tenga salida
    $hoy = date('Y-m-d 00:00:00');
    $stmt = $conn->prepare("SELECT id FROM registros WHERE empleado_id = ? AND hora_entrada >= ? AND hora_salida IS NULL ORDER BY hora_entrada DESC LIMIT 1");
    $stmt->bind_param("ss", $employee_id, $hoy);
    $stmt->execute();
    $resSalida = $stmt->get_result();

    if ($resSalida->num_rows === 0) {
        http_response_code(400);
        echo json_encode(['error' => 'No has marcado entrada hoy']);
        exit;
    }

    $registro = $resSalida->fetch_assoc();
    $stmt = $conn->prepare("UPDATE registros SET hora_salida = NOW(), salida_estacion = ? WHERE id = ?");
    $stmt->bind_param("si", $estacion, $registro['id']);
    $stmt->execute();
}

echo json_encode(['success' => true, 'nombre' => $name]);
?>
