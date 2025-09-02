<?php
// Inicia la sesión para mantener datos del usuario entre páginas
session_start();

// Incluye el archivo de configuración y conexión a la base de datos (bd.php)
include_once("bd.php");

// Verifica si se pudo establecer una conexión a la base de datos
if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    // Si el usuario no ha iniciado sesión, redirige a la página de inicio
    echo "<script>window.location.href = 'index.php';</script>";
    exit();
}

// Verifica si el usuario tiene el nivel de acceso adecuado (nivel 1)
if (!isset($_SESSION['nivel_acceso']) || $_SESSION['nivel_acceso'] != 1) {
    // Si el usuario no tiene el nivel de acceso adecuado, redirige a la página de opciones
    echo "<script>window.location.href = 'opciones.php';</script>";
    exit();
}

// Función para insertar un mensaje en la base de datos
function insertarMensaje($titulo, $mensaje, $fecha_expiracion)
{
    global $conn; // Usamos la conexión a la base de datos definida en bd.php

    // Recupera los datos del formulario
    $titulo = $_POST['titulo'];
    $mensaje = $_POST['mensaje'];
    $fecha_expiracion = $_POST['fecha_expiracion'];

    // Prepara la consulta para insertar el mensaje
    $stmt = $conn->prepare("INSERT INTO mensajes (titulo, mensaje, fecha_expiracion, activo) VALUES (?, ?, ?, 1)");
    $stmt->bind_param("sss", $titulo, $mensaje, $fecha_expiracion);

    // Ejecuta la inserción
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Mensaje insertado correctamente.";
    } else {
        $_SESSION['error_message'] = "Error al insertar el mensaje: " . $stmt->error;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['agregar'])) {
        // Llama a la función para insertar un nuevo mensaje
        insertarMensaje($_POST['titulo'], $_POST['mensaje'], $_POST['fecha_expiracion']);
    } elseif (isset($_POST['editar'])) {
        $id = $_POST['id'];
        $nuevoTitulo = $_POST['titulo'];
        $nuevoMensaje = $_POST['mensaje'];
        $nuevaFechaExpiracion = $_POST['fecha_expiracion'];

        // Obtener los datos actuales del mensaje
        $sql = "SELECT titulo, mensaje, fecha_expiracion FROM mensajes WHERE id = $id";
        $result = $conn->query($sql);

        if ($result->num_rows == 1) {
            $row = $result->fetch_assoc();

            // Comparar los valores actuales con los nuevos
            $tituloActual = $row['titulo'];
            $mensajeActual = $row['mensaje'];
            $fechaExpiracionActual = $row['fecha_expiracion'];

            // Verificar qué campos han cambiado
            $actualizarTitulo = $nuevoTitulo != $tituloActual;
            $actualizarMensaje = $nuevoMensaje != $mensajeActual;
            $actualizarFechaExpiracion = $nuevaFechaExpiracion != $fechaExpiracionActual;

            // Actualizar solo los campos que han cambiado
            if ($actualizarTitulo || $actualizarMensaje || $actualizarFechaExpiracion) {
                $set = [];

                if ($actualizarTitulo) {
                    $set[] = "titulo = '$nuevoTitulo'";
                }

                if ($actualizarMensaje) {
                    $set[] = "mensaje = '$nuevoMensaje'";
                }

                if ($actualizarFechaExpiracion) {
                    $set[] = "fecha_expiracion = '$nuevaFechaExpiracion'";
                }

                $sql = "UPDATE mensajes SET " . implode(', ', $set) . " WHERE id = $id";

                if ($conn->query($sql) === TRUE) {
                    $_SESSION['success_message'] = "Mensaje actualizado correctamente.";
                } else {
                    $_SESSION['error_message'] = "Error al actualizar el mensaje: " . $conn->error;
                }
            } else {
                $_SESSION['info_message'] = "Ningún cambio detectado en el mensaje.";
            }
        }
    } elseif (isset($_POST['eliminar'])) {
        eliminarMensaje($_POST['id']);
    } elseif (isset($_POST['activar'])) {
        activarMensaje($_POST['id']);
    } elseif (isset($_POST['desactivar'])) {
        desactivarMensaje($_POST['id']);
    }
}

// Función para eliminar un mensaje en la base de datos
function eliminarMensaje($id)
{
    global $conn;
    $stmt = $conn->prepare("DELETE FROM mensajes WHERE id = ?");
    $stmt->bind_param("i", $id);

    // Recupera el ID del mensaje a eliminar
    $id = $_POST['id'];

    // Ejecuta la eliminación
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Mensaje eliminado correctamente.";
    } else {
        $_SESSION['error_message'] = "Error al eliminar el mensaje: " . $stmt->error;
    }
    $stmt->close();
}

// Función para activar un mensaje en la base de datos
function activarMensaje($id)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE mensajes SET activo = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);

    // Recupera el ID del mensaje a activar
    $id = $_POST['id'];

    // Ejecuta la activación
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Mensaje activado correctamente.";
    } else {
        $_SESSION['error_message'] = "Error al activar el mensaje: " . $stmt->error;
    }
    $stmt->close();
}

