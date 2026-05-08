import pool from '../../../lib/db';

export const POST = async ({ url }) => {
  try {
    const id = url.searchParams.get('id');
    if (!id) {
      return new Response(JSON.stringify({ error: 'ID no proporcionado' }), { status: 400 });
    }
    // Borrado lógico: Solo cambiamos el estado a inactivo
    await pool.execute('UPDATE users SET active = 0 WHERE id = ?', [id]);
    return new Response(JSON.stringify({ message: 'Usuario desactivado correctamente' }), { status: 200 });
  } catch (error) {
    console.error('Error al desactivar usuario:', error);
    return new Response(JSON.stringify({ error: 'Error interno del servidor' }), { status: 500 });
  }
};
