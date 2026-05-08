import pool from './src/lib/db.js';

async function checkTables() {
  try {
    const [rows] = await pool.execute('SHOW TABLES');
    console.log('Tablas en la base de datos:');
    console.log(rows);
    process.exit(0);
  } catch (error) {
    console.error('Error al conectar:', error);
    process.exit(1);
  }
}

checkTables();
