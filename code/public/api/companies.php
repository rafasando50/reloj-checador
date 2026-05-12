<?php
require_once 'db.php';

$stmt = $conn->prepare("SELECT * FROM companies ORDER BY name ASC");
$stmt->execute();
$result = $stmt->get_result();

$companies = [];
while ($row = $result->fetch_assoc()) {
    $companies[] = $row;
}

echo json_encode($companies);
?>