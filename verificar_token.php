<?php
include "bd.php";

if (isset($_POST['email']) && isset($_POST['token']) && isset($_POST['codigo'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $token = mysqli_real_escape_string($conn, $_POST['token']);
    $codigo = mysqli_real_escape_string($conn, $_POST['codigo']);

    $sql = "SELECT * FROM password WHERE email=? AND token=? AND codigo=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $email, $token, $codigo);
    $stmt->execute();
    $result = $stmt->get_result();

    $correcto = false;

    if ($result && $result->num_rows > 0) {
        $fila = $result->fetch_assoc();
        $fecha = $fila['fecha'];
        $fecha_actual = date("Y-m-d H:i:s");
        $diferencia = strtotime($fecha_actual) - strtotime($fecha);
        $minutos = $diferencia / 60;

        $correcto = true;
    } else {
        $correcto = false;
    }
}
?>


<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Cambiar contraseña</title>
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
                        <h2 class="fw-bold" id="Titulo">Cambio de contraseña</h2>
                        <br><br>
                        <div class="form-container">
                            <?php if ($correcto) { ?>

                                <form action="cambiar_password.php" method="POST">

                                    <div class="form-outline mb-4">
                                        <h4 class="fw-bold" id="Titulo">Ingrese su nueva contraseña</h4>
                                        <hr>

                                        <input type="password" name="p1" id="password1" class="form-control" required placeholder="Ingrese su nueva contraseña." />
                                        <br>
                                        <input type="password" name="p2" id="password2" class="form-control" required placeholder="Confirmar contraseña." />

                                        <input type="hidden" class="form-control" id="c" name="email" value="<?php echo $email ?>">




                                    </div>
                                    <br><br>

                                    <button type="submit" class="btn btn-primary btn-block mb-4" id="Registro">Cambiar contraseña</button>

                                </form>
                            <?php } else { ?>
                                <div class="alert alert-danger">Código incorrecto o vencido</div>
                            <?php } ?>

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