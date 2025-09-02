<!-- Aquí va toda la configuración del servidor, se recomienda que este sea el único archivo en no modificar después de su implementación-->

<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "marcacion";

// Crear la conexión
$conn = new mysqli($servername, $username, $password, $dbname);
if (!$conn) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}
