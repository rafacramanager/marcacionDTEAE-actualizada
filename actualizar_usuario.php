<?php
// Inicia la sesión para mantener datos del usuario entre páginas
session_start();

// Incluye el archivo de configuración y conexión a la base de datos
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

// Verifica si el usuario ha iniciado sesión y tiene el nivel de acceso adecuado (nivel 1)
if (!isset($_SESSION['nivel_acceso']) || $_SESSION['nivel_acceso'] != 1) {
    // Si el usuario no tiene el nivel de acceso adecuado, redirige a la página de opciones
    echo "<script>window.location.href = 'opciones.php';</script>";
    exit();
}

// Variables para mensajes de éxito, error y advertencia, y para almacenar datos de usuario
$mensajeExito = '';
$mensajeError = '';
$mensajeAdvertencia = '';
$usuarioDatos = array(
    'codigo' => '',
    'nivel' => '',
    'usuario' => '',
    'correo' => '',
    'contra' => '',
    'institucion' => '',
    'codigo_institucion' => '',
    'coordinacion' => ''
);

// Obtiene el código del usuario actual
$codigo_usuario = $_SESSION['usuario'];

// Consulta SQL para obtener el nombre de usuario correspondiente al código de usuario actual
$sql = "SELECT usuario FROM usuarios WHERE codigo = '$codigo_usuario'";
$consulta = mysqli_query($conn, $sql);

if ($row = mysqli_fetch_assoc($consulta)) {
    $nombre_usuario = $row['usuario'];
} else {
    echo "Usuario no encontrado";
}

// Verifica si se ha enviado el formulario de actualización
if (isset($_POST['actualizacion'])) {
    // Obtiene los datos enviados a través del formulario
    $usuario_id = $_POST['usuario_id'];
    $codigo = $_POST['codigo'];
    $nivel = $_POST['nivel'];
    $nombre = $_POST['usuario'];
    $contra = $_POST['contra'];
    $correo = $_POST['correo'];
    $institucion = $_POST['institucion'];
    $codigo_institucion = $_POST['codigo_institucion'];
    $coordinacion = $_POST['coordinacion'];

    if (empty($usuario_id)) {
        $mensajeAdvertencia = "Por favor, seleccione un usuario antes de actualizar los datos.";
    } else {
        // Validar la seguridad e integridad de los datos antes de usarlos en consultas SQL
        $usuario_id = mysqli_real_escape_string($conn, $usuario_id);
        $codigo = mysqli_real_escape_string($conn, $codigo);
        $nivel = mysqli_real_escape_string($conn, $nivel);
        $nombre = mysqli_real_escape_string($conn, $nombre);
        $contra = mysqli_real_escape_string($conn, $contra);
        $correo = mysqli_real_escape_string($conn, $correo);
        $institucion = mysqli_real_escape_string($conn, $institucion);
        $codigo_institucion = mysqli_real_escape_string($conn, $codigo_institucion);
        $coordinacion = mysqli_real_escape_string($conn, $coordinacion);

        // Verifica si el nuevo código ya está en uso
        $sql = "SELECT * FROM usuarios WHERE codigo = '$codigo' AND codigo != '$usuario_id'";
        $result = mysqli_query($conn, $sql);
        if (mysqli_num_rows($result) > 0) {
            $mensajeError = "El nuevo código ya está en uso. Por favor, ingrese uno nuevo.";
        } else {
            // Verifica si el nuevo correo ya está en uso
            $sql = "SELECT * FROM usuarios WHERE correo = '$correo' AND codigo != '$usuario_id'";
            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) > 0) {
                $mensajeError = "El nuevo correo ya está en uso. Por favor, ingrese otro.";
            } else {
                // Construye una lista de campos a actualizar
                $updateFields = array();

                if (!empty($nombre)) {
                    $updateFields[] = "usuario = '$nombre'";
                }
                if (!empty($contra)) {
                    $updateFields[] = "contra = '$contra'";
                }
                if (!empty($correo)) {
                    $updateFields[] = "correo = '$correo'";
                }
                if (!empty($institucion)) {
                    $updateFields[] = "institucion = '$institucion'";
                }
                if (!empty($codigo_institucion)) {
                    $updateFields[] = "codigo_institucion = '$codigo_institucion'";
                }
                if (!empty($coordinacion)) {
                    $updateFields[] = "coordinacion = '$coordinacion'";
                }

                if (!empty($updateFields)) {
                    $updateFieldsStr = implode(", ", $updateFields);
                    // Actualiza los datos del usuario en la base de datos
                    $sql = "UPDATE usuarios SET nivel = '$nivel', $updateFieldsStr WHERE codigo = '$usuario_id'";
                    if (mysqli_query($conn, $sql)) {
                        $mensajeExito = "Datos actualizados con éxito.";
                    } else {
                        $mensajeError = "Error al actualizar el usuario: " . mysqli_error($conn);
                    }
                } else {
                    $mensajeAdvertencia = "No hay datos que actualizar, por favor ingrese los nuevos datos.";
                }
            }
        }
    }
}

