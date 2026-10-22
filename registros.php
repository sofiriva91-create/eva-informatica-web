<?php
include("conexion.php");
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
    $cedula = trim($_POST['cedula']);
    $nombre = trim($_POST['nombre']);
    $correo = trim($_POST['correo']);
    $clave = password_hash(trim($_POST['clave']), PASSWORD_BCRYPT);
    $rol = trim($_POST['rol']);
    $seccion = trim($_POST['seccion']);

    // Consulta para guardar en la base de datos
    $sql = "INSERT INTO usuarios (cedula, nombre, correo, clave, rol, sección) VALUES ('$cedula', '$nombre', '$correo', '$clave', '$rol', '$seccion')";
    
    if ($conexion->query($sql) === TRUE) {
        // Redirección automática al login tras registrarse exitosamente
        header("Location: login.php");
        exit();
    } else {
        $error = "Ocurrió un error al registrar los datos.";
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - EVA Informática</title>
    <link rel="stylesheet" href="style.css?v=4.4">
</head>
<body class="auth-body">

    <div class="auth-wrapper" style="max-width: 480px;">
        <h2>Registro de Usuario</h2>
        <p>Crea tu cuenta para acceder al sistema</p>
        
        <?php if (!empty($error)): ?>
            <div style="color: #e53e3e; background: #fff5f5; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="registros.php" method="POST">
            <div class="form-group">
                <label>Cédula:</label>
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
                <label>Tipo de Usuario:</label>
                <select name="rol" required>
                    <option value="" disabled selected>Seleccione un rol</option>
                    <option value="estudiante">Estudiante</option>
                    <option value="docente">Docente</option>
                </select>
            </div>
            <div class="form-group">
                <label>Sección:</label>
                <select name="seccion" required>
                    <option value="" disabled selected>Seleccione una sección</option>
                    <option value="Sección A">Sección A</option>
                    <option value="Sección B">Sección B</option>
                </select>
            </div>
            
            <button type="submit" class="btn-auth">Completar Registro</button>
        </form>

        <div class="auth-footer">
            <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
        </div>
    </div>

</body>
</html>