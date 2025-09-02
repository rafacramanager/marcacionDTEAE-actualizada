<?php
session_start();
require 'vendor/autoload.php'; // Incluye el autoload de Composer
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

include_once("bd.php");

// Obtener datos de la sesión
$mes = $_SESSION['mes'];
$nombre_usuario = $_SESSION['nombre_usuario'];
$codigo_usuario = $_SESSION['codigo_usuario'];
$institucion = $_SESSION['centro_educativo'];
$codigo_institucion = $_SESSION['codigo_infraestructura'];

// Configurar mes y año
list($yearSeleccionado, $monthSeleccionado) = explode('-', $mes);
$meses = [
    1 => 'enero',
    2 => 'febrero',
    3 => 'marzo',
    4 => 'abril',
    5 => 'mayo',
    6 => 'junio',
    7 => 'julio',
    8 => 'agosto',
    9 => 'septiembre',
    10 => 'octubre',
    11 => 'noviembre',
    12 => 'diciembre'
];
$mesFormateado = ucfirst($meses[(int)$monthSeleccionado]);
$fecha_actual = date('d/m/Y');

// Consulta a la base de datos
$consulta = "SELECT r.fecha, 
    MIN(r.hora_inout) AS hora_entrada,
    MAX(r.hora_inout) AS hora_salida,
    tp.nombre AS tipo_permiso
    FROM registros r
    LEFT JOIN tipo_permisos tp ON r.tipo_permiso_id = tp.id
    WHERE r.codigo = '$codigo_usuario' 
    AND YEAR(r.fecha) = $yearSeleccionado 
    AND MONTH(r.fecha) = $monthSeleccionado
    GROUP BY r.fecha
    ORDER BY r.fecha ASC";

$resultado = mysqli_query($conn, $consulta);

// Crear el documento Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Configurar encabezados
$sheet->mergeCells('A1:D1');
$sheet->setCellValue('A1', 'Ministerio de Educación, Ciencia y Tecnología')
    ->getStyle('A1')
    ->getFont()
    ->setBold(true);

$sheet->mergeCells('A2:D2');
$sheet->setCellValue('A2', 'Registro de Asistencia Diaria')
    ->getStyle('A2')
    ->getFont()
    ->setBold(true);

// Datos de la institución
$sheet->setCellValue('A4', 'Mes: ' . $mesFormateado);
$sheet->setCellValue('D4', 'Año: ' . $yearSeleccionado);
$sheet->setCellValue('A5', 'Fecha: ' . $fecha_actual);
$sheet->setCellValue('D5', 'Infraestructura: ' . $codigo_institucion);
$sheet->setCellValue('A6', 'Centro Educativo: ' . $institucion);
$sheet->setCellValue('D6', 'Código Empleado: ' . $codigo_usuario);

// Encabezados de la tabla
$sheet->setCellValue('A8', 'FECHA')
    ->getStyle('A8')
    ->getFont()
    ->setBold(true);
$sheet->setCellValue('B8', 'NOMBRE COMPLETO')
    ->getStyle('B8')
    ->getFont()
    ->setBold(true);
$sheet->setCellValue('C8', 'HORA DE ENTRADA')
    ->getStyle('C8')
    ->getFont()
    ->setBold(true);
$sheet->setCellValue('D8', 'HORA DE SALIDA')
    ->getStyle('D8')
    ->getFont()
    ->setBold(true);

// Llenar datos
$row = 9;
while ($registro = mysqli_fetch_assoc($resultado)) {
    $fecha = date('d/m/Y', strtotime($registro['fecha']));
    $hora_entrada = date("h:i A", strtotime($registro['hora_entrada']));
    $hora_salida = date("h:i A", strtotime($registro['hora_salida']));
    $tipo_permiso = $registro['tipo_permiso'];

    $sheet->setCellValue('A' . $row, $fecha);
    $sheet->setCellValue('B' . $row, $nombre_usuario);
    if ($tipo_permiso) {
        $sheet->setCellValue('C' . $row, $tipo_permiso);
        $sheet->mergeCells('C' . $row . ':D' . $row);
    } else {
        $sheet->setCellValue('C' . $row, $hora_entrada);
        $sheet->setCellValue('D' . $row, $hora_salida);
    }
    $row++;
}

// Ajustar bordes y estilos
$styleArray = [
    'borders' => [
        'allBorders' => [
            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
        ],
    ],
];
$sheet->getStyle('A8:D' . ($row - 1))->applyFromArray($styleArray);

// Centrar contenido
$sheet->getStyle('A8:D' . ($row - 1))
    ->getAlignment()
    ->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// Configurar ancho de columnas
$sheet->getColumnDimension('A')->setWidth(20);
$sheet->getColumnDimension('B')->setWidth(40);
$sheet->getColumnDimension('C')->setWidth(20);
$sheet->getColumnDimension('D')->setWidth(20);

// Enviar el archivo al navegador
ob_clean(); // Limpia el buffer de salida

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="Reporte_Asistencia.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
