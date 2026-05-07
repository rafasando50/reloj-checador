import pool from '../../../lib/db';

export const POST = async ({ request }) => {
  try {
    const { id } = await request.json();
    await pool.execute('UPDATE users SET active = 1 WHERE id = ?', [id]);
    return new Response(JSON.stringify({ message: 'Usuario reactivado correctamente' }), { status: 200 });
  } catch (e) {
    return new Response(JSON.stringify({ message: 'Error al reactivar' }), { status: 500 });
  }
};
