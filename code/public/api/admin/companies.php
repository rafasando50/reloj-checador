<?php
require_once '../db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_GET['name'] ?? '';
    if (!$name) {
        echo json_encode(['error' => 'Nombre no proporcionado']);
        http_response_code(400);
        exit;
    }
    $stmt = $conn->prepare("INSERT IGNORE INTO companies (name) VALUES (?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    echo json_encode(['message' => 'Empresa añadida correctamente']);
} else {
    $result = $conn->query("SELECT * FROM companies ORDER BY name ASC");
    $companies = [];
    while ($row = $result->fetch_assoc()) {
        $companies[] = $row;
    }
    echo json_encode($companies);
}
?>