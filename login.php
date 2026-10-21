<?php
include('conexion.php');

$error = "";

if (isset($_POST['ingresar'])) {
    $correo = $conexion->real_escape_string($_POST['correo']);
    $clave  = $_POST['clave'];

    $sql = "SELECT * FROM usuarios WHERE correo = '$correo' LIMIT 1";
    $res = $conexion->query($sql);

    if ($res && $res->num_rows > 0) {
        $usuario = $res->fetch_assoc();
        if (password_verify($clave, $usuario['clave']) || $clave === $usuario['clave']) {
            $_SESSION['id']      = $usuario['id'];
            $_SESSION['nombre']  = $usuario['nombre'];
            $_SESSION['cedula']  = $usuario['cedula'];
            $_SESSION['rol']     = strtolower(trim($usuario['rol']));
            $_SESSION['seccion'] = $usuario['seccion'];

            if ($_SESSION['rol'] === 'docente') {
                header("Location: panel_admin.php");
                exit();
            } else {
                header("Location: panel_estudiante.php");
                exit();
            }
        } else {
            $error = "❌ Contraseña incorrecta. Inténtalo de nuevo.";
        }
    } else {
        $error = "⚠️ El correo electrónico no se encuentra registrado.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - EVA Informática</title>
    <link rel="stylesheet" href="style.css?v=1.0">
</head>
<body class="public-body">

    <nav class="public-nav">
        <a href="index.php" class="public-logo" style="text-decoration:none;">
            🖥️ EVA Informática
        </a>
        <div class="nav-links">
            <a href="registros.php" class="btn-nav btn-nav-solid">Registrarse</a>
        </div>
    </nav>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h2>¡Bienvenid@ de nuevo! 👋</h2>
                <p>Ingresa tus datos para acceder al aula virtual</p>
            </div>

            <?php if (isset($_GET['registro']) && $_GET['registro'] === 'exitoso'): ?>
                <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; text-align: center;">
                    ¡Registro completado con éxito! Ya puedes iniciar sesión.
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['reset']) && $_GET['reset'] === 'exitoso'): ?>
                <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; text-align: center;">
                    ¡Contraseña actualizada con éxito! Inicia sesión con tu nueva clave.
                </div>
            <?php endif; ?>

            <?php if ($error != ""): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; text-align: center;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php">
                <div class="form-group">
                    <label>Correo Electrónico:</label>
                    <input type="email" name="correo" placeholder="ejemplo@correo.com" required>
                </div>

                <div class="form-group">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <label style="margin-bottom: 0;">Contraseña:</label>
                        <a href="recuperar.php" style="font-size: 0.82rem; color: #007bff; text-decoration: none;">¿Olvidaste tu contraseña?</a>
                    </div>
                    <input type="password" name="clave" placeholder="••••••••" required style="margin-top: 8px;">
                </div>

                <button type="submit" name="ingresar" class="btn-submit">Ingresar al Sistema</button>
            </form>

            <div class="auth-footer">
                ¿Aún no tienes cuenta? <a href="registros.php">Regístrate aquí</a>
            </div>
        </div>
    </div>

    <footer class="public-footer">
        &copy; <?php echo date('Y'); ?> EVA Informática — Plataforma Educativa
    </footer>

</body>
</html>