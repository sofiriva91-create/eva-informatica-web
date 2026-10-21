<?php
include('conexion.php');

$error = "";

if (isset($_POST['recuperar'])) {
    $cedula      = $conexion->real_escape_string($_POST['cedula']);
    $correo      = $conexion->real_escape_string($_POST['correo']);
    $nueva_clave = password_hash($_POST['nueva_clave'], PASSWORD_DEFAULT);

    $sql = "SELECT id FROM usuarios WHERE cedula = '$cedula' AND correo = '$correo' LIMIT 1";
    $res = $conexion->query($sql);

    if ($res && $res->num_rows > 0) {
        $user = $res->fetch_assoc();
        $user_id = $user['id'];
        $conexion->query("UPDATE usuarios SET clave = '$nueva_clave' WHERE id = $user_id");
        header("Location: login.php?reset=exitoso");
        exit();
    } else {
        $error = "⚠️ La cédula y el correo ingresados no coinciden con ninguna cuenta.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - EVA Informática</title>
    <link rel="stylesheet" href="style.css?v=1.0">
</head>
<body class="public-body">

    <nav class="public-nav">
        <a href="index.php" class="public-logo" style="text-decoration:none;">
            🖥️ EVA Informática
        </a>
        <div class="nav-links">
            <a href="login.php" class="btn-nav btn-nav-outline">Iniciar Sesión</a>
        </div>
    </nav>

    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-header">
                <h2>Recuperar Contraseña 🔑</h2>
                <p>Verifica tu identidad para asignar una nueva clave</p>
            </div>

            <?php if ($error != ""): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; text-align: center;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="recuperar.php">
                <div class="form-group">
                    <label>Cédula de Identidad:</label>
                    <input type="text" name="cedula" placeholder="Ej: 28123456" required>
                </div>

                <div class="form-group">
                    <label>Correo Electrónico:</label>
                    <input type="email" name="correo" placeholder="ejemplo@correo.com" required>
                </div>

                <div class="form-group">
                    <label>Nueva Contraseña:</label>
                    <input type="password" name="nueva_clave" placeholder="••••••••" required>
                </div>

                <button type="submit" name="recuperar" class="btn-submit">Actualizar Contraseña</button>
            </form>

            <div class="auth-footer">
                <a href="login.php">Volver al inicio de sesión</a>
            </div>
        </div>
    </div>

    <footer class="public-footer">
        &copy; <?php echo date('Y'); ?> EVA Informática — Plataforma Educativa
    </footer>

</body>
</html>