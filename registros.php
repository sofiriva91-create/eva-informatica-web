<?php
include("conexion.php");
$error = "";

// Solo procesar cuando se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = isset($_POST['cedula']) ? trim($_POST['cedula']) : '';
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $clave_raw = isset($_POST['clave']) ? trim($_POST['clave']) : '';
    $rol = isset($_POST['rol']) ? trim($_POST['rol']) : '';
    
    // Si es docente no pide sección, le asignamos "General" o "N/A". Si es estudiante, toma la sección elegida.
    if ($rol === 'docente') {
        $seccion = 'General';
    } else {
        $seccion = isset($_POST['seccion']) ? trim($_POST['seccion']) : '';
    }

    if (!empty($cedula) && !empty($nombre) && !empty($correo) && !empty($clave_raw) && !empty($rol)) {
        // Si es estudiante, verificamos que también haya elegido sección
        if ($rol === 'estudiante' && empty($seccion)) {
            $error = "Por favor seleccione una sección para el estudiante.";
        } else {
            $clave = password_hash($clave_raw, PASSWORD_BCRYPT);

            $sql = "INSERT INTO usuarios (cedula, nombre, correo, clave, rol, seccion) VALUES ('$cedula', '$nombre', '$correo', '$clave', '$rol', '$seccion')";
            
            if ($conexion->query($sql) === TRUE) {
                // Redirección automática al login tras registrarse con éxito
                header("Location: login.php");
                exit();
            } else {
                $error = "Ocurrió un error al registrar los datos: " . $conexion->error;
            }
        }
    } else {
        $error = "Por favor completa todos los campos obligatorios.";
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

    <div class="auth-wrapper" style="max-width: 480px;">
        <h2>Registro de Usuario</h2>
        <p>Crea tu cuenta para acceder al sistema</p>
        <img src="logo-uptaapc.png" alt="Logo UPTAAPC" class="auth-logo">
        
        <?php if (!empty($error)): ?>
            <div style="color: #e53e3e; background: #fff5f5; padding: 10px; border-radius: 5px; margin-bottom: 15px; font-size: 14px; border: 1px solid #feb2b2;">
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
                <select name="rol" id="rol-select" required onchange="toggleSeccion()">
                    <option value="" disabled selected>Seleccione un rol</option>
                    <option value="estudiante">Estudiante</option>
                    <option value="docente">Docente</option>
                </select>
            </div>

            <!-- Este campo de sección estará oculto por defecto y solo saldrá si se selecciona Estudiante -->
            <div class="form-group" id="seccion-grupo" style="display: none;">
                <label>Sección:</label>
                <select name="seccion" id="seccion-select">
                    <option value="" disabled selected>Seleccione una sección</option>
                    <option value="Sección A">Sección A</option>
                    <option value="Sección B">Sección B</option>
                </select>
            </div>
            
            
<div class="form-group" id="grupo-materia">
    <label for="materia">Materia Asignada:</label>
    <select name="materia" id="materia" class="form-control" required>
        <option value="" disabled selected>Selecciona tu materia</option>
        <option value="Informática Básica">Informática Básica</option>
        <option value="Programación I">Introducción a la Programación</option>
        <option value="Bases de Datos">Matemática Inicial</option>
        <option value="Redes de Computadoras">Redes de Computadoras</option>
        <option value="Ingeniería de Software">Ingeniería de Software</option>
        <option value="Sistemas Operativos">Sistemas Operativos</option>
    </select>
</div>
            <button type="submit" class="btn-auth">Completar Registro</button>
        </form>

        <div class="auth-footer">
            <p>¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a></p>
        </div>
    </div>

    <!-- SCRIPT PARA MOSTRAR/OCULTAR LA SECCIÓN DINÁMICAMENTE -->
    <script>
    function toggleSeccion() {
        var rol = document.getElementById('rol-select').value;
        var seccionGrupo = document.getElementById('seccion-grupo');
        var seccionSelect = document.getElementById('seccion-select');
        
        if (rol === 'estudiante') {
            seccionGrupo.style.display = 'block';
            seccionSelect.required = true;
        } else {
            seccionGrupo.style.display = 'none';
            seccionSelect.required = false;
            seccionSelect.value = ''; // Limpiar selección si cambia a docente
        }
    }
    </script>

</body>
</html>