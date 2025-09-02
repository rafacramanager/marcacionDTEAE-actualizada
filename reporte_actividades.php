<?php
include_once("bd.php");
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    echo "<script>window.location.href = 'panel.php';</script>";
    exit();
}

// Obtener la fecha seleccionada
$fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';

// Inicializar variables para las actividades
$actividades = [];
$showTable = !empty($fecha); // Mostrar la tabla solo si se ha seleccionado una fecha

if ($showTable) {
    // Calcular las fechas de la semana (lunes a viernes)
    $timestamp = strtotime($fecha);
    $lunes = date("Y-m-d", strtotime('monday this week', $timestamp));
    $viernes = date("Y-m-d", strtotime('friday this week', $timestamp));

    // Convertir fechas a formato DD/MM/YYYY para mostrar
    $fecha_inicio = date("d/m/Y", strtotime($lunes));
    $fecha_fin = date("d/m/Y", strtotime($viernes));

    // Consultar las actividades de la semana para todos los usuarios
    $sql = "SELECT a.usuario, a.actividad, a.descripcion, a.fecha, u.usuario AS nombre_usuario 
            FROM actividades AS a 
            INNER JOIN usuarios AS u ON a.usuario = u.codigo
            WHERE a.fecha BETWEEN ? AND ?
            ORDER BY a.fecha ASC, a.created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $lunes, $viernes);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $actividades[$row['fecha']][] = $row;
        }
    }
    $stmt->close();

    // Obtener todos los usuarios
    $sqlUsuarios = "SELECT codigo, usuario FROM usuarios";
    $resultUsuarios = $conn->query($sqlUsuarios);
    $usuarios = [];
    if ($resultUsuarios->num_rows > 0) {
        while ($row = $resultUsuarios->fetch_assoc()) {
            $usuarios[$row['codigo']] = $row['usuario'];
        }
    }

    // Obtener fechas de la semana en un array
    $dias_semana = [];
    for ($i = 0; $i < 5; $i++) {
        $dias_semana[] = date("Y-m-d", strtotime("+$i days", strtotime($lunes)));
    }

    // Días de la semana en español con fechas
    $dias_semana_completos = [];
    foreach ($dias_semana as $dia) {
        $nombre_dia = obtenerNombreDia($dia);
        $fecha_formato = date("d/m/Y", strtotime($dia));
        $dias_semana_completos[] = "$nombre_dia $fecha_formato";
    }

    // Calcular el nombre del archivo en formato deseado
    $nombre_archivo = "Semana del " . date("j", strtotime($lunes)) . " al " . date("j", strtotime($viernes)) . " de " . obtenerNombreMes(date("m", strtotime($viernes))) . " " . date("Y", strtotime($viernes));
}

function obtenerNombreDia($fecha)
{
    $dias = [
        'Monday' => 'Lunes',
        'Tuesday' => 'Martes',
        'Wednesday' => 'Miércoles',
        'Thursday' => 'Jueves',
        'Friday' => 'Viernes',
        'Saturday' => 'Sábado',
        'Sunday' => 'Domingo'
    ];
    $dia_ing = date("l", strtotime($fecha));
    return $dias[$dia_ing];
}

function obtenerNombreMes($mes)
{
    $meses = [
        '01' => 'Ene',
        '02' => 'Feb',
        '03' => 'Mar',
        '04' => 'Abr',
        '05' => 'May',
        '06' => 'Jun',
        '07' => 'Jul',
        '08' => 'Ago',
        '09' => 'Sep',
        '10' => 'Oct',
        '11' => 'Nov',
        '12' => 'Dic'
    ];
    return $meses[$mes];
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Actividades</title>
    <link rel="icon" href="img/Mined.ico">
    <script src="https://kit.fontawesome.com/6305bb531f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="nav.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script src="Utils/HTML-PDF.js"></script>
    <style>
        .table th,
        .table td {
            border: 1px solid #dee2e6;
        }
    </style>
</head>

<body>
    <!-- menu de navegación -->
    <nav class="navbar navbar-dark bg-dark fixed-top navbar-custom">
        <div class="container-fluid d-flex justify-content-between">
            <a href="asignar_actividades.php" class="button-back btn btn-secondary">Regresar a Actividades</a>

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
                    <a class="nav-link link-style" href="mensajes.php">Agregar Mensajes</a>
                </div>
                <div class="offcanvas-footer">
                    <a class="nav-link link-style-close" href="cerrar_sesion.php"><i class="fa-solid fa-door-open"></i> Cerrar sesión</a>
                </div>
            </div>
        </div>
    </nav>

    <br><br><br><br>
    <div class="container-fluid text-center">

        <h1 id="Titulo">
            <h1 class="fw-bold" id="Titulo">Reporte Semanal de Actividades</h1>
        </h1>

        <br>

        <form action="" method="POST">
            <div align="center">
                <div class="col-md-3">
                    <label for="fecha" class="form-label" style="color: white;">Seleccione la fecha (semana) a reportar:</label>
                    <input class="form-control" type="date" id="fecha" name="fecha" required>
                </div>
                <br>
                <div class="d-flex justify-content-center gap-3">
                    <button type="submit" class="button-btn btn btn-primary">Generar Reporte</button>
                </div>
            </div>
        </form>

        <br>

        <div class="acciones" align="center">
            <?php if ($showTable) : ?>
                <!-- Botón para generar el PDF -->
                <form method="POST" action="" class="mt-3" id="pdfForm">
                    <input type="hidden" name="fecha" value="<?php echo htmlspecialchars($fecha); ?>">
                    <input type="hidden" name="table_html" id="tableHtml" value="">
                    <button type="submit" class="button-btn btn btn-secondary">
                        Descargar Reporte
                    </button>
                </form>
            <?php endif; ?>
        </div>

    </div>

    <br><br>

    <div id="impreso">
        <?php if ($showTable) : ?>
            <input type="hidden" id="pdfFilename" value="<?php echo htmlspecialchars($nombre_archivo); ?>">

            <div class="report-header">
                <img src="img/Mined-Letras.png" alt="Ministerio de Educación">
                <div class="report-header-text">
                    <h5>MINISTERIO DE EDUCACIÓN, CIENCIA Y TECNOLOGÍA (MINEDUCYT)</h5>
                    <h6>INFORME DE ACTIVIDADES SEMANALES</h6>
                    <h6>SEMANA: <?php echo "$fecha_inicio - $fecha_fin"; ?></h6>
                </div>
            </div>

            <table class="table calendar-table mt-3" id="activityTable">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <?php foreach ($dias_semana_completos as $dia_completo) : ?>
                            <th><?php echo htmlspecialchars($dia_completo); ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $codigo => $usuario) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($usuario); ?></td>
                            <?php foreach ($dias_semana as $dia) : ?>
                                <td>
                                    <?php if (isset($actividades[$dia])) : ?>
                                        <?php $hasActivity = false; ?>
                                        <?php foreach ($actividades[$dia] as $act) : ?>
                                            <?php if ($act['usuario'] == $codigo) : ?>
                                                <strong><?php echo htmlspecialchars($act['actividad']); ?>:</strong>
                                                <?php echo htmlspecialchars($act['descripcion']); ?><br>
                                                <?php $hasActivity = true; ?>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                        <?php if (!$hasActivity) : ?>
                                            No hay actividades
                                        <?php endif; ?>
                                    <?php else : ?>
                                        No hay actividades
                                    <?php endif; ?>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <br><br>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>