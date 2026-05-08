import pool from '../../lib/db';
import bcrypt from 'bcryptjs';

export const POST = async ({ request, cookies }) => {
  try {
    const { employee_id, password } = await request.json();
    const [rows] = await pool.execute('SELECT * FROM users WHERE employee_id = ? AND active = 1', [employee_id]);

    if (rows.length === 0) return new Response(JSON.stringify({ message: 'Usuario no encontrado o inactivo' }), { status: 401 });
    const user = rows[0];
    const isValid = await bcrypt.compare(password, user.password);

    if (!isValid) return new Response(JSON.stringify({ message: 'Clave incorrecta' }), { status: 401 });

    cookies.set('session', `user-${user.id}`, { path: '/', httpOnly: true, maxAge: 60 * 60 * 24 * 365 });
    return new Response(JSON.stringify({ message: 'Ok' }), { status: 200 });
  } catch (e) { return new Response(JSON.stringify({ message: 'Error' }), { status: 500 }); }
};
