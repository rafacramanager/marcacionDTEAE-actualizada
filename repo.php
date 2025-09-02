<?php
session_start();
require('fpdf/fpdf.php');
include_once("bd.php");

// Limpiar el bucle de salida
ob_clean();

class PDF extends FPDF
{
    private $meses = array(
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
    );

    function Header()
    {
        // Formato del mes y año
        $mes = $_SESSION['mes'];
        $mesArray = explode('-', $mes);
        $mesNum = (int)$mesArray[1];
        $mesFormateado = ucfirst($this->meses[$mesNum]);
        $yearSeleccionado = $mesArray[0];
        $fecha_actual = strftime("%A %e de %B de %Y", time());

        // Agregar detalles de la institución, infraestructura y empleado
        $centro_educativo = isset($_SESSION['centro_educativo']) && !empty($_SESSION['centro_educativo']) ? $_SESSION['centro_educativo'] : 'No disponible';
        $codigo_infraestructura = isset($_SESSION['codigo_infraestructura']) && !empty($_SESSION['codigo_infraestructura']) ? $_SESSION['codigo_infraestructura'] : 'No disponible';
        $codigo_empleado = isset($_SESSION['codigo_usuario']) ? $_SESSION['codigo_usuario'] : 'No disponible';

        // Configuración de la fuente
        $this->SetFont('Arial', 'B', size: 14);
        $this->Cell(45);
        $this->SetTextColor(0, 0, 0);
        $this->Cell(100, 5, utf8_decode('Ministerio de Educación, Ciencia y Tecnología'), 0, 1, 'C', 0);
        $this->Cell(190, 5, utf8_decode('Registro de Asistencia Diaria'), 0, 1, 'C', 0);
        $this->Cell(190, 5, utf8_decode('Tecnologías Emergentes Aplicadas a la Educación'), 0, 1, 'C', 0);

        $this->Ln(5);
        $this->SetFont('Arial', 'B', 12);

        // Primera columna
        $this->Cell(95, 5, utf8_decode("Mes: $mesFormateado"), 0, 0, 'L', 0);
        $this->Cell(95, 5, utf8_decode("Año: $yearSeleccionado"), 0, 1, 'R', 0);

        $this->SetFont('Arial', '', 12);
        $this->Cell(95, 5, utf8_decode("Fecha: $fecha_actual"), 0, 0, 'L', 0);
        $this->Cell(95, 5, utf8_decode("Infraestructura: $codigo_infraestructura"), 0, 1, 'R', 0);

        $this->Cell(95, 5, utf8_decode("Centro Educativo: $centro_educativo"), 0, 0, 'L', 0);
        $this->Cell(95, 5, utf8_decode("Código Empleado: $codigo_empleado"), 0, 1, 'R', 0);

        $this->Ln(5);

        $this->SetFillColor(175, 175, 175);
        $this->SetTextColor(0, 0, 0);
        $this->SetDrawColor(0, 0, 0);
        $this->SetFont('Arial', 'B', 10);

        $this->Cell(30, 10, utf8_decode('FECHA'), 1, 0, 'C', 1);
        $this->Cell(80, 10, utf8_decode('NOMBRE COMPLETO'), 1, 0, 'C', 1);
        $this->Cell(40, 10, utf8_decode('HORA DE ENTRADA'), 1, 0, 'C', 1);
        $this->Cell(40, 10, utf8_decode('HORA DE SALIDA'), 1, 1, 'C', 1);
    }
    function Footer()
    {
        $this->SetY(-30);
        $this->SetFont('Arial', '', 10);
        $this->Cell(0, 5, utf8_decode('Nombre, Firma de quien registró asistencia: ___________________________________________________________'), 0, 1, 'L', 0);
        $this->Ln(5);
        $this->Cell(0, 5, utf8_decode('Nombre, Firma y sello de Vo. Bo. Gerente/Director de Área o Titular: Maira Celina Serrano Jiménez'), 0, 1, 'L', 0);
    }
}

function formatearFecha($fecha)
{
    return date('j/n/Y', strtotime($fecha));
}

function formatearHora($hora)
{
    $horaFormateada = date("h:i A", strtotime($hora));
    return $horaFormateada;
}

// Establece la configuración regional en español
setlocale(LC_TIME, 'es_ES');

$pdf = new PDF();
$pdf->AddPage();
$pdf->AliasNbPages();

$codigo_usuario = $_SESSION['usuario'];
$mes = $_SESSION['mes'];

if (isset($_POST['mes'])) {
    $mesSeleccionado = $_POST['mes'];
    $yearSeleccionado = date('Y', strtotime($mesSeleccionado));
    $monthSeleccionado = date('m', strtotime($mesSeleccionado));
}

$consulta = "SELECT r.codigo, r.fecha, 
    MIN(r.hora_inout) AS hora_entrada,
    MAX(r.hora_inout) AS hora_salida,
    tp.nombre AS tipo_permiso
    FROM registros r
    LEFT JOIN tipo_permisos tp ON r.tipo_permiso_id = tp.id
    WHERE r.codigo = '$codigo_usuario' AND YEAR(r.fecha) = $yearSeleccionado AND MONTH(r.fecha) = $monthSeleccionado
    GROUP BY r.codigo, r.fecha";

$resultado = mysqli_query($conn, $consulta);

$pdf->SetFont('Arial', '', 12);
$pdf->SetDrawColor(0, 0, 0);

while ($row = $resultado->fetch_assoc()) {
    $fechaFormateada = formatearFecha($row['fecha']);
    $hora_entrada = formatearHora($row['hora_entrada']);
    $hora_salida = formatearHora($row['hora_salida']);
    $nombre_usuario = $_SESSION['nombre_usuario'];
    $tipo_permiso = $row['tipo_permiso'];

    $pdf->Cell(30, 10, utf8_decode($fechaFormateada), 1, 0, 'C', 0);
    $pdf->Cell(80, 10, utf8_decode($nombre_usuario), 1, 0, 'C', 0);

    if ($tipo_permiso) {
        $pdf->Cell(80, 10, utf8_decode($tipo_permiso), 1, 1, 'C', 0);
    } else {
        $pdf->Cell(40, 10, utf8_decode($hora_entrada), 1, 0, 'C', 0);
        $pdf->Cell(40, 10, utf8_decode($hora_salida), 1, 1, 'C', 0);
    }
}

$pdf->Output('Asistencia.pdf', 'I');
