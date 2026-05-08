import pool from '../../../lib/db';
import ExcelJS from 'exceljs';

export const GET = async ({ url }) => {
  let start = url.searchParams.get('start');
  let end = url.searchParams.get('end');
  const empId = url.searchParams.get('employee_id');
  const company = url.searchParams.get('company');

  // Fallback a mes actual si no hay fechas
  if (!start || !end) {
    const now = new Date();
    start = new Date(now.getFullYear(), now.getMonth(), 1).toISOString().split('T')[0];
    end = now.toISOString().split('T')[0];
  }

  let query = 'SELECT r.id, r.empleado_id, r.hora_entrada AS entrada, r.hora_salida AS salida, u.full_name, u.company FROM registros r LEFT JOIN users u ON r.empleado_id = u.employee_id WHERE r.hora_entrada >= ? AND r.hora_entrada <= ?';
  let params = [`${start} 00:00:00`, `${end} 23:59:59`];

  if (empId) { query += ' AND r.empleado_id = ?'; params.push(empId); }
  if (company) { query += ' AND u.company = ?'; params.push(company); }

  const [rows] = await pool.execute(query + ' ORDER BY entrada DESC', params);

  // Crear libro con ExcelJS
  const workbook = new ExcelJS.Workbook();
  const worksheet = workbook.addWorksheet('Asistencia');

  // Definir columnas y anchos
  worksheet.columns = [
    { header: 'EMPRESA', key: 'company', width: 15 },
    { header: 'EMPLEADO', key: 'name', width: 35 },
    { header: 'ID EMPLEADO', key: 'empId', width: 15 },
    { header: 'FECHA', key: 'date', width: 15 },
    { header: 'ENTRADA', key: 'entrada', width: 22 },
    { header: 'SALIDA', key: 'salida', width: 22 },
    { header: 'ESTADO', key: 'status', width: 15 },
    { header: 'PUNTUALIDAD', key: 'puntualidad', width: 15 }
  ];

  // Agregar datos
  rows.forEach(row => {
    let puntualidad = 'A Tiempo';
    if (row.entrada) {
      const hora = new Date(row.entrada);
      const mins = hora.getHours() * 60 + hora.getMinutes();
      if (mins > (8 * 60 + 16)) puntualidad = 'Falta';
      else if (mins > (8 * 60 + 6)) puntualidad = 'Retardo';
    }

    worksheet.addRow({
      company: row.company || 'EINSUR',
      name: row.full_name || 'Desconocido',
      empId: row.empleado_id,
      date: row.entrada ? new Date(row.entrada).toLocaleDateString() : '-',
      entrada: row.entrada ? new Date(row.entrada).toLocaleString() : '-',
      salida: row.salida ? new Date(row.salida).toLocaleString() : '-',
      status: row.salida ? 'Completado' : 'En Turno',
      puntualidad: puntualidad
    });
  });

  // DISEÑO: Estilizar encabezados
  const headerRow = worksheet.getRow(1);
  headerRow.height = 25;
  headerRow.eachCell((cell) => {
    cell.fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: 'FF273469' } // Azul oscuro premium
    };
    cell.font = {
      bold: true,
      color: { argb: 'FFFFFFFF' }, // Blanco
      size: 11
    };
    cell.alignment = { vertical: 'middle', horizontal: 'center' };
    cell.border = {
      top: { style: 'thin' },
      left: { style: 'thin' },
      bottom: { style: 'thin' },
      right: { style: 'thin' }
    };
  });

  // Estilizar celdas de datos
  worksheet.eachRow((row, rowNumber) => {
    if (rowNumber > 1) {
      row.height = 20;
      row.eachCell((cell) => {
        cell.alignment = { vertical: 'middle', horizontal: 'center' };
        cell.border = {
          top: { style: 'thin', color: { argb: 'FFE2E8F0' } },
          left: { style: 'thin', color: { argb: 'FFE2E8F0' } },
          bottom: { style: 'thin', color: { argb: 'FFE2E8F0' } },
          right: { style: 'thin', color: { argb: 'FFE2E8F0' } }
        };
      });
    }
  });

  // Generar buffer
  const buffer = await workbook.xlsx.writeBuffer();

  return new Response(buffer, {
    status: 200,
    headers: {
      'Content-Type': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'Content-Disposition': `attachment; filename=reporte-asistencia-${start}-a-${end}.xlsx`
    }
  });
};
