<?php
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$employee_id = $data['employee_id'] ?? '';
$password = $data['password'] ?? '';

if (empty($employee_id) || empty($password)) {
    echo json_encode(['message' => 'Campos requeridos']);
    http_response_code(400);
    exit;
}

$stmt = $conn->prepare("SELECT * FROM users WHERE employee_id = ? AND active = 1");
$stmt->bind_param("s", $employee_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['message' => 'Usuario no encontrado']);
    http_response_code(401);
    exit;
}

$user = $result->fetch_assoc();

if (password_verify($password, $user['password'])) {
    // Establecer cookie por un año
    setcookie('session', 'user-' . $user['id'], time() + (365 * 24 * 60 * 60), '/');
    echo json_encode(['message' => 'Ok', 'user_id' => $user['id']]);
} else {
    echo json_encode(['message' => 'Clave incorrecta']);
    http_response_code(401);
}
?>
