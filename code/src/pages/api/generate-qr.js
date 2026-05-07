import pool from '../../lib/db';

export const GET = async ({ cookies }) => {
  const session = cookies.get('session');
  if (!session) return new Response(JSON.stringify({ error: 'No session' }), { status: 401 });

  const userId = session.value.replace('user-', '');
  const [rows] = await pool.execute('SELECT employee_id FROM users WHERE id = ?', [userId]);
  
  if (rows.length === 0) return new Response(JSON.stringify({ error: 'User not found' }), { status: 404 });

  const qrCode = `${rows[0].employee_id}|${Date.now()}|EINSUR2026`;
  return new Response(JSON.stringify({ qrCode }), { status: 200 });
};
