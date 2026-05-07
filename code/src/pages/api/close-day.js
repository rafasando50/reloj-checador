import pool from '../../lib/db';

export const POST = async () => {
  try {
    await pool.execute('UPDATE registros SET hora_salida = ? WHERE hora_salida IS NULL', [new Date()]);
    return new Response(JSON.stringify({ success: true }), { status: 200 });
  } catch (e) { return new Response(JSON.stringify({ error: 'Error' }), { status: 500 }); }
};
