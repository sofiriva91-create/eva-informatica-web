<?php
session_start();
include('conexion.php');


if (!isset($_SESSION['nombre']) || $_SESSION['rol'] != 'docente') {
    header("Location: login.php");
    exit();
}

$seccion_actual = isset($_GET['seccion']) ? $_GET['seccion'] : 'A';
$vista = isset($_GET['vista']) ? $_GET['vista'] : 'contenido';
$mensaje = "";


function obtenerYouTubeID($url) {
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
    return isset($match[1]) ? $match[1] : false;
}

if (isset($_POST['actualizar_notas'])) {
    $cedula_estudiante = $_POST['cedula'];
    $u1 = $_POST['unidad1'];
    $u2 = $_POST['unidad2'];
    $u3 = $_POST['unidad3'];
    $u4 = $_POST['unidad4'];

    $sql_update_notas = "UPDATE usuarios SET unidad1='$u1', unidad2='$u2', unidad3='$u3', unidad4='$u4' WHERE cedula='$cedula_estudiante'";
    if ($conexion->query($sql_update_notas)) {
        $mensaje = "¡Notas actualizadas con éxito para el estudiante Cédula: $cedula_estudiante!";
    } else {
        $mensaje = "Error al actualizar notas: " . $conexion->error;
    }
}


if (isset($_POST['publicar'])) {
    $titulo = $conexion->real_escape_string($_POST['titulo']);
    $tipo = $conexion->real_escape_string($_POST['tipo']);
    $seccion_recurso = $conexion->real_escape_string($_POST['seccion_recurso']);
    $enlace_final = "";

    if ($tipo === 'Video Tutorial') {
        $enlace_final = trim($_POST['enlace']);
    } else {
        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
            $nombre_archivo = time() . "_" . $_FILES['archivo']['name'];
            $ruta_destino = "uploads/" . $nombre_archivo;

            if (!is_dir('uploads')) {
                mkdir('uploads', 0777, true);
            }

            if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino)) {
                $enlace_final = $ruta_destino;
            } else {
                $mensaje = "Error al mover el archivo al servidor.";
            }
        } else {
            $mensaje = "Por favor selecciona un archivo válido.";
        }
    }

    if ($enlace_final != "") {
        $sql_insert = "INSERT INTO archivos (titulo, enlace, tipo, seccion) VALUES ('$titulo', '$enlace_final', '$tipo', '$seccion_recurso')";
        $conexion->query($sql_insert);
        $mensaje = "¡Contenido publicado con éxito!";
    }
}


