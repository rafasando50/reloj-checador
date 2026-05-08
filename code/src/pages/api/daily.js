import pool from '../../lib/db';

export const GET = async ({ url }) => {
  const empId = url.searchParams.get('employee_id');
  const company = url.searchParams.get('company');

  let query = `
    SELECT r.id, r.empleado_id, r.hora_entrada AS entrada, r.hora_salida AS salida, u.full_name, u.company 
    FROM registros r 
    JOIN users u ON r.empleado_id = u.employee_id 
    WHERE DATE(r.hora_entrada) = CURDATE()
  `;
  let params = [];

  if (empId) {
    query += ' AND r.empleado_id = ?';
    params.push(empId);
  }
  if (company) {
    query += ' AND u.company = ?';
    params.push(company);
  }

  const [rows] = await pool.execute(query + ' ORDER BY entrada DESC', params);
  
  // Calcular puntualidad
  const data = rows.map(r => {
    let puntualidad = 'A Tiempo';
    if (r.entrada) {
      const hora = new Date(r.entrada);
      // Ajustar a zona horaria local si es necesario, 
      // pero el servidor suele estar en la misma zona o UTC
      const mins = hora.getHours() * 60 + hora.getMinutes();
      if (mins > (8 * 60 + 16)) puntualidad = 'Falta';
      else if (mins > (8 * 60 + 6)) puntualidad = 'Retardo';
    }
    return { ...r, puntualidad };
  });

  return new Response(JSON.stringify(data), { status: 200 });
};
