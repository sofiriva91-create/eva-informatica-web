<?php
include('conexion.php');

$error = "";

if (isset($_POST['registrar'])) {
    $cedula  = $conexion->real_escape_string($_POST['cedula']);
    $nombre  = $conexion->real_escape_string($_POST['nombre']);
    $correo  = $conexion->real_escape_string($_POST['correo']);
    $clave   = password_hash($_POST['clave'], PASSWORD_DEFAULT);
    $rol     = strtolower($conexion->real_escape_string($_POST['rol']));
    $seccion = $conexion->real_escape_string($_POST['seccion']);

    // Validar si el correo o la cédula ya existen
    $validar = $conexion->query("SELECT * FROM usuarios WHERE correo = '$correo' OR cedula = '$cedula'");

    if ($validar && $validar->num_rows > 0) {
        $error = "⚠️ La cédula o el correo electrónico ya se encuentran registrados.";
    } else {
        $sql = "INSERT INTO usuarios (cedula, nombre, correo, clave, rol, seccion) 
                VALUES ('$cedula', '$nombre', '$correo', '$clave', '$rol', '$seccion')";

        if ($conexion->query($sql)) {
            header("Location: login.php?registro=exitoso");
            exit();
        } else {
            $error = "❌ Ocurrió un error al guardar los datos. Inténtalo de nuevo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario - EVA Informática</title>
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
                <h2>Crear una Cuenta 📝</h2>
                <p>Regístrate como docente o estudiante</p>
            </div>

            <?php if ($error != ""): ?>
                <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; text-align: center;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="registros.php">
                <div class="form-group">
                    <label>Cédula de Identidad:</label>
                    <input type="text" name="cedula" placeholder="Ej: 28123456" required>
                </div>

                <div class="form-group">
                    <label>Nombre Completo:</label>
                    <input type="text" name="nombre" placeholder="Ej: María Pérez" required>
                </div>

                <div class="form-group">
                    <label>Correo Electrónico:</label>
                    <input type="email" name="correo" placeholder="ejemplo@correo.com" required>
                </div>

                <div class="form-group">
                    <label>Contraseña:</label>
                    <input type="password" name="clave" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label>Tipo de Usuario (Rol):</label>
                    <select name="rol" required>
                        <option value="estudiante">Estudiante</option>
                        <option value="docente">Docente / Administrador</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Sección Asignada:</label>
                    <select name="seccion" required>
                        <option value="A">Sección A</option>
                        <option value="B">Sección B</option>
                    </select>
                </div>

                <button type="submit" name="registrar" class="btn-submit">Completar Registro</button>
            </form>

            <div class="auth-footer">
                ¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a>
            </div>
        </div>
    </div>

    <footer class="public-footer">
        &copy; <?php echo date('Y'); ?> EVA Informática — Plataforma Educativa
    </footer>

</body>
</html>