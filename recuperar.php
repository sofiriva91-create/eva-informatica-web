<?php
include'../backend/conexion.php';
$error = "";
$exito = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identificador = trim($_POST['usuario']); 
    $nueva_clave = trim($_POST['nueva_clave']);
    $confirmar_clave = trim($_POST['confirmar_clave']);

    if (!empty($identificador) && !empty($nueva_clave) && !empty($confirmar_clave)) {
        if ($nueva_clave === $confirmar_clave) {
            $clave_encriptada = password_hash($nueva_clave, PASSWORD_BCRYPT);
            
            
            $sql = "UPDATE usuarios SET clave = ? WHERE cedula = ? OR correo = ?";
            $stmt = $conexion->prepare($sql);
            $stmt->bind_param("sss", $clave_encriptada, $identificador, $identificador);
            
            if ($stmt->execute()) {
                
                exit();
            } else {
                $error = "Error al actualizar la contraseña.";
            }
        } else {
            $error = "Las contraseñas no coinciden.";
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
    <title>Recuperar Contraseña - EVA Informática</title>
    <link rel="stylesheet" href="style.css?v=4.3">
</head>
<body class="auth-body">

    <div class="auth-wrapper">
        <h2>Restablecer Contraseña</h2>
        <p>Ingresa tus datos y tu nueva clave</p>
        
        <?php if (!empty($error)): ?>
            <div style="color: #e53e3e; background: #fff5f5; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px;">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="recuperar.php" method="POST">
            <div class="form-group">
                <label>Cédula o Correo:</label>
                <input type="text" name="usuario" placeholder="Ingresa tu cédula o correo" required>
            </div>
            
            <div class="form-group">
                <label>Nueva Contraseña:</label>
                <input type="password" name="nueva_clave" placeholder="••••••••" required>
            </div>

            <div class="form-group">
                <label>Confirmar Nueva Contraseña:</label>
                <input type="password" name="confirmar_clave" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="btn-auth">Actualizar Contraseña</button>
        </form>

        <div class="auth-footer">
            <p><a href="login.php">Volver al Inicio de Sesión</a></p>
        </div>
    </div>

</body>
</html>