<?php
session_start();
include_once("bd.php");

if (!isset($_SESSION['usuario'])) {
    echo "<script> window.location.href = 'index.php'</script>";
    exit();
}

if (!isset($_SESSION['nivel_acceso']) || $_SESSION['nivel_acceso'] != 2) {
    echo "<script>window.location.href = 'panel.php';</script>";
    exit();
}

$codigo_usuario = $_SESSION['usuario'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['consultar'])) {
    $mesSeleccionado = $_POST['mes'];
    $yearSeleccionado = date('Y', strtotime($mesSeleccionado));
    $monthSeleccionado = date('m', strtotime($mesSeleccionado));

    // Configurar variables de sesión
    $_SESSION['mes'] = $mesSeleccionado;

    // Obtener datos del usuario
    $sql_usuario = "SELECT usuario, institucion, codigo_institucion, coordinacion FROM usuarios WHERE codigo = '$codigo_usuario'";
    $consulta_usuario = mysqli_query($conn, $sql_usuario);

    if ($row = mysqli_fetch_assoc($consulta_usuario)) {
        $nombre_usuario = $row['usuario'];
        $institucion = $row['institucion'];
        $codigo_institucion = $row['codigo_institucion'];
        $coordinacion = $row['coordinacion'];

        $_SESSION['centro_educativo'] = $institucion;
        $_SESSION['codigo_infraestructura'] = $codigo_institucion;
        $_SESSION['nombre_usuario'] = $nombre_usuario;
        $_SESSION['coordinacion'] = $coordinacion;

        // Asegurar que el código del usuario también se guarda correctamente
        $_SESSION['codigo_usuario'] = $codigo_usuario;

        // Realizar la consulta de registros
        $sql = "SELECT * FROM registros 
                WHERE codigo = '$codigo_usuario' 
                AND YEAR(fecha) = $yearSeleccionado 
                AND MONTH(fecha) = $monthSeleccionado 
                ORDER BY fecha DESC, hora_inout DESC";

        $consulta = mysqli_query($conn, $sql);
    } else {
        echo "Usuario no encontrado";
    }
}
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Consulta</title>
    <link rel="icon" href="img/Mined.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <script src="https://kit.fontawesome.com/6305bb531f.js" crossorigin="anonymous"></script>
</head>

<body>
    <br>
    <div class="container-fluid text-center">
        <!-- Botones de navegación -->
        <div class="row">
            <div class="col">
                <a type="button" id="Botones" href="opciones.php">Regresar</a>
            </div>
            <div class="col">
                <a type="button" id="Botones" href="cerrar_sesion.php">Salir</a>
            </div>
        </div>
        <img class="img-fluid" src="img\Mined-Letras.png" alt="Logo" id="Logo">


        <!-- Título de la página -->
        <h1 class="fw-bold" id="Titulo">Reporte de Marcaciones</h1>

        <!-- Nombre del usuario -->
        <div>
            <h2 class="fw-bold" id="Titulo">
                Usuario: <span class='usuario nombre-usuario'><?= isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : '' ?></span>
                Código: <span class='usuario codigo-usuario'><?= isset($_SESSION['usuario']) ? $_SESSION['usuario'] : '' ?></span>
            </h2>
        </div>

        <br>

        <form method="POST" action="">
            <label for="mes">
                <span class="fw-bold" id="Subtitulo">Selecciona el mes y año:</span>
            </label>
            <br><br>
            <input class="form-control custom-month-input" type="month" name="mes" required value="<?= isset($_POST['mes']) ? htmlspecialchars($_POST['mes']) : '' ?>">
            <br>
            <div align="center">
                <button type="submit" class="button-btn btn btn-primary" name="consultar">Consultar Marcaciones</button>
            </div>
        </form>

        <?php if (isset($consulta)): ?>

            <!-- Mostrar un solo botón "Generar" que abre un modal -->
            <div class="d-flex justify-content-center mt-4">
                <!-- Botón Generar -->
                <button type="button" class="button-btn btn btn-secondary" data-bs-toggle="modal" data-bs-target="#exportModal">
                    Exportar Marcaciones
                </button>
            </div>

            <br><br>

            <!-- Modal para seleccionar el formato -->
            <div class="modal fade" id="exportModal" tabindex="-1" aria-labelledby="exportModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="exportModalLabel">Seleccionar formato de exportación</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" align="center">
                            <form action="repo.php" method="post" target="_blank" class="mb-3">
                                <input type="hidden" name="mes" value="<?= $_SESSION['mes']; ?>">
                                <input type="hidden" name="nombre_usuario" value="<?= isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : ''; ?>">
                                <input type="hidden" name="codigo_usuario" value="<?= isset($_SESSION['codigo_usuario']) ? $_SESSION['codigo_usuario'] : ''; ?>">
                                <input type="hidden" name="institucion" value="<?= isset($_SESSION['institucion']) ? $_SESSION['institucion'] : ''; ?>">
                                <input type="hidden" name="codigo_institucion" value="<?= isset($_SESSION['codigo_institucion']) ? $_SESSION['codigo_institucion'] : ''; ?>">

                                <button type="submit" class="button-btn btn btn-secondary">
                                    Generar PDF
                                </button>
                            </form>

                            <form action="repo_excel.php" method="post" target="_blank">
                                <input type="hidden" name="mes" value="<?= $_SESSION['mes']; ?>">
                                <input type="hidden" name="nombre_usuario" value="<?= isset($_SESSION['nombre_usuario']) ? $_SESSION['nombre_usuario'] : ''; ?>">
                                <input type="hidden" name="codigo_usuario" value="<?= isset($_SESSION['codigo_usuario']) ? $_SESSION['codigo_usuario'] : ''; ?>">
                                <input type="hidden" name="institucion" value="<?= isset($_SESSION['institucion']) ? $_SESSION['institucion'] : ''; ?>">
                                <input type="hidden" name="codigo_institucion" value="<?= isset($_SESSION['codigo_institucion']) ? $_SESSION['codigo_institucion'] : ''; ?>">

                                <button type="submit" class="button-btn btn btn-secondary">
                                    Generar Excel
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>

            <br>
            <table class="table" id="Tabla-Consulta">
                <thead class="thead-dark">
                    <tr>
                        <th class="text-center">
                            <h6 class="fw-bold">Código</h6>
                        </th>
                        <th class="text-center">
                            <h6 class="fw-bold">Fecha de Marcación</h6>
                        </th>
                        <th class="text-center">
                            <h6 class="fw-bold">Hora de Marcación</h6>
                        </th>
                        <th class="text-center">
                            <h6 class="fw-bold">Foto</h6>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (mysqli_num_rows($consulta) > 0) {
                        while ($row = mysqli_fetch_array($consulta)) {
                            setlocale(LC_TIME, 'es_ES.UTF-8');
                            $fechaFormateada = strftime('%A %d-%m-%Y', strtotime($row['fecha']));
                            $hora = date('h:i A', strtotime($row['hora_inout']));
                            echo "<tr>
                            <td>{$row['codigo']}</td>
                            <td>{$fechaFormateada}</td>
                            <td>{$hora}</td>
                            <td><img class='img-thumbnail' width='100' src='upload/{$row['foto']}'></td>
                        </tr>";
                        }
                    } else {
                        if (isset($_POST['mes'])) {
                            $mesNoRegistros = strftime('%B %Y', strtotime($_POST['mes']));
                            echo "<tr><td colspan='4' class='text-center'><div class='alert alert-info'>No se han encontrado registros para $mesNoRegistros</div></td></tr>";
                        }
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>
        <br>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>