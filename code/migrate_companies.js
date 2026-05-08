import pool from './src/lib/db.js';

async function createCompaniesTable() {
  try {
    // Crear tabla de empresas
    await pool.execute(`
      CREATE TABLE IF NOT EXISTS companies (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
      )
    `);
    
    // Insertar empresas actuales si no existen
    const companies = ['Einsur', 'Innova'];
    for (const company of companies) {
      await pool.execute('INSERT IGNORE INTO companies (name) VALUES (?)', [company]);
    }
    
    console.log('Tabla companies creada e inicializada correctamente.');
    process.exit(0);
  } catch (error) {
    console.error('Error al crear la tabla:', error);
    process.exit(1);
  }
}

createCompaniesTable();
