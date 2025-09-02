<?php
session_start();
include_once("bd.php");

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

$codigo_usuario = $_SESSION['usuario'];

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    echo "<script>window.location.href = 'index.php';</script>";
    exit();
}

// Verifica si el usuario ha iniciado sesión y tiene el nivel de acceso adecuado
if (!isset($_SESSION['usuario']) || !isset($_SESSION['nivel_acceso']) || $_SESSION['nivel_acceso'] != 1) {
    // Si el usuario no ha iniciado sesión o no tiene el nivel de acceso adecuado, redirige al inicio
    echo "<script>window.location.href = 'index.php';</script>";
    exit(); // Asegura que el script se detenga después de redirigir
}

// Definir variables para mensajes de éxito y error
$mensajeExito = '';
$mensajeError = '';

// Manejar el registro si se envió el formulario
if (isset($_POST['registro'])) {
    $usuario = $_POST['usuario'];
    $codigo = $_POST['codigo'];
    $correo = $_POST['correo'];
    $contra = $_POST['contra'];
    $nivel = $_POST['nivel'];
    $institucion = $_POST['institucion'];
    $codigo_institucion = $_POST['codigo_institucion'];
    $coordinacion = $_POST['coordinacion'];

    // Verificar si el código ya existe en la tabla
    $stmt = $conn->prepare("SELECT codigo FROM usuarios WHERE codigo = ?");
    $stmt->bind_param("s", $codigo);
    $stmt->execute();
    $stmt->store_result();

    // Verificar si el correo ya existe en la tabla
    $stmt2 = $conn->prepare("SELECT correo FROM usuarios WHERE correo = ?");
    $stmt2->bind_param("s", $correo);
    $stmt2->execute();
    $stmt2->store_result();

    // Comprobar si el código ya existe
    if ($stmt->num_rows > 0) {
        $mensajeErrorCodigo = 'El código ya está en uso.';
    }

    // Comprobar si el correo ya existe
    if ($stmt2->num_rows > 0) {
        $mensajeErrorCorreo = 'El correo ya está en uso.';
    }

    // Si ni el código ni el correo están en uso, insertar el nuevo registro
    if (empty($mensajeErrorCodigo) && empty($mensajeErrorCorreo)) {
        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, codigo, correo, contra, nivel, institucion, codigo_institucion, coordinacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssisss", $usuario, $codigo, $correo, $contra, $nivel, $institucion, $codigo_institucion, $coordinacion);

        if ($stmt->execute()) {
            // Mensaje de éxito
            $mensajeExito = 'Usuario creado exitosamente.';
        } else {
            // Mensaje de error
            $mensajeError = 'No se ha podido crear el usuario, por favor vuelva a intentarlo.';
        }
    }

    // Cerrar las declaraciones preparadas
    $stmt->close();
    $stmt2->close();
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear Usuarios</title>
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
                    <a class="nav-link link-style" href="actualizar_usuario.php">Modificar usuarios</a>
                    <a class="nav-link link-style" href="mensajes.php">Agregar Mensajes</a>
                    <a class="nav-link link-style" href="asignar_actividades.php">Asignar actividades</a>
                </div>
                <div class="offcanvas-footer">
                    <a class="nav-link link-style-close" href="cerrar_sesion.php"><i class="fa-solid fa-door-open"></i> Cerrar sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <br><br>


    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="row g-0 bg-body-secondary position-relative" id="Login">
                    <div class="col-md-6 mb-md-0 p-md-4">
                        <br><br><br><br>
                        <div class="card d-flex justify-content-center align-items-center" id="user">
                            <svg xmlns="http://www.w3.org/2000/svg" class="card-img-top" id="user-image" height="20em" viewBox="0 0 640 512">
                                <style>
                                    svg {
                                        fill: #ffffff;
                                    }
                                </style>
                                <path d="M96 128a128 128 0 1 1 256 0A128 128 0 1 1 96 128zM0 482.3C0 383.8 79.8 304 178.3 304h91.4C368.2 304 448 383.8 448 482.3c0 16.4-13.3 29.7-29.7 29.7H29.7C13.3 512 0 498.7 0 482.3zM504 312V248H440c-13.3 0-24-10.7-24-24s10.7-24 24-24h64V136c0-13.3 10.7-24 24-24s24 10.7 24 24v64h64c13.3 0 24 10.7 24 24s-10.7 24-24 24H552v64c0 13.3-10.7 24-24 24s-24-10.7-24-24z" />
                            </svg>
                        </div>
                    </div>
                    <style>
                        /* Media query para ajustar el tamaño del svg en dispositivos móviles */
                        @media (max-width: 768px) {
                            #user-image {
                                height: 5em;
                                /* Ajusta el tamaño como desees */
                            }
                        }
                    </style>

                    <div class="col-md-6 p-4 ps-md-0">
                        <!-- Mostrar mensajes de error para código y correo si es necesario -->
                        <?php
                        if (!empty($mensajeErrorCodigo)) {
                            echo '<div class="alert alert-danger" role="alert">' . $mensajeErrorCodigo . '</div>';
                        }

                        if (!empty($mensajeErrorCorreo)) {
                            echo '<div class="alert alert-danger" role="alert">' . $mensajeErrorCorreo . '</div>';
                        }
                        ?>

                        <!-- Mostrar mensaje de éxito o error general -->
                        <?php
                        if (!empty($mensajeExito)) {
                            echo '<div class="alert alert-success" role="alert">' . $mensajeExito . '</div>';
                        } elseif (!empty($mensajeError)) {
                            echo '<div class="alert alert-danger" role="alert">' . $mensajeError . '</div>';
                        }
                        ?>

                        <h2 class="fw-bold" id="Titulo">Crear Usuario</h2>
                        <h6 class="fw-normal" id="Subtitulo">Por favor ingrese los datos para crear una cuenta</h6>
                        <br><br>

                        <div class="form-container">
                            <form action="" method="POST">
                                <div class="form-outline mb-4">
                                    <div style="display: flex; align-items: center;">
                                        <div style="flex: 1;">
                                            <h4 class="fw-bold" id="Titulo">Código</h4>
                                            <input type="text" name="codigo" id="codigo" class="form-control" placeholder="Ingrese el código del empleado" style="width: 100%;" required>
                                        </div>
                                        <div style="flex: 1; margin-left: 5%;">
                                            <h4 class="fw-bold" id="Titulo">T de usuario</h4>
                                            <select name="nivel" id="nivel" class="form-select" required>
                                                <option value="2">Técnico</option>
                                                <option value="1">Administrador</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-outline mb-4">
                                    <h4 class="fw-bold" id="Titulo">Nombre</h4>

                                    <input type="text" name="usuario" id="usuario" class="form-control" placeholder="Ingrese el nombre completo del usuario" required>
                                </div>

                                <div class="form-outline mb-4">
                                    <h4 class="fw-bold" id="Titulo">Contraseña</h4>

                                    <input type="text" name="contra" id="contra" class="form-control" placeholder="Ingrese la contraseña del usuario" required>
                                </div>

                                <div class="form-outline mb-4">
                                    <h4 class="fw-bold" id="Titulo">Correo</h4>

                                    <input type="email" name="correo" id="correo" class="form-control" placeholder="Ingrese el correo electrónico del usuario" required>
                                </div>

                                <div class="form-outline mb-4">
                                    <h4 class="fw-bold" id="Titulo">Institución</h4>

                                    <input type="text" name="institucion" id="institucion" class="form-control" placeholder="Ingrese la institución del usuario">
                                </div>

                                <div class="form-outline mb-4">
                                    <h4 class="fw-bold" id="Titulo">Código de Institución</h4>

                                    <input type="number" name="codigo_institucion" id="codigo_institucion" class="form-control" placeholder="Ingrese el código de la institución">
                                </div>

                                <div class="form-outline mb-4">
                                    <h4 class="fw-bold" id="Titulo">Coordinación</h4>

                                    <select name="coordinacion" id="coordinacion" class="form-select" required>
                                        <option value="Proyectos Innovadores">Proyectos Innovadores</option>
                                        <option value="Asistencia Técnica">Asistencia Técnica</option>
                                        <option value="Administradores">Administradores</option>
                                    </select>
                                </div>

                                <button type="submit" name="registro" value="Registrar" class="btn btn-primary btn-block mb-4" id="Registro">Registrar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>

</body>

</html>