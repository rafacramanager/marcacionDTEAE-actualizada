<?php
include "bd.php";

$enviado = false; // Variable para controlar si se envió el correo con éxito

if (isset($_POST['email'])) {
    $email = $_POST['email'];

    // Verificar si el correo existe en la tabla de usuarios
    $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // El correo existe en la tabla de usuarios
        $bytes = random_bytes(5);
        $token = bin2hex($bytes);

        include "mail_reset.php";
        if ($enviado) {
            $conn->query("INSERT INTO password (email, token, codigo) VALUES ('$email', '$token', '$codigo')") or die($conn->error);
            $enviado = true; // Actualiza la variable enviada
            $mensaje = 'Verifica tu correo electrónico para restablecer tu contraseña.';
        } else {
            $mensaje = 'Error al enviar el correo electrónico. Por favor, intenta de nuevo más tarde.';
        }
    } else {
        // El correo no existe en la tabla de usuarios
        $mensaje = "El correo electrónico no está registrado en nuestro sistema. <a href='contraseña.php'>Regresar</a>";
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Solicitud</title>
    <link rel="icon" href="img/Mined.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
    <meta http-equiv="cache-control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="pragma" content="no-cache">
    <meta http-equiv="expires" content="0">
</head>

<body>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <img src="img\Mined-Letras.png" class="w-100 img-fluid" alt="Logo">

                <?php if (!empty($mensaje)) { ?>
                    <div class="alert <?php echo $enviado ? 'alert-success' : 'alert-danger'; ?>"><?php echo $mensaje; ?></div>
                <?php } ?>
            </div>
        </div>
    </div>

</body>


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>

</html>