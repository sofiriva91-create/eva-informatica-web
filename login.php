<?php
session_start();
include("conexion.php"); // Asegúrate de que este archivo conecta con tu base de datos

$error = "";

// Esto se ejecuta cuando presionas el botón de enviar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = trim($_POST['cedula']);
    $clave = trim($_POST['clave']);

    if (!empty($cedula) && !empty($clave)) {
        $consulta = $conexion->prepare("SELECT * FROM usuarios WHERE cedula = ?");
        $consulta->bind_param("s", $cedula);
        $consulta->execute();
        $resultado = $consulta->get_result();
        
        if ($resultado->num_rows === 1) {
            $datos_usuario = $resultado->fetch_assoc();
            if (password_verify($clave, $datos_usuario['clave'])) {
                $_SESSION['usuario'] = $datos_usuario['cedula'];
                // Redirección automática al panel del estudiante
                header("Location: panel_estudiante.php"); 
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "La cédula no está registrada.";
        }
    } else {
        $error = "Por favor completa todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - EVA Informática</title>
    <link rel="stylesheet" href="style.css?v=4.2">
</head>
<body class="auth-body">

    <div class="auth-wrapper">
        <h2>¡Bienvenid@ de nuevo!</h2>
        <p>Ingresa tu cédula y contraseña para acceder</p>
        
        <!-- Aquí mostramos el mensaje de error de PHP si las credenciales fallan -->
        <?php if (!empty($error)): ?>
            <div style="color: #e53e3e; background: #fff5f5; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- EL FORMULARIO HTML -->
        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Cédula:</label>
                <input type="text" name="cedula" placeholder="Ej: 28123456" required>
            </div>
            
            <div class="form-group">
                <label>Contraseña:</label>
                <input type="password" name="clave" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn-auth">Ingresar al Sistema</button>
        </form>

        <div class="auth-footer">
            <p><a href="recuperar.php">¿Olvidaste tu contraseña?</a></p>
            <p style="margin-top: 10px;">¿Aún no tienes cuenta? <a href="registros.php">Regístrate aquí</a></p>
        </div>
    </div>

</body>
</html>