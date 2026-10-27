<?php
session_start();
echo "<h1>Estado actual de la sesión:</h1>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

echo "<hr>";
echo "<a href='../Web/login.php'>Ir al Login</a> | ";
echo "<a href='../Web/panel_admin.php'>Ir al Panel de Admin</a> | ";
echo "<a href='logout.php'>Cerrar Sesión / Borrar todo</a>"; 
?>