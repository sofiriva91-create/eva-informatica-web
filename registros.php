<?php
include'../backend/conexion.php';
$error = "";


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cedula = isset($_POST['cedula']) ? trim($_POST['cedula']) : '';
    $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
    $clave_raw = isset($_POST['clave']) ? trim($_POST['clave']) : '';
    $rol = isset($_POST['rol']) ? trim($_POST['rol']) : '';
    $materia = isset($_POST['materia']) ? trim($_POST['materia']) : '';
    
    if ($rol === 'docente') {
        $seccion = 'General';
    } else {
        $seccion = isset($_POST['seccion']) ? trim($_POST['seccion']) : '';
    }

    if (!empty($cedula) && !empty($nombre) && !empty($correo) && !empty($clave_raw) && !empty($rol)) {
        
        if ($rol === 'estudiante' && empty($seccion)) {
            $error = "Por favor seleccione una sección para el estudiante.";
        } else {
            $clave = password_hash($clave_raw, PASSWORD_BCRYPT);

            $sql = "INSERT INTO usuarios (cedula, nombre, correo, clave, rol, seccion, materia) VALUES ('$cedula', '$nombre', '$correo', '$clave', '$rol', '$seccion', '$materia')";
            
            if ($conexion->query($sql) === TRUE) {
                
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

            
            <div class="form-group" id="seccion-grupo" style="display: none;">
                <label>Sección:</label>
                <select name="seccion" id="seccion-select">
                    <option value="" disabled selected>Seleccione una sección</option>
                    <option value="Sección A">Sección A</option>
                    <option value="Sección B">Sección B</option>
                </select>
            </div>
            
            
<div class="form-group" id="bloque-materia" style="display: none;"> 
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

    
    <script>
    document.getElementById('rol-select').addEventListener('change', function() {
        var rol = this.value;
        var bloqueMateria = document.getElementById('bloque-materia');
        var selectMateria = document.getElementById('materia');
        
        
        var seccionGrupo = document.getElementById('seccion-grupo'); 
        var seccionSelect = document.getElementById('seccion');

        if (rol === 'docente') {
            
            if (bloqueMateria) {
                bloqueMateria.style.display = 'block';
                selectMateria.setAttribute('required', 'required');
            }
            if (seccionGrupo) {
                seccionGrupo.style.display = 'none';
                if (seccionSelect) {
                    seccionSelect.removeAttribute('required');
                    seccionSelect.value = '';
                }
            }
        } else if (rol === 'estudiante') {
            
            if (bloqueMateria) {
                bloqueMateria.style.display = 'none';
                selectMateria.removeAttribute('required');
                selectMateria.value = '';
            }
            if (seccionGrupo) {
                seccionGrupo.style.display = 'block';
                if (seccionSelect) {
                    seccionSelect.setAttribute('required', 'required');
                }
            }
        }
    });

</script>
</body>
</html>