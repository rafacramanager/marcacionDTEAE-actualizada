<?php
session_start();
ini_set('display_errors', '1');
include_once("bd.php");

if (!isset($conn)) {
    die("Error de conexión a la base de datos.");
}

$tipo_permiso_id = $_POST['tipo_permiso_id'] ?? null;
$usuario = mysqli_real_escape_string($conn, $_SESSION['usuario']);
$fecha = mysqli_real_escape_string($conn, $_POST['fecha']);
$hora_original = mysqli_real_escape_string($conn, $_POST['hora']);
$hora_24h = date("H:i:s", strtotime($hora_original));
$latitud = mysqli_real_escape_string($conn, $_POST['latitud']);
$longitud = mysqli_real_escape_string($conn, $_POST['longitud']);

// Verificar si ya hay un registro de entrada o salida para ese día
$query = "SELECT * FROM registros WHERE codigo = '$usuario' AND fecha = '$fecha' AND tipo_permiso_id IS NULL";
$result = mysqli_query($conn, $query);
$registroExistente = mysqli_fetch_assoc($result);

if ($tipo_permiso_id) {
    manejarPermiso($registroExistente, $usuario, $fecha, $hora_24h, $tipo_permiso_id, $latitud, $longitud);
} else {
    manejarEntradaSalida($usuario, $fecha, $hora_24h, $latitud, $longitud, $_POST['image'] ?? '');
}

function manejarPermiso($registroExistente, $usuario, $fecha, $hora_24h, $tipo_permiso_id, $latitud, $longitud)
{
    global $conn;

    if ($registroExistente) {
        alertarYRedirigir('Ya se registraron entradas/salidas para este día. No puede registrar un tipo de permiso.', 'marcacion.php');
    }

    $consulta = "INSERT INTO registros (codigo, fecha, hora_inout, tipo_permiso_id, latitud, longitud) 
                 VALUES ('$usuario', '$fecha', '$hora_24h', '$tipo_permiso_id', '$latitud', '$longitud')";
    ejecutarConsulta($consulta);
}

function manejarEntradaSalida($usuario, $fecha, $hora_24h, $latitud, $longitud, $img)
{
    if (!$img) {
        alertarYRedirigir("Debe tomar una fotografía para registrar su entrada/salida.", "marcacion.php");
    }

    $fileName = procesarImagen($img);
    $consulta = "INSERT INTO registros (codigo, fecha, hora_inout, latitud, longitud, foto) 
                 VALUES ('$usuario', '$fecha', '$hora_24h', '$latitud', '$longitud', '$fileName')";
    ejecutarConsulta($consulta);
}

function procesarImagen($img)
{
    $folderPath = "upload/";
    $image_parts = explode(";base64,", $img);

    if (count($image_parts) < 2) {
        alertarYRedirigir("Error al procesar la imagen.", "marcacion.php");
    }

    $image_base64 = base64_decode($image_parts[1]);
    $fileName = uniqid() . '.jpeg';
    $file = $folderPath . $fileName;

    if (!file_put_contents($file, $image_base64)) {
        alertarYRedirigir("Error al guardar la imagen.", "marcacion.php");
    }

    return $fileName;
}

function ejecutarConsulta($consulta)
{
    global $conn;
    $resultado = mysqli_query($conn, $consulta);
    $registro = $resultado ? "true" : "false";
    echo "<script>window.location.href = 'opciones.php?registro=$registro';</script>";
    exit();
}

function alertarYRedirigir($mensaje, $url)
{
    echo "<script>alert('$mensaje');</script>";
    echo "<script>window.location.href = '$url';</script>";
    exit();
}
