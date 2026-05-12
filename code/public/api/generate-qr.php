<?php
require_once 'db.php';

$session = $_COOKIE['session'] ?? '';
if (empty($session)) {
    echo json_encode(['error' => 'No session']);
    http_response_code(401);
    exit;
}

$userId = str_replace('user-', '', $session);

$stmt = $conn->prepare("SELECT employee_id FROM users WHERE id = ? OR employee_id = ?");
$stmt->bind_param("ss", $userId, $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['error' => 'User not found']);
    http_response_code(404);
    exit;
}

$row = $result->fetch_assoc();
$qrCode = $row['employee_id'] . '|' . (round(microtime(true) * 1000)) . '|EINSUR2026';

echo json_encode(['qrCode' => $qrCode]);
?>
