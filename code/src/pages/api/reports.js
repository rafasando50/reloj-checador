import pool from '../../lib/db';

export const GET = async ({ url }) => {
  const start = url.searchParams.get('start');
  const end = url.searchParams.get('end');
  const empId = url.searchParams.get('employee_id');
  let query = 'SELECT r.*, u.full_name FROM registros r LEFT JOIN users u ON r.empleado_id = u.employee_id WHERE r.hora_entrada >= ? AND r.hora_entrada <= ?';
  let params = [`${start} 00:00:00`, `${end} 23:59:59`];
  if (empId) { query += ' AND r.empleado_id = ?'; params.push(empId); }
  const [rows] = await pool.execute(query + ' ORDER BY r.hora_entrada DESC', params);
  return new Response(JSON.stringify(rows), { status: 200 });
};
