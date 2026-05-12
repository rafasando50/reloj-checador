<?php
require_once '../db.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? '';
$password = $data['password'] ?? '';
$full_name = $data['full_name'] ?? '';
$employee_id = $data['employee_id'] ?? '';
$department = $data['department'] ?? '';
$company = $data['company'] ?? '';

if ($id) {
    // ACTUALIZAR
    $query = "UPDATE users SET full_name = ?, employee_id = ?, department = ?, company = ?";
    $params = [$full_name, $employee_id, $department, $company];
    $types = "ssss";

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
    $stmt->execute();
    echo json_encode(['message' => 'Updated']);
} else {
    // CREAR
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $stmt = $conn->prepare("INSERT INTO users (password, full_name, employee_id, department, company) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $hashedPassword, $full_name, $employee_id, $department, $company);
    $stmt->execute();
    echo json_encode(['message' => 'Created']);
    http_response_code(201);
}
?>
