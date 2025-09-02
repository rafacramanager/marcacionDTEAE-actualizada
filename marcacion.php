<?php
session_start();
include_once("bd.php");

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    echo "<script> window.location.href = 'index.php'</script>";
    exit();
}

// Verificar que el usuario tenga el nivel de acceso adecuado
if (!isset($_SESSION['usuario']) || !isset($_SESSION['nivel_acceso']) || $_SESSION['nivel_acceso'] != 2) {
    echo "<script>window.location.href = 'panel.php';</script>";
    exit();
}

$codigo_usuario = $_SESSION['usuario'];

// Consulta para obtener los mensajes activos de la base de datos
$sql = "SELECT * FROM mensajes WHERE activo = 1";
$resultado = mysqli_query($conn, $sql);
$mensajes = [];
while ($row = mysqli_fetch_assoc($resultado)) {
    $mensajes[] = $row['mensaje'];
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Marcación</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.25/webcam.min.js"></script>
    <link rel="icon" href="img/Mined.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <style type="text/css">
        #resultsNO {
            padding: 20px;
            border: 1px solid;
            background: #ccc;
        }

        /* Estilos opcionales para el modal */
        .modal-header {
            background-color: #0d6efd;
            color: #fff;
        }

        .modal-footer .btn-primary {
            background-color: #0d6efd;
        }

        .modal-error {
            color: red;
            display: none;
            margin-top: 5px;
        }
    </style>
</head>

