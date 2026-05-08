import pool from '../../lib/db';

export const GET = async ({ url }) => {
  let start = url.searchParams.get('start');
  let end = url.searchParams.get('end');
  const empId = url.searchParams.get('employee_id');

  // Fallback a mes actual si no hay fechas
  if (!start || !end) {
    const now = new Date();
    start = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
    end = now.toISOString().split('T')[0];
  }

  let query = 'SELECT r.id, r.empleado_id, r.hora_entrada AS entrada, r.hora_salida AS salida, u.full_name FROM registros r LEFT JOIN users u ON r.empleado_id = u.employee_id WHERE r.hora_entrada >= ? AND r.hora_entrada <= ?';
  let params = [`${start} 00:00:00`, `${end} 23:59:59`];
  if (empId) { query += ' AND r.empleado_id = ?'; params.push(empId); }
  const [rows] = await pool.execute(query + ' ORDER BY entrada DESC', params);
  return new Response(JSON.stringify(rows), { status: 200 });
};