// Función para desactivar un mensaje en la base de datos
function desactivarMensaje($id)
{
    global $conn;
    $stmt = $conn->prepare("UPDATE mensajes SET activo = 0 WHERE id = ?");
    $stmt->bind_param("i", $id);

    // Recupera el ID del mensaje a desactivar
    $id = $_POST['id'];

    // Ejecuta la desactivación
    if ($stmt->execute()) {
        $_SESSION['success_message'] = "Mensaje desactivado correctamente.";
    } else {
        $_SESSION['error_message'] = "Error al desactivar el mensaje: " . $stmt->error;
    }
    $stmt->close();
}

// Función para desactivar mensajes vencidos
function desactivarMensajesVencidos()
{
    global $conn;

    // Obtén la fecha actual
    $fechaActual = date("Y-m-d");

    // Actualiza los mensajes cuya fecha de expiración ha pasado
    $sql = "UPDATE mensajes SET activo = 0 WHERE fecha_expiracion < '$fechaActual'";
    $conn->query($sql);
}

// Llama a la función para desactivar mensajes vencidos
desactivarMensajesVencidos();

// Función para mostrar todos los mensajes (activos e inactivos)
function mostrarTodosLosMensajes()
{
    global $conn;
    $sql = "SELECT * FROM mensajes";
    $result = $conn->query($sql);

    echo "<h2 id='Titulo'><br>Todos los Mensajes:</h2>";

    if ($result->num_rows > 0) {
        echo "<div class='table-responsive'>";
        echo "<table class='table table-bordered'>";
        echo "<thead>";
        echo "<tr>";
        echo "<th class='table-header'>Título</th>";
        echo "<th class='table-header'>Mensaje</th>";
        echo "<th class='table-header'>Fecha de Expiración</th>";
        echo "<th class='table-header'>Acciones</th>";
        echo "</tr>";
        echo "</thead>";
        echo "<tbody>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td style='color: white; vertical-align: middle;'>{$row['titulo']}</td>";
            echo "<td style='color: white; vertical-align: middle;'>{$row['mensaje']}</td>";
            echo "<td style='color: white; vertical-align: middle;'>{$row['fecha_expiracion']}</td>";

            echo "<td>
                <form method='post' action=''>
                    <input type='hidden' name='id' value='{$row['id']}'>
                    <input type='text' name='titulo' class='form-control' id='Acciones' placeholder='Nuevo título' value='{$row['titulo']}'>
                    <input type='text' name='mensaje' class='form-control' id='Acciones' placeholder='Nuevo mensaje' value='{$row['mensaje']}'>
                    <input type='date' name='fecha_expiracion' class='form-control' id='Acciones' placeholder='Nueva fecha de expiración' value='{$row['fecha_expiracion']}'>

                    <div class='button-container' style='display: flex; align-items: center; justify-content: space-between;'>
                        <button class='btn btn-primary btn-md' type='submit' name='editar'>Editar</button>
                        <button class='btn btn-danger btn-md' type='submit' name='eliminar'>Eliminar</button>";

            if ($row['activo'] == 1) {
                echo "<button class='btn btn-warning btn-md' type='submit' name='desactivar'>Desactivar</button>";
            } else {
                echo "<button class='btn btn-success btn-md' type='submit' name='activar'>Activar</button>";
            }
            echo "</div>
                </form>
            </td>";
            echo "</tr>";
        }
        echo "</tbody>";
        echo "</table>";
        echo "</div>"; // Cierre del div table-responsive
    } else {
        echo "<p id='Subtitulo'>No hay mensajes.</p>";
    }
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mensajes</title>
    <link rel="icon" href="img/Mined.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="nav.css">
    <script src="https://kit.fontawesome.com/6305bb531f.js" crossorigin="anonymous"></script>
</head>

