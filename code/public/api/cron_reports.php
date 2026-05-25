<?php
require_once 'db.php';

// 1. Obtener empleados activos
$queryUsers = "SELECT id, employee_id, full_name, company, hora_entrada FROM users WHERE active = 1";
$resUsers = $conn->query($queryUsers);
$users = [];
while ($u = $resUsers->fetch_assoc()) {
    $users[$u['employee_id']] = $u;
}

// 2. Obtener registros de hoy
$queryRegs = "SELECT empleado_id, hora_entrada, hora_salida FROM registros WHERE DATE(hora_entrada) = CURDATE()";
$resRegs = $conn->query($queryRegs);
$registros = [];
while ($r = $resRegs->fetch_assoc()) {
    $registros[$r['empleado_id']] = $r;
}

$now = time();
$incidencias = [];

// 3. Procesar cada usuario
foreach ($users as $empId => $user) {
    $company = trim($user['company']);
    if (empty($company)) {
        $company = 'Sin Empresa';
    }

    $hora_esperada_str = $user['hora_entrada'] ?: '08:00:00';
    
    if (isset($registros[$empId])) {
        // El empleado asistió hoy
        $reg = $registros[$empId];
        $hora_entrada_real = strtotime($reg['hora_entrada']);
        $hora_esperada_ts = strtotime(date('Y-m-d ', $hora_entrada_real) . $hora_esperada_str);
        
        $diff_segundos = $hora_entrada_real - $hora_esperada_ts;
        $diff_minutos = $diff_segundos / 60;
        
        if ($diff_minutos > 16) {
            // Falta por retardo grave
            $incidencias[$company][] = [
                'employee_id' => $empId,
                'full_name' => $user['full_name'],
                'tipo' => 'Falta (Retardo grave)',
                'hora_esperada' => substr($hora_esperada_str, 0, 5),
                'hora_real' => date('H:i', $hora_entrada_real),
                'minutos_tarde' => round($diff_minutos)
            ];
        } else if ($diff_minutos > 6) {
            // Retardo
            $incidencias[$company][] = [
                'employee_id' => $empId,
                'full_name' => $user['full_name'],
                'tipo' => 'Retardo',
                'hora_esperada' => substr($hora_esperada_str, 0, 5),
                'hora_real' => date('H:i', $hora_entrada_real),
                'minutos_tarde' => round($diff_minutos)
            ];
        }
    } else {
        // El empleado no ha checado hoy
        $hora_esperada_ts = strtotime(date('Y-m-d ') . $hora_esperada_str);
        $grace_ts = $hora_esperada_ts + (16 * 60); // 16 minutos de tolerancia para considerar Falta
        
        if ($now > $grace_ts) {
            // Ya expiró la tolerancia y no asistió
            $incidencias[$company][] = [
                'employee_id' => $empId,
                'full_name' => $user['full_name'],
                'tipo' => 'Falta (No asistió / No ha checado)',
                'hora_esperada' => substr($hora_esperada_str, 0, 5),
                'hora_real' => '--:--',
                'minutos_tarde' => '--'
            ];
        }
    }
}

// 4. Enviar correos por cada empresa
$destinatario = "asistenciaeglobal@einsursupply.com.mx";
$reportes_enviados = [];

