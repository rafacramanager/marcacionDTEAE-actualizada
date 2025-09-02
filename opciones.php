<?php
session_start(); // Inicia la sesión (asegúrate de tener esta línea al comienzo de tu archivo)
include_once("bd.php");

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['usuario'])) {
    // Si el usuario no ha iniciado sesión, redirigir a la página de inicio de sesión
    echo "<script> window.location.href = 'index.php'</script>";
    exit();
}

// Verifica si el usuario ha iniciado sesión y tiene el nivel de acceso adecuado
if (!isset($_SESSION['usuario']) || !isset($_SESSION['nivel_acceso']) || $_SESSION['nivel_acceso'] != 2) {
    // Si el usuario no ha iniciado sesión o no tiene el nivel de acceso adecuado, redirige al inicio
    echo "<script>window.location.href = 'panel.php';</script>";
    exit(); // Asegura que el script se detenga después de redirigir
}

?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Opciones</title>
    <link rel="icon" href="img/Mined.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <?php
    if (isset($_GET['registro']) && $_GET['registro'] == "true") {
        echo '<div class="alert alert-success d-flex align-items-center" role="alert">
        <svg xmlns="http://www.w3.org/2000/svg" class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="success">
            <use xlink:href="#exclamation-triangle-fill"/>
        </svg>
        <div align="center">
            Se ha registrado satisfactoriamente la marcación.
        </div>
    </div>';
    } elseif (isset($_GET['registro']) && $_GET['registro'] == "false") {
        echo '<div class="alert alert-danger d-flex align-items-center" role="alert">
    <svg xmlns="http://www.w3.org/2000/svg" class="bi flex-shrink-0 me-2" width="24" height="24" role="img" aria-label="danger:">
        <use xlink:href="#exclamation-triangle-fill"/>
    </svg>
    <div>
        Error inesperado! Intenta de nuevo o contacta a tu Administrador de sistema.
    </div>
</div>';
    } else {
        echo "";
    }
    ?>
    <br>
    <div class="container-fluid text-center">
        <div class="row">
            <div class="col">
                <a type="button" id="Botones" href="marcacion.php">Regresar</a>
            </div>
            <div class="col">
                <a type="button" id="Botones" href="cerrar_sesion.php">Salir</a>
            </div>
        </div>

        <br>

        <!-- <img class="img-fluid" src="img\Mined-Letras.png" alt="Logo" width="50%" id="Logo"> -->

        <div class="container" id="Aplicaciones">
            <div class="row gx-4 gx-md-5">
                <div class="col-md-4">
                    <div class="card">
                        <img src="img/marcar.jpg" class="card-img-top" alt="card2">
                        <div class="card-body">
                            <h5 class="fw-bold" id="Marcación-text">Marcación</h5>
                            <p class="fw-normal" id="Marcación-subtext">Realiza acá el proceso para marcar tu entrada y salida diariamente.</p>
                            <a href="marcacion.php" class="button-btn btn btn-primary">Marcación de entrada y salida</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <img src="img/reporte.jpeg" class="card-img-top" alt="card2">
                        <div class="card-body">
                            <h5 class="fw-bold" id="Reporte-text">Reporte</h5>
                            <p class="fw-normal" id="Reporte-subtext">Consulta tus marcaciones de entradas y salidas diarias.</p>
                            <a href="consultas.php" class="button-btn btn btn-primary">Reporte de entradas y salidas</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card">
                        <img src="img/actividades.jpeg" class="card-img-top" alt="card3">
                        <div class="card-body">
                            <h5 class="fw-bold" id="Reporte-text">Actividades diarias</h5>
                            <p class="fw-normal" id="Reporte-subtext">Registre acá las actividades diarias o consulta las registradas.</p>
                            <a href="actividades.php" class="button-btn btn btn-primary">Registro de Actividades Diarias</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <br><br>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
</body>

</html>