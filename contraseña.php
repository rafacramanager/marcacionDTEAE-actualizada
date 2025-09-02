<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Restablecimiento de contraseña</title>
    <link rel="icon" href="img/Mined.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="row g-0 bg-body-secondary position-relative" id="Login">
                    <div class="col-md-6 mb-md-0 p-md-4">
                        <img src="img\Mined-Letras.png" class="w-100 img-fluid" alt="Logo">
                    </div>
                    <div class="col-md-6 p-4 ps-md-0">
                        <h2 class="fw-bold" id="Titulo">Restablecimiento de contraseña</h2>
                        <br><br>
                        <div class="form-container">

                            <form action="restablecer.php" method="POST">

                                <div class="form-outline mb-4">
                                    <h4 class="fw-bold" id="Titulo">Ingresar su correo institucional</h4>
                                    <p class="fw-bold" id="Subtitulo">Se le enviará a su correo las instrucciones para restablecer su contraseña</p>
                                    <hr>
                                    <input type="email" name="email" id="email" class="form-control" required placeholder="Ingrese su correo institucional" />
                                </div>
                                <br><br>

                                <button type="submit" class="btn btn-primary btn-block mb-4" id="Registro">Enviar solicitud</button>

                            </form>

                            <div class="text-center">
                                <a href="index.php" id="Links">Regresar al inicio</a>
                            </div>

                        </div>
                        <br>
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