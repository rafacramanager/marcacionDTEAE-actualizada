<?php
// Varios destinatarios
$para  = $email;

// título
$título = 'Restablecimiento de contraseña DTEAE';
$codigo = rand(1000, 9999);

// mensaje
$mensaje = '
<html>
<head>
    <title>Restablecimiento de contraseña DTEAE</title>
</head>
<body>

<h1>Código de verificación</h1>

<div style="text-align:center; background-color:#444654; border-radius: 30px; color: #fff; padding: 50px;">
    <p>Ingresar el código de verificación en el siguiente link </p>
    <p>Código:</p>
    <h2>' . $codigo . '</h2>
    <h3><a href="https://saucessv.com/Proyecto-Mined/reset.php?email=' . $email . '&token=' . $token . '"> Restablecimiento de contraseña </a></h3>
    <p><small>Si usted no solicitó este correo, por favor ignorarlo.</small></p>
</div>   

</body>
</html>
';

// Para enviar un correo HTML, debe establecerse la cabecera Content-type
$cabeceras  = 'MIME-Version: 1.0' . "\r\n";
$cabeceras .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
$cabeceras .= 'From: Administración <Administracion@gmail.com>' . "\r\n";

// Enviarlo
$enviado = false;
if (mail($para, $título, $mensaje, $cabeceras)) {
    $enviado = true;
}
