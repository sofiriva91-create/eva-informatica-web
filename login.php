<?php
require_once 'conexion.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario']);
    $clave = trim($_POST['clave']);

    if (!empty($usuario) && !empty($clave)) {
        $consulta = $conexion->prepare("SELECT * FROM usuarios WHERE usuario = ?");
        $consulta->bind_param("s", $usuario);
        $consulta->execute();
        $resultado = $consulta->get_result();

        if ($resultado->num_rows === 1) {
            $datos_usuario = $resultado->fetch_assoc();
            if (password_verify($clave, $datos_usuario['clave']) || $clave === $datos_usuario['clave']) {
                $_SESSION['usuario'] = $datos_usuario['usuario'];
                header("Location: panel_estudiante.php");
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "El usuario no existe.";
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
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <header>
        <div class="logo-seccion">
            <img src="imagenes/logo-UPTAAPC.png" alt="Logo UPTAAPC" class="logo-img">
            <span>EVA Informática</span>
        </div>
        <nav>
            <a href="index.php">Inicio</a>
            <a href="registros.php" class="btn-registros">Registrarte Aquí</a>
        </nav>
    </header>

    <div class="form-contenedor">
        <h2>Iniciar Sesión</h2>

        <?php if (!empty($error)): ?>
            <div class="mensaje-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="campo">
                <label>Usuario</label>
                <input type="text" name="usuario" required>
            </div>
            <div class="campo">
                <label>Contraseña</label>
                <input type="password" name="clave" required>
            </div>
            <button type="submit" class="btn-enviar">Entrar</button>
        </form>
        <p style="text-align: center; margin-top: 15px;">
            <a href="recuperar.php" style="color: #2b6cb0;">¿Olvidaste tu contraseña?</a>
        </p>
    </div>

</body>
</html>