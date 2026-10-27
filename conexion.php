<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servidor = "mysql-evainformatica.alwaysdata.net";
$usuario = "evainformatica";
$clave = "Oddy32157.";
$base_datos = "evainformatica_bd";

$conexion = new mysqli($servidor, $usuario, $clave, $base_datos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8");
?>