<body>
    <div class="container-fluid text-center">
        <br><br>
        <!-- Mensaje por error de geolocalización -->
        <div class="alert alert-warning" role="alert" id="error-message" style="display: none;">
            <h4>Para continuar, habilita la geolocalización en tu dispositivo.</h4>
        </div>
        <!-- Mostrar mensajes activos -->
        <?php foreach ($mensajes as $mensaje) : ?>
            <div class="alert alert-secondary" role="alert">
                <marquee><?php echo $mensaje; ?></marquee>
            </div>
        <?php endforeach; ?>


        <h2 class="fw-bold" id="Titulo">
            Código: <span class='codigo'><?php echo $codigo_usuario; ?></span>
        </h2><br>
        <a href="opciones.php" class="btn btn-primary" id="Botones">Menu</a>
        <br>
        <br>
        <div class="container" id="Marcacion">
            <br>
            <h2 class="fw-bold" id="Titulo">Marcación Diaria</h2>
            <hr>
            <form method="POST" action="subir.php" id="subir">
                <div class="row gx-4 gx-md-5">
                    <div class="container" id="Fotos">
                        <div class="row align-items-center">
                            <div class="col-12 col-md-6">
                                <hr id="HR-COLOR">
                                <div>
                                    <div id="my_camera"></div>
                                </div>
                                <br />
                                <input type="button" class="btn btn-primary btn-block mb-4" id="tomarFoto" name="tomarFoto" value="Tomar fotografía" onClick="take_snapshot()">
                                <input type="hidden" name="image" class="image-tag">
                                <!-- Botón para abrir el modal de tipo de permiso -->
                                <button type="button" class="btn btn-primary btn-block mb-4" id="tipoPermiso" data-bs-toggle="modal" data-bs-target="#permisoModal">
                                    Otro tipo reporte
                                </button>
                                <div id="permisoSeleccionadoContainer" style="display: none; margin-top: 10px;">
                                    <strong id="infotipo">Permiso seleccionado:</strong> <span id="permisoSeleccionadoTexto"></span>
                                </div>
                            </div>
                            <hr id="HR-COLOR">
                            <div class="col-md-1"></div>
                            <div class="col-12 col-md-5" id="capturedImageContainer" style="display: none;">
                                <div id="results">
                                    <h2 class="fw-bold" id="Subtitulo">Fotografía a registrar:</h2>
                                </div>
                                <img id="capturedImage" src="" alt="Imagen capturada" class="mt-3">
                                <hr id="HR-COLOR">
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="fecha" id="fecha">
                <input type="hidden" name="hora" id="hora">
                <input type="hidden" name="latitud" id="latitud">
                <input type="hidden" name="longitud" id="longitud">
                <div class="row mt-3">
                    <div class="col-md-12" style="font-size:14px; color:white">
                        <div>
                            <!-- <br> -->
                            <hr>
                            <h2 class="fw-bold"> Tu hora de marcación es: <span id="horaMostrar"> </span> del día <span id="fechaMostrar"></span> </h2>
                            <br>
                            <br>
                        </div>
                        <div> <span id="coordenadas"> </span></div>
                        <button type="submit" class="btn btn-primary btn-block mb-4" id="Boton-Marcacion">
                            <h2 class="fw-bold" id="Titulo">Tomar Registro</h2>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal para selección de tipo de permiso -->
    <div class="modal fade" id="permisoModal" tabindex="-1" aria-labelledby="permisoModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="permisoModalLabel">Seleccionar tipo de permiso</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="modalTipoPermiso">Seleccione un tipo de permiso</label>
                        <select id="modalTipoPermiso" class="form-control">
                            <option value="">-- Seleccione --</option>
                            <?php
                            $query = "SELECT * FROM tipo_permisos";
                            $result = mysqli_query($conn, $query);
                            while ($row = mysqli_fetch_assoc($result)) {
                                echo "<option value='{$row['id']}'>{$row['nombre']}</option>";
                            }
                            ?>
                        </select>
                        <div class="modal-error" id="modalError">Debe seleccionar un tipo de permiso.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" id="guardarPermiso">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Configuraciones de la cámara -->

    <!-- Librerías  -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/webcamjs/1.0.25/webcam.min.js"></script>

    <script>
        let fotoTomada = false;
        let permisoSeleccionado = false;

        Webcam.set({
            width: 200,
            height: 200,
            image_format: 'jpeg',
            jpeg_quality: 90
        });
        Webcam.attach('#my_camera');

        function verificarCondicionesMarcacion() {
            const botonMarcacion = document.getElementById("Boton-Marcacion");
            if (fotoTomada || permisoSeleccionado) {
                botonMarcacion.disabled = false;
            } else {
                botonMarcacion.disabled = true;
            }
        }

        function take_snapshot() {
            Webcam.snap(function(data_uri) {
                document.querySelector(".image-tag").value = data_uri;
                document.getElementById("capturedImage").src = data_uri;
                document.getElementById("capturedImageContainer").style.display = "block";
                fotoTomada = true;

                // Si se toma una foto, deshabilitar la selección de permiso
                document.getElementById("modalTipoPermiso").disabled = true;
                document.getElementById("guardarPermiso").disabled = true;

                verificarCondicionesMarcacion();
            });
        }


        document.getElementById("guardarPermiso").addEventListener("click", function() {
            if (fotoTomada) {
                alert("No puede seleccionar un permiso si ya tomó una fotografía.");
                return;
            }
            const selectPermiso = document.getElementById("modalTipoPermiso");
            const tipoPermiso = selectPermiso.value;
            const nombrePermiso = selectPermiso.options[selectPermiso.selectedIndex].text; // Obtener el texto del permiso

            if (!tipoPermiso) {
                document.getElementById("modalError").style.display = "block";
            } else {
                document.getElementById("modalError").style.display = "none";

                // Agregar el permiso al formulario
                document.getElementById("subir").insertAdjacentHTML("beforeend",
                    `<input type='hidden' name='tipo_permiso_id' value='${tipoPermiso}'>`);

                // Mostrar visualmente el permiso seleccionado
                const permisoContainer = document.getElementById("permisoSeleccionadoContainer");
                const permisoTexto = document.getElementById("permisoSeleccionadoTexto");
                permisoTexto.innerText = nombrePermiso;
                permisoContainer.style.display = "block";

                permisoSeleccionado = true;
                verificarCondicionesMarcacion();
                document.querySelector("[data-bs-dismiss='modal']").click();
            }
        });


        document.getElementById("subir").addEventListener("submit", function(e) {
            const image = document.querySelector(".image-tag").value;
            const lat = document.getElementById("latitud").value;
            const lon = document.getElementById("longitud").value;

            if (!permisoSeleccionado && image.trim() === "") {
                e.preventDefault();
                alert("Debe tomar una fotografía o seleccionar un tipo de permiso para registrar su entrada/salida.");
                return false;
            }

            if (lat.trim() === "" || lon.trim() === "") {
                alert("No se pudo obtener la geolocalización. Se procederá a registrar sin ubicación.");
            }
        });

        document.addEventListener("DOMContentLoaded", verificarCondicionesMarcacion);
    </script>

    <script>
        // Obtener la fecha y hora del dispositivo
        document.addEventListener("DOMContentLoaded", function() {
            const now = new Date();
            const fechaFormateada = now.toISOString().split('T')[0];
            const horaFormateada = now.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
                hour12: true
            });

            document.getElementById("fecha").value = fechaFormateada;
            document.getElementById("hora").value = horaFormateada;
            document.getElementById("horaMostrar").innerHTML = horaFormateada;
            document.getElementById("fechaMostrar").innerHTML = now.toLocaleDateString('en-GB');
        });

        // Función para inicializar la geolocalización
        const initGeolocation = () => {
            if (!navigator.geolocation) {
                alert("Tu navegador no soporta el acceso a la ubicación. Intenta con otro.");
                document.getElementById("error-message").style.display = "block";
                return;
            }

            const onUbicacionConcedida = ubicacion => {
                const {
                    latitude,
                    longitude
                } = ubicacion.coords;
                document.getElementById("latitud").value = latitude;
                document.getElementById("longitud").value = longitude;
            }

            const onErrorDeUbicacion = err => {
                console.error("Error obteniendo ubicación: ", err);
                alert("Error al obtener la ubicación. Asegúrate de tener el GPS habilitado.");
                document.getElementById("error-message").style.display = "block";
            }

            const opcionesDeSolicitud = {
                enableHighAccuracy: true,
                maximumAge: 0,
                timeout: 5000
            };

            navigator.geolocation.getCurrentPosition(onUbicacionConcedida, onErrorDeUbicacion, opcionesDeSolicitud);
        };

        document.addEventListener("DOMContentLoaded", initGeolocation);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
</body>

</html>