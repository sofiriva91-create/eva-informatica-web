<?php
session_start();
include("conexion.php"); // Asegúrate de que conecta con tu base de datos
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = isset($_POST['cedula']) ? trim($_POST['cedula']) : '';
    $clave = isset($_POST['clave']) ? trim($_POST['clave']) : '';

    if (!empty($cedula) && !empty($clave)) {
        // Consultar el usuario en la base de datos por su cédula
        $consulta = $conexion->prepare("SELECT * FROM usuarios WHERE cedula = ?");
        $consulta->bind_param("s", $cedula);
        $consulta->execute();
        $resultado = $consulta->get_result();
        
        if ($resultado->num_rows === 1) {
            $datos_usuario = $resultado->fetch_assoc();
            
            // Verificar la contraseña cifrada
            if (password_verify($clave, $datos_usuario['clave'])) {
                // Guardar los datos esenciales en la sesión
                $_SESSION['cedula'] = $datos_usuario['cedula'];
                $_SESSION['nombre'] = $datos_usuario['nombre'];
                $_SESSION['rol'] = $datos_usuario['rol'];
                $_SESSION['seccion'] = $datos_usuario['seccion'];
                $_SESSION['materia'] = $datos_usuario['materia'];
                // ==========================================
                // REDIRECCIÓN INTELIGENTE SEGÚN EL ROL
                // ==========================================
                // Limpiamos el rol y lo pasamos a minúsculas para evitar errores
                $rol_usuario = strtolower(trim($datos_usuario['rol']));

                if ($rol_usuario === 'docente' || $rol_usuario === 'admin' || $rol_usuario === 'administrador') {
                    header("Location: panel_admin.php");
                    exit();
                } else {
                    header("Location: panel_estudiante.php");
                    exit();
                }

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
    <title>EVA Informática - UPTAAPC</title>
    <link rel="icon" type="image/png" href="logo-uptaapc.png">
    <link rel="stylesheet" href="style.css">
</head>
<body class="auth-body">

    <div class="auth-wrapper">
        <h2>¡Bienvenid@s al Entorno Virtual de Aprendizaje de la UPTAAPC!</h2>
        <img src="logo-uptaapc.png" alt="Logo UPTAAPC" class="auth-logo">
        <p>Ingresa tu cédula y contraseña para acceder</p>
        
        <?php if (!empty($error)): ?>
            <div style="color: #e53e3e; background: #fff5f5; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; border: 1px solid #feb2b2;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

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