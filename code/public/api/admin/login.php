<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);
$user = $data['user'] ?? '';
$pass = $data['pass'] ?? '';

if ($user === 'sistemas' && $pass === 'esusistemas123') {
    setcookie('admin_session', 'active', time() + (365 * 24 * 60 * 60), '/');
    echo json_encode(['message' => 'Ok']);
} else {
    echo json_encode(['message' => 'Error']);
    http_response_code(401);
}
?>
