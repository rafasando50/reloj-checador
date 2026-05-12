<?php
header('Content-Type: application/json');
require_once '../db.php';

$id = $_GET['id'] ?? '';
if (!$id) exit;

$stmt = $conn->prepare("UPDATE users SET active = 0 WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode(['message' => 'Deactivated']);
?>
