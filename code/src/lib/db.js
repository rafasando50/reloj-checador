import mysql from 'mysql2/promise';
import dotenv from 'dotenv';
import path from 'path';

// Asegurar que cargamos el .env desde la raíz del proyecto
dotenv.config({ path: path.resolve(process.cwd(), '.env') });

const pool = mysql.createPool({
  host: process.env.DB_HOST || '127.0.0.1',
  user: process.env.DB_USER || 'root',
  password: process.env.DB_PASSWORD || '',
  database: process.env.DB_NAME || 'relojChecador',
  waitForConnections: true,
  connectionLimit: 10,
  queueLimit: 0
});

export default pool;
