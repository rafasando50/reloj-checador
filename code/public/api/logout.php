<?php
setcookie('session', '', time() - 3600, '/');
echo json_encode(['message' => 'Ok']);
?>
