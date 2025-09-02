<?php
session_start();
include_once("bd.php");

// Verifica si el usuario ha iniciado sesión y tiene el nivel de acceso adecuado
if (!isset($_SESSION['usuario']) || !isset($_SESSION['nivel_acceso']) || $_SESSION['nivel_acceso'] != 1) {
    echo "<script>window.location.href = 'panel.php';</script>";
    exit();
}

// Función para obtener el nombre completo del usuario por su código
function getUsuarioDetalles($conn, $codigo_usuario)
{
    $sql = $conn->prepare("SELECT usuario FROM usuarios WHERE codigo = ?");
    $sql->bind_param('s', $codigo_usuario);
    $sql->execute();
    $result = $sql->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['usuario'];
    } else {
        return null;
    }
}

// Maneja el formulario de agregar actividad
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['editar_registro']) && !isset($_POST['eliminar_registro'])) {
    if (isset($_SESSION['usuario'])) {
        $codigo_usuario = $_SESSION['usuario'];
        $actividad = htmlspecialchars($_POST['actividad'], ENT_QUOTES, 'UTF-8');
        $descripcion = htmlspecialchars($_POST['descripcion'], ENT_QUOTES, 'UTF-8');
        $fecha = $_POST['fecha'];
        $nivel_acceso = $_SESSION['nivel_acceso'];

        // Obtener el nombre completo del usuario actual
        $nombre_usuario = getUsuarioDetalles($conn, $codigo_usuario);

        if (!$nombre_usuario) {
            $_SESSION['alerta'] = [
                'tipo' => 'danger',
                'mensaje' => 'Error al obtener el nombre del usuario'
            ];
            header("Location: asignar_actividades.php");
            exit();
        }

        // Crear la cadena con solo el nombre del usuario
        $asignado_por = $nombre_usuario;

        $estado = ($nivel_acceso == 2) ? 'No aplica' : 'en proceso';

        // Asignar actividades a todos los usuarios seleccionados
        if (isset($_POST['usuarios'])) {
            $usuarios = $_POST['usuarios'];
            foreach ($usuarios as $codigo_usuario) {
                // Obtener el nombre completo del usuario asignado
                $nombre_asignado = getUsuarioDetalles($conn, $codigo_usuario);
                if (!$nombre_asignado) {
                    $_SESSION['alerta'] = [
                        'tipo' => 'danger',
                        'mensaje' => 'Error al obtener el nombre del usuario asignado'
                    ];
                    continue;
                }

                $sql = $conn->prepare("INSERT INTO actividades (usuario, fecha, actividad, descripcion, estado, creador, asignado_por) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $sql->bind_param('sssssis', $codigo_usuario, $fecha, $actividad, $descripcion, $estado, $nivel_acceso, $asignado_por);

                if ($sql->execute()) {
                    $_SESSION['alerta'] = [
                        'tipo' => 'success',
                        'mensaje' => 'Nueva actividad asignada exitosamente'
                    ];
                } else {
                    $_SESSION['alerta'] = [
                        'tipo' => 'danger',
                        'mensaje' => 'Error al asignar la actividad: ' . $sql->error
                    ];
                }

                $sql->close();
            }
        }

        header("Location: asignar_actividades.php");
        exit();
    } else {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'Usuario no autenticado'
        ];
        header("Location: asignar_actividades.php");
        exit();
    }
}

// Maneja la edición de una actividad
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['editar_registro'])) {
    $id = $_POST['editar_registro'];
    $actividad = htmlspecialchars($_POST['actividad'], ENT_QUOTES, 'UTF-8');
    $descripcion = htmlspecialchars($_POST['descripcion'], ENT_QUOTES, 'UTF-8');
    $fecha = $_POST['fecha'];
    $estado = $_POST['estado'];
    $usuario = $_POST['usuario'];

    $sql = $conn->prepare("UPDATE actividades SET actividad = ?, descripcion = ?, fecha = ?, estado = ?, usuario = ? WHERE id = ?");
    $sql->bind_param('sssssi', $actividad, $descripcion, $fecha, $estado, $usuario, $id);

    if ($sql->execute() && $sql->affected_rows > 0) {
        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensaje' => 'Registro actualizado exitosamente'
        ];
    } elseif ($sql->affected_rows == 0) {
        $_SESSION['alerta'] = [
            'tipo' => 'info',
            'mensaje' => 'No se realizaron cambios'
        ];
    } else {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'Error al actualizar el registro: ' . $sql->error
        ];
    }

    $sql->close();
    header("Location: asignar_actividades.php");
    exit();
}

