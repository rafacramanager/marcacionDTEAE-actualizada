<?php
session_start();
include_once("bd.php");

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    echo "<script>window.location.href = 'index.php';</script>";
    exit();
}

$codigo_usuario = $_SESSION['usuario'];
// Verifica si el usuario ha iniciado sesión y tiene el nivel de acceso adecuado
if (!isset($_SESSION['nivel_acceso']) || $_SESSION['nivel_acceso'] != 1) {
    // Si el usuario no tiene el nivel de acceso adecuado, redirige al inicio
    echo "<script>window.location.href = 'opciones.php';</script>";
    exit(); // Asegura que el script se detenga después de redirigir
}
if ($codigo_usuario === '$usuario') {
    $nombre_usuario = '$usuario';
} else {
    // Realiza una consulta para obtener el nombre de usuario
    $sql = "SELECT usuario FROM usuarios WHERE codigo = '$codigo_usuario'";
    $consulta = mysqli_query($conn, $sql);

    if ($row = mysqli_fetch_assoc($consulta)) {
        $nombre_usuario = $row['usuario'];
    } else {
        echo "Usuario no encontrado";
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Panel de Administradores</title>
    <link rel="icon" href="img/Mined.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="nav.css">
    <script src="https://kit.fontawesome.com/6305bb531f.js" crossorigin="anonymous"></script>

</head>

<body>

    <nav class="navbar navbar-dark bg-dark fixed-top navbar-custom">
        <div class="container-fluid d-flex justify-content-end">
            <button class="navbar-toggler link-style" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasDarkNavbar" aria-controls="offcanvasDarkNavbar" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="offcanvas offcanvas-end text-bg-dark" tabindex="-1" id="offcanvasDarkNavbar" aria-labelledby="offcanvasDarkNavbarLabel">
                <div class="offcanvas-header">
                    <h5 class="offcanvas-title" id="offcanvasDarkNavbarLabel"><i class="fa-solid fa-sliders"></i> Secciones</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
                </div>
                <div class="offcanvas-body">
                    <a class="nav-link link-style" href="registro-usuarios.php">Agregar usuarios</a>
                    <a class="nav-link link-style" href="actualizar_usuario.php">Modificar usuarios</a>
                    <a class="nav-link link-style" href="mensajes.php">Agregar Mensajes</a>
                    <a class="nav-link link-style" href="asignar_actividades.php">Asignar actividades</a>
                </div>
                <div class="offcanvas-footer">
                    <a class="nav-link link-style-close" href="cerrar_sesion.php"><i class="fa-solid fa-door-open"></i> Cerrar cesión</a>
                </div>
            </div>
        </div>
    </nav>

    <br><br><br><br>
    <div class="container-fluid text-center">
        <br><br>
        <h1 class="fw-bold" id="Titulo">Panel de Consultas para Administradores</h1>
        <br>
        <h1 class="fw-bold" id="nombre-root"><?php echo $nombre_usuario; ?></h1>
        <br>
        <div class="container-fluid text-center">
            <div class="row">
                <div class="col-12 col-md-6 offset-md-3">
                    <form method="POST" action="" enctype="multipart/form-data" id="formularioBusqueda">
                        <label for="usuario">
                            <h5 class="fw-bold" id="Texto">Seleccione un método para consultar los registros:</h5>
                        </label>
                        <div class="mb-3">
                            <br><br>

                            <!-- Agrega el campo de búsqueda en tiempo real aquí -->
                            <div class="search-container">
                                <input type="text" id="searchInput" placeholder="Ingrese el nombre de un usuario" name="usuarios">
                                <ul id="results"></ul>
                            </div>

                            <!-- Esto son los elementos que se seleccionan según el search, esto no se muestra en pantalla -->
                            <?php
                            $sql = "SELECT codigo, usuario FROM usuarios";
                            $resultado = mysqli_query($conn, $sql);
                            ?>
                            <select class="form-control" id="selectBox" name="usuarios">
                                <option value="" id="Select-form">Seleccione un empleado en específico</option>
                                <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>
                                    <option value="<?php echo $fila['codigo']; ?>" <?php if (isset($_POST['usuarios']) && $_POST['usuarios'] === $fila['codigo']) echo 'selected'; ?>><?php echo $fila['usuario']; ?></option>
                                <?php } ?>
                            </select>

                            <!--  esto oculta todos los usuarios para que solo se puedan filtran por nombre, en ves de salir todos de un solo -->
                            <!-- para que se vea el selectbox comentar el siguiente script  -->
                            <script>
                                document.addEventListener("DOMContentLoaded", function() {
                                    const selectBox = document.getElementById("selectBox");
                                    selectBox.style.display = "none";
                                });
                            </script>

                            <!-- Estos son los filtros de dia y meses -->

                            <div style="margin: 20px 2px 20px; display: flex; align-items: center;">
                                <input class="form-control" type="date" id="Select-form" name="fecha" style="margin-right: 5px;" placeholder="Seleccione una fecha en específico" <?php if (isset($_POST['fecha'])) echo 'value="' . htmlspecialchars($_POST['fecha']) . '"'; ?>>

                                <select class="form-control" id="Select-form" name="mes" style="margin-right: 5px;">
                                    <option value="" <?php if (!isset($_POST['mes']) || $_POST['mes'] === '') echo 'selected'; ?>>Seleccione un mes en específico</option>
                                    <option value="1" <?php if (isset($_POST['mes']) && $_POST['mes'] == '1') echo 'selected'; ?>>Enero</option>
                                    <option value="2" <?php if (isset($_POST['mes']) && $_POST['mes'] == '2') echo 'selected'; ?>>Febrero</option>
                                    <option value="3" <?php if (isset($_POST['mes']) && $_POST['mes'] == '3') echo 'selected'; ?>>Marzo</option>
                                    <option value="4" <?php if (isset($_POST['mes']) && $_POST['mes'] == '4') echo 'selected'; ?>>Abril</option>
                                    <option value="5" <?php if (isset($_POST['mes']) && $_POST['mes'] == '5') echo 'selected'; ?>>Mayo</option>
                                    <option value="6" <?php if (isset($_POST['mes']) && $_POST['mes'] == '6') echo 'selected'; ?>>Junio</option>
                                    <option value="7" <?php if (isset($_POST['mes']) && $_POST['mes'] == '7') echo 'selected'; ?>>Julio</option>
                                    <option value="8" <?php if (isset($_POST['mes']) && $_POST['mes'] == '8') echo 'selected'; ?>>Agosto</option>
                                    <option value="9" <?php if (isset($_POST['mes']) && $_POST['mes'] == '9') echo 'selected'; ?>>Septiembre</option>
                                    <option value="10" <?php if (isset($_POST['mes']) && $_POST['mes'] == '10') echo 'selected'; ?>>Octubre</option>
                                    <option value="11" <?php if (isset($_POST['mes']) && $_POST['mes'] == '11') echo 'selected'; ?>>Noviembre</option>
                                    <option value="12" <?php if (isset($_POST['mes']) && $_POST['mes'] == '12') echo 'selected'; ?>>Diciembre</option>
                                </select>
                            </div>
                            <button type="submit" id="Registro" name="consultar">Consultar</button>
                            <button type="button" id="Limpiar" onclick="window.location.href = 'panel.php'"><i class="fa-solid fa-trash-can" style="color: #fab005;"></i></button>

                        </div>
                    </form>
                </div>
            </div>
        </div>

        <br>

        <?php
        if (isset($_POST['consultar'])) {
            $usuario = $_POST['usuarios'];
            $fecha = $_POST['fecha'];
            $mes = $_POST['mes'];

            $sql = "SELECT * FROM registros WHERE 1"; // Query base para seleccionar todos los registros

            if (!empty($usuario)) {
                $sql .= " AND codigo = '$usuario'";
            }

            if (!empty($fecha)) {
                $sql .= " AND fecha = '$fecha'";
            }

            if (!empty($mes)) {
                $sql .= " AND EXTRACT(MONTH FROM fecha) = '$mes'";
            }

            $sql .= " ORDER BY fecha ASC, hora_inout ASC"; // Ordena los registros por la fecha y la hora

            $consulta = mysqli_query($conn, $sql);
            if (!$consulta) {
                die("Error en la consulta: " . mysqli_error($conn));
            }

        ?>
            <h5 id="Subtitulo">Código de Usuario: <span class='codigo'><?php echo $usuario ?></span></h5>

            <div class="table-responsive">
                <table width="auto" class="table" id="Tabla">
                    <thead align="center" class="thead-dark">
                        <tr align="center">
                            <th class="text-center">
                                <h6 class="fw-bold" id="Subtitulo-tabla">Código de Usuario</h6>
                            </th>
                            <th class="text-center">
                                <h6 class="fw-bold" id="Subtitulo-tabla">Nombre</h6>
                            </th>
                            <th class="text-center">
                                <h6 class="fw-bold" id="Subtitulo-tabla">Fecha de marcación</h6>
                            </th>
                            <th class="text-center">
                                <h6 class="fw-bold" id="Subtitulo-tabla">Hora de marcación</h6>
                            </th>
                            <th class="text-center">
                                <h6 class="fw-bold" id="Subtitulo-tabla">Coordenadas</h6>
                            </th>
                            <th class="text-center">
                                <h6 class="fw-bold" id="Subtitulo-tabla">Ver ubicación</h6>
                            </th>
                            <th class="text-center">
                                <h6 class="fw-bold" id="Subtitulo-tabla">Foto</h6>
                            </th>
                        </tr>
                    </thead>
                    <tbody align="center">
                        <?php
                        $n = 0;
                        while ($row = mysqli_fetch_array($consulta)) {
                            $n++;
                            // Consulta para obtener el nombre del usuario
                            $codigo_usuario = $row['codigo'];
                            $nombre_usuario_query = mysqli_query($conn, "SELECT usuario FROM usuarios WHERE codigo = '$codigo_usuario'");
                            $nombre_usuario_row = mysqli_fetch_assoc($nombre_usuario_query);
                            $nombre_usuario = $nombre_usuario_row['usuario'];

                            // Cambiar el idioma a español
                            setlocale(LC_TIME, 'es_ES.UTF-8');

                            // Formatear la fecha en el nuevo formato (jueves 10-09-2023)
                            $fechaFormateada = strftime('%A %d-%m-%Y', strtotime($row['fecha']));

                            // Formatear la hora con "a.m." o "p.m."
                            $hora = date('h:i A', strtotime($row['hora_inout']));
                            echo "<tr>";
                            echo "<td class='codigo-usuario'>" . $codigo_usuario . "</td>"; // Código de usuario con clase CSS
                            echo "<td>" . $nombre_usuario . "</td>"; // Nombre de usuario
                            echo "<td class='fecha-marcacion'>" . $fechaFormateada . "</td>"; // Fecha de marcación con clase CSS
                            echo "<td class='hora-marcacion'>" . $hora . "</td>"; // Hora de marcación con clase CSS
                            echo "<td class='coordenadas'>" . $row['latitud'] . ", " . $row['longitud'] . "</td>"; // Coordenadas con clase CSS
                            echo "<td><a href='https://www.google.com/maps?q=" . $row['latitud'] . "," . $row['longitud'] . "' target='_blank'><img class='icono' onmouseover='bigImg(this)' onmouseout='normalImg(this)' border='0' src='img/point.png' width='20px' ></a></td>"; // Ver ubicación
                            echo "<td><img onclick='javascript:this.height=600;this.width=300' ondblclick='javascript:this.width=100;this.height=250' class='img-thumbnail' width='90' src='upload/" . $row['foto'] . "'></td>"; // Foto
                            echo "</tr>";
                        }
                        ?>
                        <?php
                        if ($n == 0) {
                            echo "<div class='alert alert-success'>No se han encontrado registros</div>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            <br><br>
        <?php
        } //end if isset($_POST['consultar'])
        ?>

        <script>
            function bigImg(img) {
                img.style.transform = "scale(1.4)"; // Cambia el tamaño a 1.2 veces el tamaño original
            }

            function normalImg(img) {
                img.style.transform = "scale(1)"; // Vuelve al tamaño original
            }

            function limpiarBusqueda() {
                // Limpiar los campos de selección y fecha
                document.getElementById("selectBox").selectedIndex = 0;
                document.getElementById("fecha").value = '';
                document.getElementById("mes").selectedIndex = 0;

                // Enviar el formulario vacío para actualizar la página
                document.forms["formularioBusqueda"].submit();
            }
        </script>



        <script src="script.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
</body>

</html>