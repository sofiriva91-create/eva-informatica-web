<?php
include('conexion.php');
session_start();

if (!isset($_SESSION['cedula_recuperacion'])) {
    header("Location: login.php");
    exit();
}

$mensaje = "";
$cedula = $_SESSION['cedula_recuperacion'];

if (isset($_POST['actualizar'])) {
    $nueva_clave = $conexion->real_escape_string($_POST['nueva_clave']);
    $confirmar_clave = $conexion->real_escape_string($_POST['confirmar_clave']);

    if ($nueva_clave === $confirmar_clave) {
      
        $sql = "UPDATE usuarios SET clave = '$nueva_clave' WHERE cedula = '$cedula'";
        if ($conexion->query($sql) === TRUE) {
            unset($_SESSION['cedula_recuperacion']); 
            echo "<p style='color:green;'><strong>¡Contraseña actualizada con éxito! Redirigiendo al login...</strong></p>";
            echo "<meta http-equiv='refresh' content='3;url=login.php'>";
        } else {
            $mensaje = "Error al actualizar: " . $conexion->error;
        }
    } else {
        $mensaje = "Las contraseñas no coinciden.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cambiar Contraseña - Aula Virtual</title>
</head>
<body>
    <h2>Restablecer Contraseña</h2>
    <?php if ($mensaje != "") echo "<p style='color:red;'><strong>$mensaje</strong></p>"; ?>

    <form method="POST" action="">
        <label>Nueva Contraseña:</label><br>
        <input type="password" name="nueva_clave" required><br><br>

        <label>Confirmar Nueva Contraseña:</label><br>
        <input type="password" name="confirmar_clave" required><br><br>

        <button type="submit" name="actualizar">Actualizar Contraseña</button>
    </form>
</body>
</html>