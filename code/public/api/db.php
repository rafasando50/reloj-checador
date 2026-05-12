<?php
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit;
}

$host = 'localhost';
$user = 'ykihkdau_eg-access_usu';
$pass = 'SkR"%167*O|3';
$db   = 'ykihkdau_eg-access';

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Connection failed: ' . $conn->connect_error]));
}

$conn->set_charset("utf8");

// Configurar Huso Horario (México)
date_default_timezone_set('America/Mexico_City');
$conn->query("SET time_zone = '-06:00'");
?>
