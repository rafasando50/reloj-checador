<?php
require_once '../db.php';

$showInactive = ($_GET['showInactive'] ?? 'false') === 'true';
$companyFilter = $_GET['company'] ?? 'all';
$search = $_GET['search'] ?? '';

$query = "SELECT * FROM users WHERE 1=1";
$params = [];
$types = "";

if (!$showInactive) {
    $query .= " AND active = 1";
}

if ($companyFilter !== 'all') {
    $query .= " AND company = ?";
    $params[] = $companyFilter;
    $types .= "s";
}

if ($search) {
    $query .= " AND (full_name LIKE ? OR employee_id LIKE ? OR department LIKE ? OR puesto LIKE ?)";
    $searchTerm = "%$search%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $params[] = $searchTerm;
    $types .= "ssss";
}

$query .= " ORDER BY full_name ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

$users = [];
while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

echo json_encode($users);
?>
