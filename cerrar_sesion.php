<?php
// Iniciar la sesión si aún no está iniciada
session_start();

// Eliminar todas las variables de sesión
$_SESSION = array();

// Borrar la cookie de la sesión (si se utiliza)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destruir la sesión
session_destroy();

// Regenerar el ID de sesión para mayor seguridad
session_regenerate_id(true);

// Redirigir al usuario a la página de inicio o a donde desees
header("Location: index.php");
exit;
