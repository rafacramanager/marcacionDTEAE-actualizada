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
$codigo_usuario = $_SESSION['usuario'];

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

    // Consultar las actividades de la semana
    $sql = "SELECT a.usuario, a.actividad, a.descripcion, a.fecha, u.usuario AS nombre_usuario 
            FROM actividades AS a 
            INNER JOIN usuarios AS u ON a.usuario = u.codigo
            WHERE a.fecha BETWEEN ? AND ? AND a.usuario = ?
            ORDER BY a.created_at ASC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sss', $lunes, $viernes, $codigo_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $actividades[$row['fecha']][] = $row;
        }
    }
    $stmt->close();

    // Días de la semana en español con fechas
    $dias_semana = ["Lunes", "Martes", "Miércoles", "Jueves", "Viernes"];
    $fechas_semana = [
        date("Y-m-d", strtotime('monday this week', $timestamp)),
        date("Y-m-d", strtotime('tuesday this week', $timestamp)),
        date("Y-m-d", strtotime('wednesday this week', $timestamp)),
        date("Y-m-d", strtotime('thursday this week', $timestamp)),
        date("Y-m-d", strtotime('friday this week', $timestamp))
    ];

    // Crear una función para obtener el nombre completo del día en español
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

    // Crear una función para obtener el nombre del mes en español
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

    // Calcular el nombre del día y la fecha en el formato deseado
    $dias_semana_completos = [];
    foreach ($fechas_semana as $fecha) {
        $nombre_dia = obtenerNombreDia($fecha);
        $mes = date("m", strtotime($fecha));
        $fecha_formato = date("d", strtotime($fecha)) . ' ' . obtenerNombreMes($mes);
        $dias_semana_completos[] = "$nombre_dia $fecha_formato";
    }

    // Calcular el nombre del archivo en formato deseado
    $nombre_archivo = "Semana del " . date("j", strtotime($lunes)) . " al " . date("j", strtotime($viernes)) . " de " . obtenerNombreMes(date("m", strtotime($viernes))) . " " . date("Y", strtotime($viernes));
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Actividades</title>
    <link rel="icon" href="img/Mined.ico">
    <script src="https://kit.fontawesome.com/6305bb531f.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.2/html2pdf.bundle.min.js"></script>
    <script src="Utils/HTML-PDF.js"></script>
</head>

<body>
    <br>
    <div class="container-fluid text-center">

        <div class="row">
            <div class="col">
                <a type="button" id="Botones" href="actividades.php">Regresar</a>
            </div>
            <div class="col">
                <a type="button" id="Botones" href="cerrar_sesion.php">Salir</a>
            </div>
        </div>
        <img class="img-fluid" src="img\Mined-Letras.png" alt="Logo" id="Logo">

        <br>
        <h1 id="Titulo">
            <h1 class="fw-bold" id="Titulo">Generación de Reporte de Actividades</h1>
        </h1>
        <h2 class="fw-bold" id="Titulo">
            <?php
            $codigo_usuario = $_SESSION['usuario'];

            $sql = "SELECT usuario, coordinacion FROM usuarios WHERE codigo = '$codigo_usuario'";
            $consulta = mysqli_query($conn, $sql);

            if ($row = mysqli_fetch_assoc($consulta)) {
                $nombre_usuario = $row['usuario'];
                $coordinacion = $row['coordinacion'];
                echo "Usuario: <span class='usuario nombre-usuario'>$nombre_usuario</span>";
            } else {
                echo "Usuario no encontrado";
            }
            ?>
        </h2>

        <br>

        <form action="consultar_actividades.php" method="POST">
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
                    <h6>NOMBRE DE LA UNIDAD: DEPARTAMENTO DE TECNOLOGÍAS EMERGENTES APLICADAS A LA EDUCACIÓN / COORDINACIÓN DE <?php echo strtoupper($coordinacion); ?></h6>
                    <h6>USUARIO: <?php echo $nombre_usuario; ?></h6>
                    <h6>SEMANA: <?php echo "$fecha_inicio - $fecha_fin"; ?></h6>
                </div>
            </div>

            <table class="table calendar-table mt-3" id="activityTable">
                <thead>
                    <tr>
                        <?php foreach ($dias_semana_completos as $dia_completo) : ?>
                            <th class="header"><?php echo $dia_completo; ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php foreach ($fechas_semana as $fecha) : ?>
                            <td class="day-cell">
                                <?php if (isset($actividades[$fecha])) : ?>
                                    <?php foreach ($actividades[$fecha] as $actividad) : ?>
                                        <strong><?php echo htmlspecialchars($actividad['actividad']); ?>:</strong>
                                        <?php echo htmlspecialchars($actividad['descripcion']); ?><br>
                                    <?php endforeach; ?>
                                <?php else : ?>
                                    No hay actividades
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
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