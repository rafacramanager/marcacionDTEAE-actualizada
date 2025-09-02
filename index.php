<?php
session_start();
include_once("bd.php");

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $codigo_usuario = mysqli_real_escape_string($conn, $_POST['codigo']);
    $contrasena = $_POST['contra'];

    if ($codigo_usuario === '$usuario' && $contrasena === '$contraseña') {
        $_SESSION['nivel_acceso'] = 1;
        $_SESSION['usuario'] = $codigo_usuario;
        $_SESSION['nombre_usuario'] = '';
        header("Location: panel.php");
        exit();
    } else {
        $query = "SELECT nivel, codigo, usuario FROM usuarios WHERE codigo = ? AND contra = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $codigo_usuario, $contrasena);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);

        if (mysqli_stmt_num_rows($stmt) === 1) {
            mysqli_stmt_bind_result($stmt, $nivel, $codigo_usuario, $nombre_usuario);
            mysqli_stmt_fetch($stmt);

            $_SESSION['nivel_acceso'] = $nivel;
            $_SESSION['usuario'] = $codigo_usuario;
            $_SESSION['nombre_usuario'] = $nombre_usuario;

            switch ($nivel) {
                case 1:
                    // Usuario de nivel 1 (administrador), redirigir a panel.php
                    header("Location: panel.php");
                    exit();
                    break;
                case 2:
                    // Otro nivel, redirigir a marcacion.php
                    header("Location: marcacion.php");
                    exit();
                    break;
                default:
                    // Usuario o contraseña incorrectos, mostrar mensaje de error
                    $error_message = "Usuario o contraseña incorrectos. Intente de nuevo...";
            }
        } else {
            // Usuario o contraseña incorrectos, mostrar mensaje de error
            $error_message = "Usuario o contraseña incorrectos. Intente de nuevo...";
        }

        mysqli_stmt_close($stmt);
    }
}

// Cerrar la conexión a la base de datos
mysqli_close($conn);
?>

<?php if (isset($error_message)) : ?>
    <div class="alert alert-danger" role="alert">
        <?php echo $error_message; ?>
    </div>
<?php endif; ?>

<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Inicio</title>
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
                        <h2 class="fw-bold" id="Titulo">MARCACIÓN DIARIA - DTEAE</h2>
                        <h6 class="fw-normal" id="Subtitulo">Por favor ingrese sus credenciales</h6>
                        <br><br>
                        <div class="form-container"> <!-- Agregamos el contenedor del formulario -->
                            <form action="" method="POST">
                                <div class="form-outline mb-4">
                                    <h4 class="fw-bold" id="Titulo">Usuario:</h4>
                                    <hr>
                                    <input type="text" name="codigo" id="codigo" class="form-control" required placeholder="Ingrese su usuario" />
                                </div>
                                <div class="form-outline mb-4">
                                    <h4 class="fw-bold" id="Titulo">Contraseña:</h4>
                                    <hr>
                                    <div class="input-group">
                                        <input type="password" name="contra" id="contra" class="form-control" required placeholder="Ingrese su contraseña" />
                                        <button type="button" class="btn btn-outline-secondary" id="show-password-btn">
                                            Ver
                                        </button>
                                    </div>
                                </div>
                                <div class="row mb-4">
                                    <div class="col">
                                        <div class="text-center">
                                            <a href="contraseña.php" id="Links">¿Olvido su contraseña?</a>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary btn-block mb-4" id="Registro">Entrar</button>
                            </form>
                        </div>
                        <br>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script>
        const showPasswordBtn = document.getElementById("show-password-btn");
        const passwordInput = document.getElementById("contra");

        showPasswordBtn.addEventListener("click", function() {
            if (passwordInput.type === "password") {
                passwordInput.type = "text";
                showPasswordBtn.textContent = "Ocultar";
            } else {
                passwordInput.type = "password";
                showPasswordBtn.textContent = "Ver";
            }
        });
    </script>
</body>

</html>