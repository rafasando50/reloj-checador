import pool from '../../../lib/db';

export const DELETE = async ({ url }) => {
  const id = url.searchParams.get('id');
  // Cambiamos el borrado físico por un borrado lógico (desactivar)
  await pool.execute('UPDATE users SET active = 0 WHERE id = ?', [id]);
  return new Response(JSON.stringify({ message: 'Usuario desactivado correctamente' }), { status: 200 });
};
