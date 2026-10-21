<?php

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

// Datos de conexión remota (cámbialos por los que te dé tu proveedor de base de datos)
$servidor = "TU_HOST_REMOTO";     // Ejemplo: un enlace o IP que te proporcione tu BD externa
$usuario = "TU_USUARIO";         // Tu usuario de la base de datos
$clave = "TU_CONTRASENA";        // Tu contraseña de la base de datos
$base_datos = "eva_informatica"; // El nombre de tu base de datos

$conexion = new mysqli($servidor, $usuario, $clave, $base_datos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8");
?>