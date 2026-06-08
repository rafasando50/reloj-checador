import pool from './src/lib/db.js';

async function checkSchema() {
  try {
    const [usersCols] = await pool.execute('DESCRIBE users');
    console.log('--- COLUMNS IN users ---');
    console.log(usersCols);

    const [companiesCols] = await pool.execute('DESCRIBE companies');
    console.log('--- COLUMNS IN companies ---');
    console.log(companiesCols);

    process.exit(0);
  } catch (error) {
    console.error('Error:', error);
    process.exit(1);
  }
}

checkSchema();