// Si se ha proporcionado un valor para 'usuario_id', obtiene los datos del usuario correspondientes
if (isset($_POST['usuario_id']) && !empty($_POST['usuario_id'])) {
    $usuario_id = $_POST['usuario_id'];
    $sql = "SELECT codigo, usuario, correo, contra, nivel, institucion, codigo_institucion, coordinacion FROM usuarios WHERE codigo = '$usuario_id'";
    $resultado = mysqli_query($conn, $sql);
    $usuarioDatos = mysqli_fetch_assoc($resultado);
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Actualizar Usuarios</title>
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
                    <div class="col-md-6 p-4 ps-md-0">
                        <?php
                        if (!empty($mensajeExito)) {
                            echo '<div class="alert alert-success" role="alert">' . $mensajeExito . '</div>';
                        } elseif (!empty($mensajeError)) {
                            echo '<div class="alert alert-danger" role="alert">' . $mensajeError . '</div>';
                        } elseif (!empty($mensajeAdvertencia)) {
                            echo '<div class="alert alert-warning" role="alert">' . $mensajeAdvertencia . '</div>';
                        }
                        ?>

                        <h2 class="fw-bold" id="Titulo">Editar datos de usuarios</h2>
                        <h6 class="fw-normal" id="Subtitulo">Por favor seleccione un usuario para modificar sus datos</h6>
                        <br>

                        <div class="form-container">
                            <form action="" method="POST">
                                <?php
                                // Se define una consulta SQL para seleccionar datos de usuarios de la base de datos
                                $sql = "SELECT codigo, usuario, correo, contra, nivel, institucion, codigo_institucion, coordinacion FROM usuarios";
                                $resultado = mysqli_query($conn, $sql);
                                ?>

                                <!-- Campo de búsqueda para buscar usuarios -->
                                <input type="text" id="busqueda" placeholder="Buscar usuario...">

                                <!-- Campo oculto para almacenar el código del usuario seleccionado -->
                                <input type="hidden" name="usuario_id" id="usuario_id" value="">

                                <!-- Div para mostrar los resultados de la búsqueda -->
                                <div id="resultados"></div>

                                <!-- Campos de entrada para mostrar y editar los datos del usuario -->
                                <br>
                                <div class="form-outline mb-4">
                                    <div style="display: flex; align-items: center;">
                                        <div style="flex: 1;">
                                            <h4 class="fw-bold" id="Titulo">Código</h4>
                                            <div style="position: relative;">
                                                <!-- Campo de código de usuario, inicialmente de solo lectura -->
                                                <input type="text" name="codigo" id="codigo" class="form-control" placeholder="Nuevo código" style="width: 100%" value="<?php echo $usuarioDatos['codigo']; ?>" readonly>
                                                <!-- Icono de bloqueo que llama a la función toggleLock -->
                                                <span class="lock-icon" onclick="toggleLock('codigo')" style="position: absolute; right: 10px; top: 10px; cursor: pointer;"><i class="fas fa-lock"></i></span>
                                            </div>
                                        </div>
                                        <div style="flex: 1; margin-left: 5%;">
                                            <h4 class="fw-bold" id="Titulo">T de usuario</h4>
                                            <div style="position: relative;">
                                                <!-- Campo de selección del tipo de usuario, inicialmente de solo lectura -->
                                                <select name="nivel" id="nivel" class="form-select" required readonly>
                                                    <option value="2" <?php if ($usuarioDatos['nivel'] == 2) echo 'selected'; ?>>Técnico</option>
                                                    <option value="1" <?php if ($usuarioDatos['nivel'] == 1) echo 'selected'; ?>>Administrador</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-outline mb-4">
                                    <h4 class="fw-bold" id="Titulo">Nombre</h4>
                                    <div style="position: relative;">
                                        <!-- Campo de nombre de usuario, inicialmente de solo lectura -->
                                        <input type="text" name="usuario" id="usuario" class="form-control" placeholder="Nombre de usuario" value="<?php echo $usuarioDatos['usuario']; ?>" readonly>
                                        <!-- Icono de bloqueo que llama a la función toggleLock -->
                                        <span class="lock-icon" onclick="toggleLock('usuario')" style="position: absolute; right: 10px; top: 10px; cursor: pointer;"><i class="fas fa-lock"></i></span>
                                    </div>
                                </div>

                                <div class="form-outline mb-4">
                                    <h4 class="fw-bold" id="Titulo">Contraseña</h4>
                                    <div style="position: relative;">
                                        <!-- Campo de contraseña, inicialmente de solo lectura -->
                                        <input type="text" name="contra" id="contra" class="form-control" placeholder="Contraseña" value="<?php echo $usuarioDatos['contra']; ?>" readonly>
                                        <!-- Icono de bloqueo que llama a la función toggleLock -->
                                        <span class="lock-icon" onclick="toggleLock('contra')" style="position: absolute; right: 10px; top: 10px; cursor: pointer;"><i class="fas fa-lock"></i></span>
                                    </div>
                                </div>

                                <div class="form-outline mb-4">
                                    <h4 class="fw-bold" id="Titulo">Correo</h4>
                                    <div style="position: relative;">
                                        <!-- Campo de correo electrónico, inicialmente de solo lectura -->
                                        <input type="email" name="correo" id="correo" class="form-control" placeholder="Correo electrónico" value="<?php echo $usuarioDatos['correo']; ?>" readonly>
                                        <!-- Icono de bloqueo que llama a la función toggleLock -->
                                        <span class="lock-icon" onclick="toggleLock('correo')" style="position: absolute; right: 10px; top: 10px; cursor: pointer;"><i class="fas fa-lock"></i></span>
                                    </div>
                                </div>

                                <div class="form-outline mb-4">
                                    <h4 class="fw-bold" id="Titulo">Institución</h4>
                                    <div style="position: relative;">
                                        <!-- Campo de institución, inicialmente de solo lectura -->
                                        <input type="text" name="institucion" id="institucion" class="form-control" placeholder="Institución" value="<?php echo $usuarioDatos['institucion']; ?>" readonly>
                                        <!-- Icono de bloqueo que llama a la función toggleLock -->
                                        <span class="lock-icon" onclick="toggleLock('institucion')" style="position: absolute; right: 10px; top: 10px; cursor: pointer;"><i class="fas fa-lock"></i></span>
                                    </div>
                                </div>

                                <div class="form-outline mb-4">
                                    <h4 class="fw-bold" id="Titulo">Código de Institución</h4>
                                    <div style="position: relative;">
                                        <!-- Campo de código de institución, inicialmente de solo lectura -->
                                        <input type="number" name="codigo_institucion" id="codigo_institucion" class="form-control" placeholder="Código de Institución" value="<?php echo $usuarioDatos['codigo_institucion']; ?>" readonly>
                                        <!-- Icono de bloqueo que llama a la función toggleLock -->
                                        <span class="lock-icon" onclick="toggleLock('codigo_institucion')" style="position: absolute; right: 10px; top: 10px; cursor: pointer;"><i class="fas fa-lock"></i></span>
                                    </div>
                                </div>

                                <div class="form-outline mb-4">
                                    <h4 class="fw-bold" id="Titulo">Coordinación</h4>
                                    <div style="position: relative;">
                                        <!-- Campo de coordinación, inicialmente de solo lectura -->
                                        <select name="coordinacion" id="coordinacion" class="form-select" required readonly>
                                            <option value="Proyectos Innovadores" <?php if ($usuarioDatos['coordinacion'] == 'Proyectos Innovadores') echo 'selected'; ?>>Proyectos Innovadores</option>
                                            <option value="Asistencia Técnica" <?php if ($usuarioDatos['coordinacion'] == 'Asistencia Técnica') echo 'selected'; ?>>Asistencia Técnica</option>
                                            <option value="Administradores" <?php if ($usuarioDatos['coordinacion'] == 'Administradores') echo 'selected'; ?>>Administradores</option>
                                        </select>
                                    </div>
                                </div>

                                <!-- Botón de actualización del formulario -->
                                <button type="submit" name="actualizacion" value="Actualizar" class="btn btn-primary btn-block mb-4" id="Actualizar">Actualizar</button>
                            </form>
                        </div>
                    </div>

                    <div class="col-md-6 mb-md-0 p-md-4">
                        <br><br><br><br>
                        <div class="card" id="user">
                            <svg xmlns="http://www.w3.org/2000/svg" height="20em" viewBox="0 0 640 512">
                                <style>
                                    svg {
                                        fill: #ffffff
                                    }
                                </style>
                                <path d="M144 160A80 80 0 1 0 144 0a80 80 0 1 0 0 160zm368 0A80 80 0 1 0 512 0a80 80 0 1 0 0 160zM0 298.7C0 310.4 9.6 320 21.3 320H234.7c.2 0 .4 0 .7 0c-26.6-23.5-43.3-57.8-43.3-96c0-7.6 .7-15 1.9-22.3c-13.6-6.3-28.7-9.7-44.6-9.7H106.7C47.8 192 0 239.8 0 298.7zM320 320c24 0 45.9-8.8 62.7-23.3c2.5-3.7 5.2-7.3 8-10.7c2.7-3.3 5.7-6.1 9-8.3C410 262.3 416 243.9 416 224c0-53-43-96-96-96s-96 43-96 96s43 96 96 96zm65.4 60.2c-10.3-5.9-18.1-16.2-20.8-28.2H261.3C187.7 352 128 411.7 128 485.3c0 14.7 11.9 26.7 26.7 26.7H455.2c-2.1-5.2-3.2-10.9-3.2-16.4v-3c-1.3-.7-2.7-1.5-4-2.3l-2.6 1.5c-16.8 9.7-40.5 8-54.7-9.7c-4.5-5.6-8.6-11.5-12.4-17.6l-.1-.2-.1-.2-2.4-4.1-.1-.2-.1-.2c-3.4-6.2-6.4-12.6-9-19.3c-8.2-21.2 2.2-42.6 19-52.3l2.7-1.5c0-.8 0-1.5 0-2.3s0-1.5 0-2.3l-2.7-1.5zM533.3 192H490.7c-15.9 0-31 3.5-44.6 9.7c1.3 7.2 1.9 14.7 1.9 22.3c0 17.4-3.5 33.9-9.7 49c2.5 .9 4.9 2 7.1 3.3l2.6 1.5c1.3-.8 2.6-1.6 4-2.3v-3c0-19.4 13.3-39.1 35.8-42.6c7.9-1.2 16-1.9 24.2-1.9s16.3 .6 24.2 1.9c22.5 3.5 35.8 23.2 35.8 42.6v3c1.3 .7 2.7 1.5 4 2.3l2.6-1.5c16.8-9.7 40.5-8 54.7 9.7c2.3 2.8 4.5 5.8 6.6 8.7c-2.1-57.1-49-102.7-106.6-102.7zm91.3 163.9c6.3-3.6 9.5-11.1 6.8-18c-2.1-5.5-4.6-10.8-7.4-15.9l-2.3-4c-3.1-5.1-6.5-9.9-10.2-14.5c-4.6-5.7-12.7-6.7-19-3L574.4 311c-8.9-7.6-19.1-13.6-30.4-17.6v-21c0-7.3-4.9-13.8-12.1-14.9c-6.5-1-13.1-1.5-19.9-1.5s-13.4 .5-19.9 1.5c-7.2 1.1-12.1 7.6-12.1 14.9v21c-11.2 4-21.5 10-30.4 17.6l-18.2-10.5c-6.3-3.6-14.4-2.6-19 3c-3.7 4.6-7.1 9.5-10.2 14.6l-2.3 3.9c-2.8 5.1-5.3 10.4-7.4 15.9c-2.6 6.8 .5 14.3 6.8 17.9l18.2 10.5c-1 5.7-1.6 11.6-1.6 17.6s.6 11.9 1.6 17.5l-18.2 10.5c-6.3 3.6-9.5 11.1-6.8 17.9c2.1 5.5 4.6 10.7 7.4 15.8l2.4 4.1c3 5.1 6.4 9.9 10.1 14.5c4.6 5.7 12.7 6.7 19 3L449.6 457c8.9 7.6 19.2 13.6 30.4 17.6v21c0 7.3 4.9 13.8 12.1 14.9c6.5 1 13.1 1.5 19.9 1.5s13.4-.5 19.9-1.5c7.2-1.1 12.1-7.6 12.1-14.9v-21c11.2-4 21.5-10 30.4-17.6l18.2 10.5c6.3 3.6 14.4 2.6 19-3c3.7-4.6 7.1-9.4 10.1-14.5l2.4-4.2c2.8-5.1 5.3-10.3 7.4-15.8c2.6-6.8-.5-14.3-6.8-17.9l-18.2-10.5c1-5.7 1.6-11.6 1.6-17.5s-.6-11.9-1.6-17.6l18.2-10.5zM472 384a40 40 0 1 1 80 0 40 40 0 1 1 -80 0z" />
                            </svg>
                        </div>
                    </div>
                    <style>
                        @media (max-width: 768px) {
                            .mb-md-0 {
                                display: none;
                            }
                        }
                    </style>

                </div>
            </div>
        </div>
    </div>

    <!-- Agrega una etiqueta <script> para definir una variable con los datos de los usuarios en formato JSON -->
    <script>
        var usuariosData = <?php
                            // Se realiza una consulta SQL para obtener datos de usuarios
                            $sql = "SELECT codigo, usuario, correo, contra, nivel, institucion, codigo_institucion, coordinacion FROM usuarios";
                            $resultado = mysqli_query($conn, $sql);
                            $usuarios = array();

                            // Se recorren los resultados y se almacenan en un array
                            while ($fila = mysqli_fetch_assoc($resultado)) {
                                $usuarios[] = $fila;
                            }

                            // Se convierte el array en formato JSON y se imprime
                            echo json_encode($usuarios);
                            ?>;
    </script>

    <!-- Agrega el script para cargar automáticamente los datos del usuario seleccionado -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Obtiene elementos del DOM
            const busqueda = document.getElementById("busqueda");
            const resultados = document.getElementById("resultados");

            // Agrega un evento para detectar cambios en el campo de búsqueda
            busqueda.addEventListener("input", function() {
                const textoBusqueda = busqueda.value.trim().toLowerCase();
                resultados.innerHTML = "";

                if (textoBusqueda.length > 0) {
                    usuariosData.forEach(usuario => {
                        // Compara el texto de búsqueda con el nombre de usuario
                        if (usuario.usuario.toLowerCase().includes(textoBusqueda)) {
                            const elementoUsuario = document.createElement("div");
                            elementoUsuario.textContent = usuario.usuario;

                            // Agrega un evento para cargar los datos del usuario al hacer clic
                            elementoUsuario.addEventListener("click", function() {
                                cargarDatosUsuario(usuario);
                            });

                            resultados.appendChild(elementoUsuario);
                        }
                    });
                }
            });

            // Función para cargar los datos del usuario en el formulario
            function cargarDatosUsuario(usuario) {
                document.getElementById("codigo").value = usuario.codigo;
                document.getElementById("usuario").value = usuario.usuario;
                document.getElementById("contra").value = usuario.contra;
                document.getElementById("correo").value = usuario.correo;
                document.getElementById("nivel").value = usuario.nivel;
                document.getElementById("institucion").value = usuario.institucion;
                document.getElementById("codigo_institucion").value = usuario.codigo_institucion;
                document.getElementById("coordinacion").value = usuario.coordinacion;

                // Establecer el valor del campo oculto usuario_id
                document.getElementById("usuario_id").value = usuario.codigo;
            }
        });
    </script>

    <!-- Aquí se define una función llamada toggleLock para bloquear o desbloquear campos de entrada -->
    <script>
        function toggleLock(fieldName) {
            const field = document.getElementById(fieldName);
            const lockIcon = field.nextElementSibling.firstElementChild;

            if (field.readOnly) {
                field.readOnly = false;
                lockIcon.classList.remove("fa-lock");
                lockIcon.classList.add("fa-unlock");
            } else {
                field.readOnly = true;
                lockIcon.classList.remove("fa-unlock");
                lockIcon.classList.add("fa-lock");
            }
        }
    </script>

    <!-- Agrega un evento al envío del formulario para mostrar una confirmación -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Obtiene el formulario y el botón de actualización
            const form = document.querySelector("form");
            const actualizarButton = document.getElementById("Actualizar");

            // Agrega un evento al envío del formulario
            form.addEventListener("submit", function(event) {
                // Muestra una confirmación al usuario
                const confirmacion = confirm("¿Está seguro de que desea actualizar los datos?");

                // Evita el envío del formulario si el usuario cancela la confirmación
                if (!confirmacion) {
                    event.preventDefault();
                }
            });
        });
    </script>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
</body>

</html>