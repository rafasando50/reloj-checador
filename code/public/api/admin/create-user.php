<?php
require_once '../db.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';
$password = $data['password'] ?? '';
$full_name = $data['full_name'] ?? '';
$employee_id = $data['employee_id'] ?? '';
$department = $data['department'] ?? '';
$company = $data['company'] ?? '';
$puesto = $data['puesto'] ?? 'Empleado';
$tipo_horario = $data['tipo_horario'] ?? 'Personalizado';
$hora_entrada = $data['hora_entrada'] ?? '08:00:00';

// Normalizar formato de hora HH:MM a HH:MM:00
if (strlen($hora_entrada) === 5) {
    $hora_entrada .= ':00';
}

// VALIDAR DUPLICADOS
if ($id) {
    // Si estamos editando, buscar si el ID ya existe en OTRO usuario
    $stmtCheck = $conn->prepare("SELECT id FROM users WHERE employee_id = ? AND id != ?");
    $stmtCheck->bind_param("si", $employee_id, $id);
} else {
    // Si estamos creando, buscar si el ID ya existe
    $stmtCheck = $conn->prepare("SELECT id FROM users WHERE employee_id = ?");
    $stmtCheck->bind_param("s", $employee_id);
}

$stmtCheck->execute();
if ($stmtCheck->get_result()->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['error' => 'El ID de empleado ya está en uso por otro usuario']);
    exit;
}

if ($id) {
    // ACTUALIZAR
    $query = "UPDATE users SET full_name = ?, employee_id = ?, department = ?, company = ?, puesto = ?, tipo_horario = ?, hora_entrada = ?";
    $params = [$full_name, $employee_id, $department, $company, $puesto, $tipo_horario, $hora_entrada];
    $types = "sssssss";

    if (!empty($password)) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $query .= ", password = ?";
        $params[] = $hashedPassword;
        $types .= "s";
    }

    $query .= " WHERE id = ?";
    $params[] = $id;
    $types .= "i";

    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['message' => 'Updated']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al actualizar: ' . $conn->error]);
    }
} else {
    // CREAR
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (password, full_name, employee_id, department, company, puesto, tipo_horario, hora_entrada) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $hashedPassword, $full_name, $employee_id, $department, $company, $puesto, $tipo_horario, $hora_entrada);
    
    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(['message' => 'Created']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Error al crear: ' . $conn->error]);
    }
}
?>

