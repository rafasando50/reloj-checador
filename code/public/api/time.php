<?php
header('Content-Type: application/json');

// Desactivar temporalmente la zona horaria para ver la hora por defecto del sistema
$default_timezone = date_default_timezone_get();
$system_time_php = date('Y-m-d H:i:s');

// Intentar obtener la hora del sistema operativo directamente
$system_os_time = 'No disponible';
if (function_exists('shell_exec')) {
    $os_date = shell_exec('date');
    if ($os_date) {
        $system_os_time = trim($os_date);
    }
}

echo json_encode([
    'nota' => 'Esta es la hora interna de tu servidor de BanaHosting. Programa tu Cron Job usando la HORA y MINUTO que se muestran en "system_os_time" o "system_php_time".',
    'system_php_time' => $system_time_php,
    'system_os_time' => $system_os_time,
    'php_timezone' => $default_timezone,
    'ruta_absoluta_cron' => dirname(__FILE__) . '/cron_reports.php'
]);
?>
