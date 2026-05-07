import pool from '../../../lib/db';
import bcrypt from 'bcryptjs';

export const POST = async ({ request }) => {
  const { id, password, full_name, employee_id, department, company } = await request.json();

  if (id) {
    // Modo: ACTUALIZAR
    let query = 'UPDATE users SET full_name = ?, employee_id = ?, department = ?, company = ?';
    let params = [full_name, employee_id, department || '', company || ''];

    if (password && password.trim() !== "") {
      const hashedPassword = await bcrypt.hash(password, 10);
      query += ', password = ?';
      params.push(hashedPassword);
    }

    query += ' WHERE id = ?';
    params.push(id);

    await pool.execute(query, params);
    return new Response(JSON.stringify({ message: 'Updated' }), { status: 200 });
  } else {
    // Modo: CREAR
    const hashedPassword = await bcrypt.hash(password, 10);
    await pool.execute(
      'INSERT INTO users (password, full_name, employee_id, department, company) VALUES (?, ?, ?, ?, ?)',
      [hashedPassword, full_name, employee_id, department || '', company || '']
    );
    return new Response(JSON.stringify({ message: 'Created' }), { status: 201 });
  }
};
