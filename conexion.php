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

// Datos reales de Alwaysdata
$servidor = "mysql-evainformatica.alwaysdata.net";
$usuario = "evainformatica";
$clave = "Oddy32157."; // <- pon aquí la contraseña que creaste al registrarte
$base_datos = "evainformatica_bd";

$conexion = new mysqli($servidor, $usuario, $clave, $base_datos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8");
?>