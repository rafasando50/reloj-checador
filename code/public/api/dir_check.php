<?php
header('Content-Type: application/json');

$dir = dirname(__FILE__);
$files = scandir($dir);
$result = [];

foreach ($files as $file) {
    if ($file === '.' || $file === '..') continue;
    
    $path = $dir . '/' . $file;
    $result[] = [
        'name' => $file,
        'size' => filesize($path),
        'permissions' => substr(sprintf('%o', fileperms($path)), -4),
        'is_readable' => is_readable($path),
        'is_writable' => is_writable($path)
    ];
}

echo json_encode([
    'current_directory' => $dir,
    'files_in_directory' => $result
]);
?>
