import pool from '../../lib/db';

export const POST = async ({ request }) => {
  try {
    const { empleado_id: qrString, tipo } = await request.json();
    const parts = qrString.split('|');
    if (parts.length !== 3 || parts[2] !== 'EINSUR2026') return new Response(JSON.stringify({ error: 'QR inválido' }), { status: 400 });

    const qrTimestamp = parseInt(parts[1]);
    const ahora = Date.now();
    // Expirar QR después de 15 segundos para evitar capturas de pantalla
    if (isNaN(qrTimestamp) || (ahora - qrTimestamp) > 15000) {
      return new Response(JSON.stringify({ error: 'El QR ha expirado (Captura no válida)' }), { status: 400 });
    }

    const employee_id = parts[0];
    const [empRows] = await pool.execute('SELECT full_name FROM users WHERE employee_id = ? AND active = 1', [employee_id]);
    
    if (empRows.length === 0) return new Response(JSON.stringify({ error: 'Empleado no encontrado o inactivo' }), { status: 400 });
    
    const name = empRows[0].full_name;

    if (tipo === 'Entrada') {
      const hoy = new Date(); hoy.setHours(0,0,0,0);
      const [exists] = await pool.execute('SELECT id FROM registros WHERE empleado_id = ? AND hora_entrada >= ?', [employee_id, hoy]);
      if (exists.length > 0) return new Response(JSON.stringify({ error: 'Ya registrado hoy' }), { status: 400 });
      await pool.execute('INSERT INTO registros (empleado_id, hora_entrada) VALUES (?, ?)', [employee_id, new Date()]);
    } else {
      const [res] = await pool.execute('UPDATE registros SET hora_salida = ? WHERE empleado_id = ? AND hora_salida IS NULL', [new Date(), employee_id]);
      if (res.affectedRows === 0) return new Response(JSON.stringify({ error: 'No hay entrada abierta' }), { status: 400 });
    }
    return new Response(JSON.stringify({ success: true, nombre: name }), { status: 200 });
  } catch (e) { return new Response(JSON.stringify({ error: 'Error' }), { status: 500 }); }
};
