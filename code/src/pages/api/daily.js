import pool from '../../lib/db';

export const GET = async () => {
  const hoy = new Date(); hoy.setHours(0,0,0,0);
  const [rows] = await pool.execute('SELECT r.*, u.full_name FROM registros r LEFT JOIN users u ON r.empleado_id = u.employee_id WHERE r.hora_entrada >= ? ORDER BY r.hora_entrada DESC', [hoy]);
  return new Response(JSON.stringify(rows), { status: 200 });
};
