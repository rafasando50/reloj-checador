import pool from '../../../lib/db';

export const GET = async () => {
  try {
    const [rows] = await pool.execute('SELECT * FROM companies ORDER BY name ASC');
    return new Response(JSON.stringify(rows), { status: 200 });
  } catch (error) {
    console.error('Error al obtener empresas:', error);
    return new Response(JSON.stringify({ error: 'Error al obtener empresas' }), { status: 500 });
  }
};

export const POST = async ({ url }) => {
  try {
    const name = url.searchParams.get('name');
    if (!name) {
      return new Response(JSON.stringify({ error: 'Nombre no proporcionado' }), { status: 400 });
    }
    await pool.execute('INSERT IGNORE INTO companies (name) VALUES (?)', [name]);
    return new Response(JSON.stringify({ message: 'Empresa añadida correctamente' }), { status: 200 });
  } catch (error) {
    console.error('Error al añadir empresa:', error);
    return new Response(JSON.stringify({ error: 'Error al añadir empresa' }), { status: 500 });
  }
};