if ($vista === 'estudiantes') {
    $sql_estudiantes = "SELECT * FROM usuarios WHERE rol = 'estudiante' AND seccion = '$seccion_actual'";
    $resultado_estudiantes = $conexion->query($sql_estudiantes);
} else {
    $sql_archivos = "SELECT * FROM archivos WHERE seccion = '$seccion_actual'";
    $resultado_archivos = $conexion->query($sql_archivos);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Entorno Virtual - Panel del Docente</title>
    <link rel="stylesheet" href="style.css">
    <style>
    body { 
        margin: 0 !important; 
        padding: 0 !important;
        overflow-x: hidden !important; 
        background-color: #f8f9fa !important; 
    }
    
    /* El menú se queda fijo y quieto a la izquierda */
    .sidebar { 
        position: fixed !important; 
        left: 0 !important; 
        top: 0 !important; 
        width: 250px !important; 
        height: 100vh !important; 
        z-index: 9999 !important; 
    }
    
    /* El contenido ocupa exactamente desde los 250px hasta el final de la pantalla */
    .main-content { 
        position: absolute !important;
        left: 250px !important;
        right: 0 !important;
        top: 0 !important;
        width: auto !important;
        margin: 0 !important;
        padding: 30px !important; 
        box-sizing: border-box !important; 
    }
</style>
</head>

<body>
    
    <div class="sidebar">
        <div class="brand">📁 Entorno Virtual<br>"Informatica-PIU"</div>
        <div>
        <ul>
            <li><a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=contenido" class="<?php echo ($vista === 'contenido') ? 'active' : ''; ?>">📊 Dashboard</a></li>
            <li><a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=contenido">📚 Cursos</a></li>
            <li><a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=estudiantes" class="<?php echo ($vista === 'estudiantes') ? 'active' : ''; ?>">👨‍🎓 Estudiantes & Notas</a></li>
            <li><a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=contenido">📄 Materiales y Tareas</a></li>
            <li><a href="#">💬 Foros</a></li>
            <li><a href="#">📅 Horario</a></li>
            <li><a href="#">❓ Soporte</a></li>
        </ul>
    </div>

        <div class="header-top">
            <h1>Introducción a la Informática (Sección <?php echo $seccion_actual; ?>)</h1>
            <div class="user-info">
                <span>👤 <?php echo htmlspecialchars($_SESSION['nombre']); ?> (Docente)</span>
                <a href="logout.php">Cerrar Sesión</a>
            </div>
        </div>

        <div class="admin-banner">
            <h2>Administración de Curso: Introducción a la Informática</h2>
            <div class="section-select">
                <label>Cambiar Sección: </label>
                <select onchange="location = this.value;">
                    <option value="panel_admin.php?seccion=A&vista=<?php echo $vista; ?>" <?php echo ($seccion_actual === 'A') ? 'selected' : ''; ?>>Sección A</option>
                    <option value="panel_admin.php?seccion=B&vista=<?php echo $vista; ?>" <?php echo ($seccion_actual === 'B') ? 'selected' : ''; ?>>Sección B</option>
                </select>
            </div>
        </div>

        <div class="sub-nav">
            <a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=contenido" class="<?php echo ($vista === 'contenido') ? 'active' : ''; ?>">Contenido / Materiales</a>
            <a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=estudiantes" class="<?php echo ($vista === 'estudiantes') ? 'active' : ''; ?>">Estudiantes y Notas</a>
        </div>

        <?php if ($mensaje != ""): ?>
            <p style="color: green; font-weight: bold; margin-bottom: 15px;"><?php echo $mensaje; ?></p>
        <?php endif; ?>

        <?php if ($vista === 'estudiantes'): ?>
            
            <h3>Control de Notas y Estudiantes (Sección <?php echo $seccion_actual; ?>)</h3><br>
            <?php if ($resultado_estudiantes && $resultado_estudiantes->num_rows > 0): ?>
                <div style="overflow-x:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Cédula</th>
                                <th>Nombre Completo</th>
                                <th>Correo</th>
                                <th>Unidad 1</th>
                                <th>Unidad 2</th>
                                <th>Unidad 3</th>
                                <th>Unidad 4</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($est = $resultado_estudiantes->fetch_assoc()): ?>
                                <tr>
                                    <form method="POST" action="">
                                        <td><?php echo htmlspecialchars($est['cedula']); ?>
                                            <input type="hidden" name="cedula" value="<?php echo $est['cedula']; ?>">
                                        </td>
                                        <td><?php echo htmlspecialchars($est['nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($est['correo']); ?></td>
                                        <td><input type="text" name="unidad1" value="<?php echo htmlspecialchars($est['unidad1']); ?>" style="width: 50px; text-align: center;"></td>
                                        <td><input type="text" name="unidad2" value="<?php echo htmlspecialchars($est['unidad2']); ?>" style="width: 50px; text-align: center;"></td>
                                        <td><input type="text" name="unidad3" value="<?php echo htmlspecialchars($est['unidad3']); ?>" style="width: 50px; text-align: center;"></td>
                                        <td><input type="text" name="unidad4" value="<?php echo htmlspecialchars($est['unidad4']); ?>" style="width: 50px; text-align: center;"></td>
                                        <td>
                                            <button type="submit" name="actualizar_notas" style="background: #2563eb; color: #fff; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer;">Guardar</button>
                                        </td>
                                    </form>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <p>No hay estudiantes registrados en esta sección aún.</p>
            <?php endif; ?>

        <?php else: ?>

            <div class="content-grid">
                <div>
                    <h3>Material de Clase y Publicaciones (Sección <?php echo $seccion_actual; ?>)</h3><br>

                    <?php if ($resultado_archivos && $resultado_archivos->num_rows > 0): ?>
                        <?php while($recurso = $resultado_archivos->fetch_assoc()): ?>
                            <?php $yt_id = obtenerYouTubeID($recurso['enlace']); ?>
                            
                            <?php if ($yt_id): ?>
                                <div class="card">
                                    <div class="youtube-card">
                                        <h4>🎥 <?php echo htmlspecialchars($recurso['titulo']); ?></h4>
                                        <iframe src="https://www.youtube.com/embed/<?php echo $yt_id; ?>" allowfullscreen></iframe>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="card">
                                    <div class="card-icon">📄</div>
                                    <div class="card-details">
                                        <h4><?php echo htmlspecialchars($recurso['titulo']); ?> <span style="font-size: 11px; background:#e2e8f0; padding:2px 6px; border-radius:4px;"><?php echo htmlspecialchars($recurso['tipo']); ?></span></h4>
                                        <p><a href="<?php echo htmlspecialchars($recurso['enlace']); ?>" target="_blank" style="color: #2563eb; font-weight: bold;">📥 Descargar / Ver Documento</a></p>
                                    </div>
                                </div>
                            <?php endif; ?>

                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No hay contenidos publicados en esta sección todavía.</p>
                    <?php endif; ?>
                </div>

                <!-- Formulario Lateral -->
                <div class="side-panel">
                    <h3>Crear Nuevo Contenido</h3>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <label><strong>Título del recurso:</strong></label>
                        <input type="text" name="titulo" placeholder="Ej: Guía de Estudio / Horario" required>

                        <label><strong>Tipo de Publicación:</strong></label>
                        <select name="tipo" id="tipoSelect" onchange="cambiarTipo()">
                            <option value="Guía de Estudio PDF">📄 Subir Guía / PDF</option>
                            <option value="Video Tutorial">▶️ Enlace de YouTube</option>
                            <option value="Horario">📅 Horario (Subir PDF/Word)</option>
                            <option value="Calificaciones Archivo">📝 Subir Archivo de Calificaciones</option>
                        </select>

                        <div id="campoEnlace" style="display:none; margin-top:10px;">
                            <label><strong>Enlace de YouTube:</strong></label>
                            <input type="text" name="enlace" placeholder="Ej: https://youtube.com/watch?v=...">
                        </div>

                        <div id="campoArchivo" style="margin-top:10px;">
                            <label><strong>Seleccionar Archivo de la PC:</strong></label>
                            <input type="file" name="archivo" accept=".pdf,.doc,.docx">
                        </div>

                        <input type="hidden" name="seccion_recurso" value="<?php echo $seccion_actual; ?>">
                        <br>
                        <button type="submit" name="publicar">Publicar Contenido</button>
                    </form>
                </div>
            </div>

        <?php endif; ?>

    </div>

    <script>
    function cambiarTipo() {
        var select = document.getElementById('tipoSelect').value;
        var campoEnlace = document.getElementById('campoEnlace');
        var campoArchivo = document.getElementById('campoArchivo');

        if (select === 'Video Tutorial') {
            campoEnlace.style.display = 'block';
            campoArchivo.style.display = 'none';
        } else {
            campoEnlace.style.display = 'none';
            campoArchivo.style.display = 'block';
        }
    }
    </script>
</body>
</html>