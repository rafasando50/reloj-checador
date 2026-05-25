import pool from './src/lib/db.js';

async function checkCompanies() {
  try {
    const [rows] = await pool.execute('SELECT * FROM companies');
    console.log('Empresas en la base de datos:');
    console.log(rows);
    process.exit(0);
  } catch (error) {
    console.error('Error al conectar:', error);
    process.exit(1);
  }
}

checkCompanies();
