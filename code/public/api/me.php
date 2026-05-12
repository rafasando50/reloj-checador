<?php
require_once 'db.php';

$session = $_COOKIE['session'] ?? '';
if (empty($session)) {
    echo json_encode(['error' => 'No session']);
    http_response_code(401);
    exit;
}

$userId = str_replace('user-', '', $session);

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ? OR employee_id = ?");
$stmt->bind_param("ss", $userId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'User not found']);
    http_response_code(404);
    exit;
}

$user = $result->fetch_assoc();
// No enviar el hash de la contraseña
unset($user['password']);

echo json_encode($user);
?>
