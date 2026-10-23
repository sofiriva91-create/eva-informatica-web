<?php
// 1. Iniciar la sesión existente para poder destruirla
session_start();

// 2. Limpiar todas las variables de sesión del usuario
$_SESSION = array();

// 3. Borrar la cookie de la sesión en el navegador (si existe)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Destruir la sesión en el servidor
session_destroy();

// 5. Redirigir automáticamente a la página de inicio (index.php)
header("Location: index.php");
exit();
?>