foreach ($incidencias as $company => $lista) {
    if (empty($lista)) {
        continue;
    }

    $subject = "[Reporte] Retardos y Faltas - " . $company . " (" . date("d/m/Y") . ")";
    
    // Construir la plantilla HTML Premium
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="utf-8">
        <style>
            body {
                font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
                background-color: #f1f5f9;
                color: #1e293b;
                margin: 0;
                padding: 20px;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                background: #ffffff;
                border-radius: 16px;
                overflow: hidden;
                box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
                border: 1px solid #e2e8f0;
            }
            .header {
                background: linear-gradient(135deg, #273469 0%, #4a90e2 100%);
                color: #ffffff;
                padding: 30px 20px;
                text-align: center;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
                font-weight: 800;
                letter-spacing: 0.5px;
            }
            .header p {
                margin: 5px 0 0;
                font-size: 14px;
                opacity: 0.9;
                font-weight: 500;
            }
            .content {
                padding: 25px 20px;
            }
            .intro-text {
                font-size: 15px;
                line-height: 1.6;
                margin-bottom: 20px;
                color: #475569;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 20px;
            }
            th {
                background-color: #f8fafc;
                text-align: left;
                padding: 12px;
                font-size: 12px;
                font-weight: 700;
                text-transform: uppercase;
                color: #64748b;
                border-bottom: 2px solid #e2e8f0;
            }
            td {
                padding: 14px 12px;
                font-size: 14px;
                border-bottom: 1px solid #f1f5f9;
                color: #334155;
            }
            .badge {
                padding: 4px 8px;
                border-radius: 6px;
                font-size: 11px;
                font-weight: 700;
                display: inline-block;
            }
            .badge-retardo {
                background-color: #fef3c7;
                color: #d97706;
            }
            .badge-falta {
                background-color: #fee2e2;
                color: #ef4444;
            }
            .footer {
                background-color: #f8fafc;
                padding: 15px;
                text-align: center;
                font-size: 12px;
                color: #94a3b8;
                border-top: 1px solid #e2e8f0;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>Reporte de Incidencias</h1>
                <p>' . htmlspecialchars($company) . ' &bull; ' . date("d/m/Y") . '</p>
            </div>
            <div class="content">
                <p class="intro-text">
                    A continuación se presenta la lista de empleados que registraron <strong>Retardo</strong> o <strong>Falta</strong> hoy:
                </p>
                <table>
                    <thead>
                        <tr>
                            <th>Empleado</th>
                            <th>Entrada</th>
                            <th>Llegada</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>';
                    
    foreach ($lista as $item) {
        $badgeClass = (strpos($item['tipo'], 'Retardo') !== false) ? 'badge-retardo' : 'badge-falta';
        $minutosTardeText = ($item['minutos_tarde'] !== '--') ? ' (' . $item['minutos_tarde'] . ' min)' : '';
        
        $html .= '
                        <tr>
                            <td>
                                <strong>' . htmlspecialchars($item['full_name']) . '</strong><br>
                                <span style="font-size: 11px; color: #94a3b8; font-family: monospace;">ID: ' . htmlspecialchars($item['employee_id']) . '</span>
                            </td>
                            <td style="font-family: monospace; font-weight: bold;">' . htmlspecialchars($item['hora_esperada']) . '</td>
                            <td style="font-family: monospace; font-weight: bold;">' . htmlspecialchars($item['hora_real']) . '</td>
                            <td>
                                <span class="badge ' . $badgeClass . '">' . htmlspecialchars($item['tipo']) . $minutosTardeText . '</span>
                            </td>
                        </tr>';
    }
    
    $html .= '
                    </tbody>
                </table>
            </div>
            <div class="footer">
                Este correo fue generado automáticamente por el Sistema Reloj Checador.<br>
                &copy; ' . date("Y") . ' Todos los derechos reservados.
            </div>
        </div>
    </body>
    </html>';

    // Configurar cabeceras de correo UTF-8
    $encoded_subject = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: Reloj Checador <no-reply@" . ($_SERVER['HTTP_HOST'] ?: 'relojchecador.local') . ">\r\n";
    
    // Enviar correo nativo
    $enviado = mail($destinatario, $encoded_subject, $html, $headers);
    
    $reportes_enviados[] = [
        'company' => $company,
        'sent' => $enviado,
        'recipient' => $destinatario,
        'count' => count($lista),
        'employees' => $lista
    ];
}

// 5. Retornar JSON
echo json_encode([
    'status' => 'success',
    'date' => date('Y-m-d H:i:s'),
    'reports_processed' => count($reportes_enviados),
    'details' => $reportes_enviados
]);
?>