<body>
    <nav class="navbar navbar-dark bg-dark fixed-top navbar-custom">
        <div class="container-fluid d-flex justify-content-between">
            <a href="panel.php" class="button-back btn btn-secondary">Regresar a Panel</a>
            <button class="navbar-toggler link-style" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="offcanvasDarkNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasDarkNavbarLabel"><i class="fa-solid fa-sliders"></i> Secciones</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <a class="nav-link link-style" href="panel.php">Panel de registros</a>
                    <a class="nav-link link-style" href="registro-usuarios.php">Agregar usuarios</a>
                    <a class="nav-link link-style" href="actualizar_usuario.php">Modificar usuarios</a>
                    <a class="nav-link link-style" href="asignar_actividades.php">Asignar actividades</a>

                </div>
                <div class="offcanvas-footer">
                    <a class="nav-link link-style-close" href="cerrar_sesion.php"><i class="fa-solid fa-door-open"></i> Cerrar sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <br><br>

    <br>
    <div class="container" id="Mensajes-div">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <br>
                    <h1 align="center" id="Titulo">Panel de Mensajes</h1>
                    <br>

                    <div id="alert-container">
                        <?php
                        if (!empty($_SESSION['success_message'])) {
                            echo '<div class="alert alert-info" role="alert">' . $_SESSION['success_message'] . '</div>';
                            unset($_SESSION['success_message']);
                        }
                        if (!empty($_SESSION['error_message'])) {
                            echo '<div class="alert alert-danger" role="alert">' . $_SESSION['error_message'] . '</div>';
                            unset($_SESSION['error_message']);
                        }
                        if (!empty($_SESSION['info_message'])) {
                            echo '<div class="alert alert-info" role="alert">' . $_SESSION['info_message'] . '</div>';
                            unset($_SESSION['info_message']);
                        }
                        ?>
                    </div>

                    <div class="row gx-4 gx-md-5">
                        <div class="col-md-6">
                            <div class="card card-equal-height">
                                <div class="card-body">
                                    <h2 id="Titulo">Crear Nuevo Mensaje</h2>
                                    <form method="post" action="">
                                        <div class="mb-3">
                                            <label for="titulo" class="form-label" id="Subtitulo">Título del mensaje:</label>
                                            <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Ingrese el asunto del mensaje." required>
                                        </div>
                                        <div class="mb-3">
                                            <label for="mensaje" class="form-label" id="Subtitulo">Mensaje:</label>
                                            <textarea id="mensaje" name="mensaje" class="form-control" rows="2" placeholder="Ingrese el mensaje a publicar..." required></textarea>
                                        </div>
                                        <div class="mb-3">
                                            <label for="fecha_expiracion" class="form-label" id="Subtitulo">Fecha de Expiración del mensaje:</label>
                                            <input type="date" id="fecha_expiracion" name="fecha_expiracion" class="form-control">
                                        </div>
                                        <br>
                                        <button type="submit" name="agregar" id="Botones-men">Enviar</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card card-equal-height">
                                <br><br>
                                <svg xmlns="http://www.w3.org/2000/svg" height="20em" viewBox="0 0 640 512"><!--! Font Awesome Free 6.4.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license (Commercial License) Copyright 2023 Fonticons, Inc. -->
                                    <style>
                                        svg {
                                            fill: #ffffff
                                        }
                                    </style>
                                    <path d="M88.2 309.1c9.8-18.3 6.8-40.8-7.5-55.8C59.4 230.9 48 204 48 176c0-63.5 63.8-128 160-128s160 64.5 160 128s-63.8 128-160 128c-13.1 0-25.8-1.3-37.8-3.6c-10.4-2-21.2-.6-30.7 4.2c-4.1 2.1-8.3 4.1-12.6 6c-16 7.2-32.9 13.5-49.9 18c2.8-4.6 5.4-9.1 7.9-13.6c1.1-1.9 2.2-3.9 3.2-5.9zM0 176c0 41.8 17.2 80.1 45.9 110.3c-.9 1.7-1.9 3.5-2.8 5.1c-10.3 18.4-22.3 36.5-36.6 52.1c-6.6 7-8.3 17.2-4.6 25.9C5.8 378.3 14.4 384 24 384c43 0 86.5-13.3 122.7-29.7c4.8-2.2 9.6-4.5 14.2-6.8c15.1 3 30.9 4.5 47.1 4.5c114.9 0 208-78.8 208-176S322.9 0 208 0S0 78.8 0 176zM432 480c16.2 0 31.9-1.6 47.1-4.5c4.6 2.3 9.4 4.6 14.2 6.8C529.5 498.7 573 512 616 512c9.6 0 18.2-5.7 22-14.5c3.8-8.8 2-19-4.6-25.9c-14.2-15.6-26.2-33.7-36.6-52.1c-.9-1.7-1.9-3.4-2.8-5.1C622.8 384.1 640 345.8 640 304c0-94.4-87.9-171.5-198.2-175.8c4.1 15.2 6.2 31.2 6.2 47.8l0 .6c87.2 6.7 144 67.5 144 127.4c0 28-11.4 54.9-32.7 77.2c-14.3 15-17.3 37.6-7.5 55.8c1.1 2 2.2 4 3.2 5.9c2.5 4.5 5.2 9 7.9 13.6c-17-4.5-33.9-10.7-49.9-18c-4.3-1.9-8.5-3.9-12.6-6c-9.5-4.8-20.3-6.2-30.7-4.2c-12.1 2.4-24.7 3.6-37.8 3.6c-61.7 0-110-26.5-136.8-62.3c-16 5.4-32.8 9.4-50 11.8C279 439.8 350 480 432 480z" />
                                </svg>
                                <br><br>
                            </div>
                        </div>
                    </div>

                    <style>
                        @media (max-width: 768px) {
                            .gx-md-5 .col-md-6:last-child {
                                display: none;
                            }
                        }
                    </style>

                </div>
                <br><br>
                <?php
                mostrarTodosLosMensajes();
                ?>
            </div>
        </div>
    </div>
    <br>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>

</body>

</html>