// Maneja la eliminación de una actividad
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['eliminar_registro'])) {
    $id = $_POST['eliminar_registro'];

    $sql = $conn->prepare("DELETE FROM actividades WHERE id = ?");
    $sql->bind_param('i', $id);

    if ($sql->execute()) {
        $_SESSION['alerta'] = [
            'tipo' => 'success',
            'mensaje' => 'Registro eliminado exitosamente'
        ];
    } else {
        $_SESSION['alerta'] = [
            'tipo' => 'danger',
            'mensaje' => 'Error al eliminar el registro'
        ];
    }

    $sql->close();
    header("Location: asignar_actividades.php");
    exit();
}

// Obtener todos los usuarios de nivel 2
$sql_usuarios = $conn->prepare("SELECT codigo, usuario FROM usuarios WHERE nivel = 2");
$sql_usuarios->execute();
$result_usuarios = $sql_usuarios->get_result();
$usuarios = [];
while ($row_usuario = $result_usuarios->fetch_assoc()) {
    $usuarios[] = $row_usuario;
}
$sql_usuarios->close();

// Obtener todas las actividades creadas por administradores (nivel 1)
$sql_actividades = $conn->prepare("
    SELECT 
        a.id, 
        a.fecha, 
        u.usuario AS usuario_nombre, 
        a.usuario AS usuario_codigo, 
        a.actividad, 
        a.descripcion, 
        a.estado, 
        a.creador,
        a.asignado_por, 
        a.created_at 
    FROM actividades a 
    INNER JOIN usuarios u ON a.usuario = u.codigo 
    ORDER BY a.fecha DESC, a.created_at DESC
");
$sql_actividades->execute();
$result_actividades = $sql_actividades->get_result();
$actividades = [];
while ($row_actividad = $result_actividades->fetch_assoc()) {
    $actividades[] = $row_actividad;
}
$sql_actividades->close();
?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Asignar Actividad</title>
    <link rel="icon" href="img/Mined.ico">
    <script src="https://kit.fontawesome.com/6305bb531f.js" crossorigin="anonymous"></script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="nav.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.css">

    <!-- DataTables JS -->
    <script type="text/javascript" charset="utf8" src="https://code.jquery.com/jquery-3.5.1.js"></script>
    <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.js"></script>
</head>

<body>
    <!-- menu de navegación -->
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
            <h1 class="fw-bold" id="Titulo">Panel De Actividades</h1>
        </h1>
        <div>
            <h2 class="fw-bold" id="Titulo">
                <?php
                $codigo_usuario = $_SESSION['usuario'];

                // Realiza una consulta para obtener el nombre de usuario
                $sql = "SELECT usuario FROM usuarios WHERE codigo = '$codigo_usuario'";
                $consulta = mysqli_query($conn, $sql);

                if ($row = mysqli_fetch_assoc($consulta)) {
                    $nombre_usuario = $row['usuario'];
                    echo "Usuario: <span class='usuario nombre-usuario'>$nombre_usuario</span>";
                } else {
                    echo "Usuario no encontrado";
                }
                ?>
            </h2>
        </div>

        <div class="container mt-6">
            <!-- Contenedor de alertas -->
            <div id="alertContainer">
                <?php if (isset($_SESSION['alerta'])) : ?>
                    <div class="alert alert-<?= $_SESSION['alerta']['tipo'] ?> alert-dismissible fade show shadow" role="alert">
                        <?= $_SESSION['alerta']['mensaje'] ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    <?php unset($_SESSION['alerta']); ?>
                <?php endif; ?>
            </div>

            <!-- Botón para abrir el modal de asignación -->
            <div class="text-end mb-3">
                <button class="btn btn-primary" type="button" data-bs-toggle="modal" data-bs-target="#modalAsignarActividad">
                    <i class="fa-solid fa-plus"></i> Asignar Nueva Actividad
                </button>
            </div>

            <!-- boton para ver todas las actividades semanales -->
            <div class="text-end mb-3">
                <a href="reporte_actividades.php" class="btn btn-primary">
                    <i class="fa-solid fa-calendar"></i> Ver Actividades Semanales
                </a>
            </div>

            <!-- Modal para asignar actividad -->
            <div class="modal fade" id="modalAsignarActividad" tabindex="-1" aria-labelledby="modalAsignarActividadLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content" style="background-color: #313944; color: white;">
                        <div class="modal-header" style="background-color: #212529; color: white;">
                            <h5 class="modal-title" id="modalAsignarActividadLabel">
                                <i class="fa-solid fa-clipboard-check me-2"></i>Formulario de Asignación
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form method="POST" action="" id="formAsignar" class="needs-validation" novalidate>
                                <!-- Filtro de búsqueda y selección de todos -->
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between">
                                        <input type="text" id="filtroUsuarios" class="form-control w-75" placeholder="Filtrar por nombre o código..." style="background-color: #212529; color: white; border: none;">
                                        <div class="form-check ms-3">
                                            <input class="form-check-input" type="checkbox" id="seleccionarTodos">
                                            <label class="form-check-label" for="seleccionarTodos" style="color: white;">Seleccionar todos</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 mb-3">
                                        <label for="usuarios" class="form-label">Seleccionar Usuario(s):</label>
                                        <select class="form-select" name="usuarios[]" id="usuarios" multiple required style="background-color: #212529; color: white; border: none;">
                                            <?php foreach ($usuarios as $usuario) : ?>
                                                <option value="<?= $usuario['codigo'] ?>" style="background-color: #212529; color: white;">
                                                    <?= $usuario['usuario'] ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Mantén presionada Ctrl (Cmd en Mac) para selección múltiple.</small>
                                        <div class="invalid-feedback">Selecciona al menos un usuario.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="actividad" class="form-label">Actividad:</label>
                                        <input type="text" class="form-control" id="actividad" name="actividad" placeholder="Ejemplo: Mantenimiento de sistemas" required style="background-color: #212529; color: white; border: none;">
                                        <div class="invalid-feedback">La actividad es obligatoria.</div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="fecha" class="form-label">Fecha:</label>
                                        <input type="date" class="form-control" id="fecha" name="fecha" required style="background-color: #212529; color: white; border: none;">
                                        <div class="invalid-feedback">Por favor, selecciona una fecha válida.</div>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="descripcion" class="form-label">Descripción:</label>
                                        <textarea class="form-control" id="descripcion" name="descripcion" rows="4" placeholder="Describe la actividad asignada" required style="background-color: #212529; color: white; border: none;"></textarea>
                                        <div class="invalid-feedback">Por favor, proporciona una descripción.</div>
                                    </div>
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="reset" class="btn btn-secondary">
                                        <i class="fa-solid fa-eraser me-2"></i> Limpiar
                                    </button>
                                    <div>
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            <i class="fa-solid fa-times me-2"></i> Cerrar
                                        </button>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fa-solid fa-check me-2"></i> Guardar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leyenda de estados: -->
            <div class="legend-container p-3 rounded">
                <h6 class="fw-bold text-uppercase text-center">Leyenda de estados:</h6>
                <div class="d-flex align-items-center my-1 text-start">
                    <span class="rounded-circle me-2 status-circle" style="background-color: green;"></span>
                    <span class="status-text">Completado: La actividad ha sido finalizada con éxito.</span>
                </div>
                <div class="d-flex align-items-center my-1 text-start">
                    <span class="rounded-circle me-2 status-circle" style="background-color: orange;"></span>
                    <span class="status-text">En proceso: La actividad está actualmente en progreso.</span>
                </div>
                <div class="d-flex align-items-center my-1 text-start">
                    <span class="rounded-circle me-2 status-circle" style="background-color: red;"></span>
                    <span class="status-text">No realizado: La actividad aún no ha sido realizada.</span>
                </div>
                <div class="d-flex align-items-center my-1 text-start">
                    <span class="rounded-circle me-2 status-circle" style="background-color: gray;"></span>
                    <span class="status-text">Sin estado: La actividad fue registrada por el técnico.</span>
                </div>
            </div>

            <br><br>

            <!-- Tabla de actividades -->
            <div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="filtroEstado" class="form-label text-white">Filtrar por estado:</label>
                        <select id="filtroEstado" class="form-select">
                            <option value="">Todos</option>
                            <option value="completado">Completado</option>
                            <option value="en proceso">En Proceso</option>
                            <option value="no realizado">No Realizado</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filtroAsignadoPor" class="form-label text-white">Filtrar por asignado por:</label>
                        <select id="filtroAsignadoPor" class="form-select">
                            <option value="">Todos</option>
                            <?php
                            $asignadoPorUsuarios = array_unique(array_column(array_filter($actividades, function ($actividad) {
                                return $actividad['creador'] == 1; // Filtrar solo actividades creadas por usuarios de nivel 1
                            }), 'asignado_por'));
                            foreach ($asignadoPorUsuarios as $asignadoPor) {
                                echo "<option value=\"" . htmlspecialchars($asignadoPor, ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($asignadoPor, ENT_QUOTES, 'UTF-8') . "</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filtroFecha" class="form-label text-white">Filtrar por semana:</label>
                        <input type="week" id="filtroFecha" class="form-control">
                    </div>
                </div>

                <br><br>

                <div class="table-responsive">
                    <table id="Tabla" class="table table-dark table-striped table-hover table-bordered align-middle">
                        <thead class="table-dark text-uppercase">
                            <tr>
                                <th style="text-align:left">Usuario (Código)</th>
                                <th style="text-align:left">Actividad</th>
                                <th>Fecha</th>
                                <th>Asignado por</th>
                                <th>Estado</th>
                                <th class="text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($actividades as $actividad) : ?>
                                <?php
                                $estado = strtolower($actividad['estado']);
                                $color = match ($estado) {
                                    'completado' => 'green',
                                    'en proceso' => 'orange',
                                    'no realizado' => 'red',
                                    default => 'gray',
                                };
                                ?>
                                <tr>
                                    <td class="text-white" style="text-align:left">
                                        <?= htmlspecialchars($actividad['usuario_nombre'], ENT_QUOTES, 'UTF-8') ?>
                                        <span class="text-muted">(<?= htmlspecialchars($actividad['usuario_codigo'], ENT_QUOTES, 'UTF-8') ?>)</span>
                                    </td>
                                    <td style="text-align:left">
                                        <span style="color: <?= $color ?>; font-weight: bold;">&#8226;</span>
                                        <span class="text-white">
                                            <?= htmlspecialchars($actividad['actividad'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </td>
                                    <td class="text-white">
                                        <?= htmlspecialchars(date('d-m-Y', strtotime($actividad['fecha'])), ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="asignado_por">
                                        <?= htmlspecialchars($actividad['asignado_por'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="estado">
                                        <?= htmlspecialchars($actividad['estado'], ENT_QUOTES, 'UTF-8') ?>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center align-items-center gap-2">
                                            <button class="btn btn-secondary btn-sm text-white ver-descripcion" data-bs-toggle="modal"
                                                data-bs-target="#descripcionModal" data-bs-toggle="tooltip" title="Ver descripción"
                                                data-id="<?= $actividad['id'] ?>"
                                                data-usuario="<?= htmlspecialchars($actividad['usuario_nombre'], ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($actividad['usuario_codigo'], ENT_QUOTES, 'UTF-8') ?>)"
                                                data-actividad="<?= htmlspecialchars($actividad['actividad'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-descripcion="<?= htmlspecialchars($actividad['descripcion'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-fecha="<?= htmlspecialchars($actividad['fecha'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-asignado="<?= htmlspecialchars($actividad['asignado_por'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-estado="<?= htmlspecialchars($actividad['estado'], ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                            <button class="btn btn-primary btn-sm editar-registro" data-bs-toggle="modal"
                                                data-bs-target="#editarModal" data-bs-toggle="tooltip" title="Editar actividad"
                                                data-id="<?= $actividad['id'] ?>"
                                                data-usuario="<?= $actividad['usuario_codigo'] ?>"
                                                data-actividad="<?= htmlspecialchars($actividad['actividad'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-descripcion="<?= htmlspecialchars($actividad['descripcion'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-fecha="<?= htmlspecialchars($actividad['fecha'], ENT_QUOTES, 'UTF-8') ?>"
                                                data-estado="<?= htmlspecialchars($actividad['estado'], ENT_QUOTES, 'UTF-8') ?>">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <form method="POST" action="" class="d-inline">
                                                <input type="hidden" name="eliminar_registro" value="<?= $actividad['id'] ?>">
                                                <button type="button" class="btn btn-danger btn-sm text-white btn-confirmar-eliminacion"
                                                    data-id="<?= $actividad['id'] ?>"
                                                    data-actividad="<?= htmlspecialchars($actividad['actividad'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-usuario="<?= htmlspecialchars($actividad['usuario_nombre'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-fecha="<?= htmlspecialchars($actividad['fecha'], ENT_QUOTES, 'UTF-8') ?>"
                                                    data-bs-toggle="modal" data-bs-toggle="tooltip" title="Eliminar actividad"
                                                    data-bs-target="#confirmarEliminacionModal">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal para ver descripción -->
            <div class="modal fade" id="descripcionModal" tabindex="-1" aria-labelledby="descripcionModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content" style="background-color: #313944; color: white; border-radius: 8px;">
                        <div class="modal-header" style="background-color: #212529; color: white; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                            <h5 class="modal-title fw-bold" id="descripcionModalLabel">Detalle de la Actividad</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" align="Left">
                            <div class="mb-3">
                                <strong class="d-block">Actividad:</strong>
                                <span id="descripcionModalActividad" class="d-block mt-1 text-white"></span>
                            </div>
                            <div class="mb-3">
                                <strong class="d-block">Técnico asignado:</strong>
                                <span id="descripcionModalTecnico" class="d-block mt-1 text-white"></span>
                            </div>
                            <div class="mb-3">
                                <strong class="d-block">Asignado por:</strong>
                                <span id="descripcionModalAsignado" class="d-block mt-1 text-white"></span>
                            </div>
                            <div class="mb-3">
                                <strong class="d-block">Fecha:</strong>
                                <span id="descripcionModalFecha" class="d-block mt-1 text-white"></span>
                            </div>
                            <div class="mb-3" id="descripcionModalEstadoContainer" style="display: none;">
                                <strong class="d-block">Estado:</strong>
                                <span id="descripcionModalEstado" class="d-block mt-1 text-white"></span>
                            </div>
                            <div class="mb-3">
                                <strong class="d-block">Descripción:</strong>
                                <p id="descripcionModalBody" class="mt-2 p-3" style="background-color: #212529; border-radius: 6px; color: white;"></p>
                            </div>
                        </div>
                        <div class="modal-footer" style="background-color: #212529; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                            <button type="button" class="btn btn-secondary" style="background-color: #495057; border: none;" data-bs-dismiss="modal">Cerrar</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal para editar actividad -->
            <div class="modal fade" id="editarModal" tabindex="-1" aria-labelledby="editarModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content" style="background-color: #313944; color: white;">
                        <div class="modal-header" style="background-color: #212529; color: white;">
                            <h5 class="modal-title" id="editarModalLabel">Editar Actividad</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <form id="editarForm" method="POST" action="">
                                <input type="hidden" name="editar_registro" id="editarId">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="editarUsuario" class="form-label">Usuario:</label>
                                        <select class="form-select" name="usuario" id="editarUsuario" required style="background-color: #212529; color: white; border: none;">
                                            <?php foreach ($usuarios as $usuario) : ?>
                                                <option value="<?= $usuario['codigo'] ?>" style="background-color: #212529; color: white;"><?= $usuario['usuario'] ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="editarFecha" class="form-label">Fecha:</label>
                                        <input type="date" class="form-control" id="editarFecha" name="fecha" required style="background-color: #212529; color: white; border: none;">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="editarActividad" class="form-label">Actividad:</label>
                                        <input type="text" class="form-control" id="editarActividad" name="actividad" required style="background-color: #212529; color: white; border: none;">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="editarEstado" class="form-label">Estado:</label>
                                        <select class="form-select" name="estado" id="editarEstado" required style="background-color: #212529; color: white; border: none;">
                                            <option value="completado" style="background-color: #212529; color: white;">Completado</option>
                                            <option value="en proceso" style="background-color: #212529; color: white;">En Proceso</option>
                                            <option value="no realizado" style="background-color: #212529; color: white;">No Realizado</option>
                                        </select>
                                    </div>
                                    <div class="col-md-12 mb-3">
                                        <label for="editarDescripcion" class="form-label">Descripción:</label>
                                        <textarea class="form-control" id="editarDescripcion" name="descripcion" rows="4" required style="background-color: #212529; color: white; border: none;"></textarea>
                                    </div>

                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal de Confirmación de eliminacion -->
            <div class="modal fade" id="confirmarEliminacionModal" tabindex="-1" aria-labelledby="confirmarEliminacionModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content" style="background-color: #313944; color: white; border-radius: 8px;">
                        <div class="modal-header" style="background-color: #212529; color: white; border-top-left-radius: 8px; border-top-right-radius: 8px;">
                            <h5 class="modal-title fw-bold" id="confirmarEliminacionModalLabel">Confirmar Eliminación</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" style="background-color: #212529;">
                            <p>¿Estás seguro de que deseas eliminar la siguiente actividad?</p>
                            <p><strong>Actividad:</strong> <span id="modalActividad"></span></p>
                            <p><strong>Usuario Asignado:</strong> <span id="modalUsuario"></span></p>
                            <p><strong>Fecha:</strong> <span id="modalFecha"></span></p>
                            <p class="text-danger fw-bold">Esta acción no se puede deshacer.</p>
                        </div>
                        <div class="modal-footer" style="background-color: #212529; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <form id="formEliminar" method="POST" action="" class="d-inline">
                                <input type="hidden" name="eliminar_registro" id="idEliminarRegistro">
                                <button type="submit" class="btn btn-danger">Eliminar</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <br><br>
    </div>

    <!-- JavaScript para DataTables -->
    <script>
        $(document).ready(function() {
            var table = $('#Tabla').DataTable({
                "language": {
                    "search": "Buscar:",
                    "searchPlaceholder": "Buscar actividad...",
                    "lengthMenu": "Mostrar _MENU_ actividades por página",
                    "info": "Mostrando _END_ de _TOTAL_ actividades",
                    "infoEmpty": "No hay actividades disponibles",
                    "infoFiltered": "(filtrado de _MAX_ actividades en total)",
                    "zeroRecords": "No se encontraron actividades que coincidan",
                    "paginate": {
                        "first": "Primero",
                        "last": "Último",
                        "next": "Siguiente",
                        "previous": "Anterior"
                    }
                },
                "ordering": true,
                "searching": true,
                "order": [
                    [2, "desc"]
                ], // Ordenar por fecha descendente
                "columnDefs": [{
                        "orderable": false,
                        "targets": -1
                    } // Deshabilitar ordenamiento para la última columna (Acción)
                ],
                "lengthMenu": [
                    [5, 10, 25, 50, -1], // Opciones de cantidad de registros por página
                    [5, 10, 25, 50, "Todos"] // Etiquetas para las opciones de cantidad de registros por página
                ],
                "pageLength": 5 // Cantidad de registros por página por defecto
            });

            // Filtrar por estado desde el select
            $('#filtroEstado').on('change', function() {
                var estado = $(this).val(); // Obtener el valor seleccionado
                table.column(4).search(estado).draw(); // Filtrar por la columna de estado (índice 4)
            });

            // Filtrar por asignado por desde el select
            $('#filtroAsignadoPor').on('change', function() {
                var asignadoPor = $(this).val(); // Obtener el valor seleccionado
                table.column(3).search(asignadoPor).draw(); // Filtrar por la columna de asignado por (índice 3)
            });

            // Filtrar por semana desde el input de semana
            $('#filtroFecha').on('change', function() {
                const selectedWeek = $(this).val(); // Obtener el valor seleccionado (formato YYYY-W##)
                if (selectedWeek) {
                    const [year, week] = selectedWeek.split('-W'); // Dividir el valor en año y número de semana
                    const firstDay = new Date(year, 0, (week - 1) * 7 + 1); // Primer día de la semana
                    const lastDay = new Date(year, 0, week * 7); // Último día de la semana

                    // Formatear las fechas al formato compatible con la tabla (d-m-Y)
                    const formatDate = (date) => {
                        const d = date.getDate().toString().padStart(2, '0');
                        const m = (date.getMonth() + 1).toString().padStart(2, '0');
                        const y = date.getFullYear();
                        return `${d}-${m}-${y}`;
                    };

                    const startDate = formatDate(firstDay);
                    const endDate = formatDate(lastDay);

                    // Crear un filtro personalizado para las fechas
                    $.fn.dataTable.ext.search.push((settings, data) => {
                        const tableDate = data[2]; // Fecha en la columna de la tabla
                        return tableDate >= startDate && tableDate <= endDate;
                    });

                    // Dibujar la tabla con el nuevo filtro
                    table.draw();

                    // Limpiar el filtro al cambiar a otro rango
                    $.fn.dataTable.ext.search.pop();
                } else {
                    // Si no hay semana seleccionada, mostrar todas las actividades
                    table.search('').columns().search('').draw();
                }
            });

        });
    </script>

    <script>
        // Filtrar usuarios dinámicamente
        document.getElementById('filtroUsuarios').addEventListener('input', function() {
            const filtro = this.value.toLowerCase();
            const opciones = document.getElementById('usuarios').options;
            for (let i = 0; i < opciones.length; i++) {
                const texto = opciones[i].textContent.toLowerCase();
                const valor = opciones[i].value.toLowerCase();
                // Mostrar la opción si coincide con el filtro
                opciones[i].style.display = texto.includes(filtro) || valor.includes(filtro) ? 'block' : 'none';
            }
        });

        // Seleccionar/deseleccionar todos los usuarios visibles
        document.getElementById('seleccionarTodos').addEventListener('change', function() {
            const opciones = document.getElementById('usuarios').options;
            const seleccionar = this.checked;
            for (let i = 0; i < opciones.length; i++) {
                if (opciones[i].style.display !== 'none') {
                    opciones[i].selected = seleccionar;
                }
            }
        });
        // Cargar datos en el modal "Ver detalles"
        document.querySelectorAll('.ver-descripcion').forEach(button => {
            button.addEventListener('click', () => {
                // Obtener datos del botón
                const actividad = button.dataset.actividad;
                const descripcion = button.dataset.descripcion;
                const tecnico = button.dataset.usuario;
                const asignadoPor = button.dataset.asignado;
                const fecha = button.dataset.fecha;
                const estado = button.dataset.estado;

                // Asignar valores a los campos del modal
                document.getElementById('descripcionModalActividad').innerText = actividad;
                document.getElementById('descripcionModalBody').innerText = descripcion;
                document.getElementById('descripcionModalTecnico').innerText = tecnico;
                document.getElementById('descripcionModalAsignado').innerText = asignadoPor;
                document.getElementById('descripcionModalFecha').innerText = fecha;

                // Mostrar estado solo si existe (nivel 1)
                if (estado) {
                    document.getElementById('descripcionModalEstado').innerText = estado;
                    document.getElementById('descripcionModalEstadoContainer').style.display = 'block';
                } else {
                    document.getElementById('descripcionModalEstadoContainer').style.display = 'none';
                }
            });
        });

        // Cargar datos en el modal "Editar actividad"
        document.querySelectorAll('.editar-registro').forEach(button => {
            button.addEventListener('click', () => {
                // Obtener datos del botón
                const id = button.dataset.id;
                const usuario = button.dataset.usuario;
                const actividad = button.dataset.actividad;
                const descripcion = button.dataset.descripcion;
                const fecha = button.dataset.fecha;
                const estado = button.dataset.estado;

                // Asignar valores a los campos del modal
                document.getElementById('editarId').value = id;
                document.getElementById('editarUsuario').value = usuario;
                document.getElementById('editarActividad').value = actividad;
                document.getElementById('editarDescripcion').value = descripcion;
                document.getElementById('editarFecha').value = fecha;
                document.getElementById('editarEstado').value = estado;
            });
        });

        // JavaScript para habilitar las validaciones de Bootstrap
        (function() {
            'use strict';
            const form = document.getElementById('formAsignar');
            form.addEventListener('submit', function(event) {
                // Evita el envío del formulario si no es válido
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            });

            // Validación personalizada para el campo múltiple de usuarios
            const usuarios = document.getElementById('usuarios');
            usuarios.addEventListener('change', function() {
                if (usuarios.selectedOptions.length > 0) {
                    usuarios.setCustomValidity('');
                } else {
                    usuarios.setCustomValidity('Selecciona al menos un usuario.');
                }
            });
        })();

        // Confirmar eliminación de un registro
        document.querySelectorAll('.btn-confirmar-eliminacion').forEach(button => {
            button.addEventListener('click', () => {
                const id = button.dataset.id;
                const actividad = button.dataset.actividad;
                const usuario = button.dataset.usuario;
                const fecha = button.dataset.fecha;

                // Establecer los valores en el modal
                document.getElementById('idEliminarRegistro').value = id;
                document.getElementById('modalActividad').innerText = actividad;
                document.getElementById('modalUsuario').innerText = usuario;
                document.getElementById('modalFecha').innerText = fecha;
            });
        });

        // Ocultar la alerta después de 3 segundos
        setTimeout(function() {
            const alert = document.querySelector('#alertContainer .alert');
            if (alert) {
                alert.classList.remove('show');
                setTimeout(() => alert.remove(), 500); // Remover del DOM después de la animación
            }
        }, 3000);
    </script>

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>