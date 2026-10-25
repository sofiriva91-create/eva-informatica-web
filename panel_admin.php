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
    
    .sidebar { 
        position: fixed !important; 
        left: 0 !important; 
        top: 0 !important; 
        width: 250px !important; 
        height: 100vh !important; 
        z-index: 1000 !important; 
    }
    
    .main-content { 
        position: absolute !important;
        left: 250px !important;
        right: 0 !important;
        top: 0 !important;
        width: auto !important;
        margin: 0 !important;
        padding: 40px 30px 30px 30px !important; 
        box-sizing: border-box !important; 
    }

    /* Estilos limpios para la cabecera y banner */
    .admin-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        margin-bottom: 20px;
        gap: 15px;
    }
    .admin-header h1 {
        margin: 0;
        font-size: 24px;
        color: #333;
    }
    .user-badge {
        background: white;
        padding: 8px 15px;
        border-radius: 8px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    .welcome-banner {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 25px;
        border-radius: 12px;
        margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .welcome-banner h2 {
        margin: 0 0 8px 0;
        font-size: 22px;
    }
    .welcome-banner p {
        margin: 0;
        opacity: 0.9;
        font-size: 14px;
    }

    /* Botones de acceso rápido con efecto elevación */
    .action-buttons-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 15px;
        margin-bottom: 30px;
    }
    .btn-accion {
        background: white;
        padding: 18px;
        border-radius: 10px;
        text-align: center;
        text-decoration: none;
        color: #333;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        font-weight: bold;
        display: block;
    }
    .btn-pdf { border-left: 4px solid #2a5298; }
    .btn-youtube { border-left: 4px solid #ff0000; }
    .btn-estudiantes { border-left: 4px solid #28a745; }

    .btn-accion:hover {
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.15);
    }
</style>
</head>

<body>
    
   <div class="sidebar">
    <div class="brand">📁 Entorno Virtual<br>"Informatica-PIU"</div>
    <ul>
        <!-- Botón Inicio corregido con vista=inicio -->
        <li><a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=inicio" class="<?php echo ($vista === 'inicio') ? 'active' : ''; ?>">🏠 Inicio</a></li>
        <li><a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=estudiantes" class="<?php echo ($vista === 'estudiantes') ? 'active' : ''; ?>">👨‍🎓 Estudiantes & Notas</a></li>
        <li><a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=materiales" class="<?php echo ($vista === 'materiales') ? 'active' : ''; ?>">📁 Materiales y Tareas</a></li>
        <li><a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=horario" class="<?php echo ($vista === 'horario') ? 'active' : ''; ?>">📅 Horario</a></li>
        <li><a href="#" onclick="alert('Soporte Técnico: Contacte al administrador del sistema.');">❓ Soporte</a></li>
    </ul>
</div>

<div class="main-content">

    <!-- CABECERA SUPERIOR (Siempre visible) -->
    <div class="admin-top-bar">
        <div class="admin-title-area">
            <h1><?php echo htmlspecialchars($materia_docente ?? 'Introducción a la Informática'); ?></h1>
            <div class="section-pills">
                <span style="font-size: 0.85rem; color: #64748b; font-weight: 500;">Sección:</span>
                <a href="panel_admin.php?seccion=A&vista=<?php echo $vista; ?>" class="section-pill <?php echo ($seccion_actual === 'A') ? 'active' : ''; ?>">Sección A</a>
                <a href="panel_admin.php?seccion=B&vista=<?php echo $vista; ?>" class="section-pill <?php echo ($seccion_actual === 'B') ? 'active' : ''; ?>">Sección B</a>
            </div>
        </div>
        
        <div class="user-info-text" style="text-align: right;">
            <span style="font-weight: bold; color: #0f172a;">"<?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Docente'); ?>"</span> 
            <span style="color: #64748b;">— Sección <?php echo htmlspecialchars($seccion_actual); ?></span>
            <br>
            <a href="logout.php" class="logout-link">Cerrar Sesión</a>
        </div>
    </div>

    <?php 
    $vista = isset($_GET['vista']) ? $_GET['vista'] : 'inicio';
    ?>

    <!-- ======================================================== -->
    <!-- VISTA 1: INICIO (SOLO SALUDO Y BOTONES) -->
    <!-- ======================================================== -->
    <?php if ($vista === 'inicio'): ?>
        <div class="welcome-banner" style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); color: white; padding: 30px; border-radius: 12px; margin-bottom: 25px;">
            <h2 style="margin: 0 0 10px 0;">¡Hola, <?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Docente'); ?>! 👋</h2>
            <p style="margin: 0; opacity: 0.95;">Qué bueno tenerte de vuelta en el Entorno Virtual de la UPTAAPC. Selecciona una opción en el menú lateral o utiliza los accesos rápidos para administrar tus contenidos.</p>
        </div>

        <div class="action-buttons-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px;">
            <a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=materiales" class="admin-action-card" style="background: #fff; padding: 25px; border-radius: 12px; text-align: center; text-decoration: none; color: #1e293b; border: 1px solid #e2e8f0; display: block; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <span style="font-size: 2rem; display: block; margin-bottom: 10px;">📄</span>
                <span style="font-weight: 600;">Subir PDF / Guía</span>
            </a>
            <a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=materiales" class="admin-action-card" style="background: #fff; padding: 25px; border-radius: 12px; text-align: center; text-decoration: none; color: #1e293b; border: 1px solid #e2e8f0; display: block; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <span style="font-size: 2rem; display: block; margin-bottom: 10px;">📺</span>
                <span style="font-weight: 600;">Pegar Enlace YouTube</span>
            </a>
            <a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=estudiantes" class="admin-action-card" style="background: #fff; padding: 25px; border-radius: 12px; text-align: center; text-decoration: none; color: #1e293b; border: 1px solid #e2e8f0; display: block; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <span style="font-size: 2rem; display: block; margin-bottom: 10px;">👥</span>
                <span style="font-weight: 600;">Ver Estudiantes y Notas</span>
            </a>
        </div>

    <!-- ======================================================== -->
    <!-- VISTA 2: MATERIALES Y TAREAS (SUBIR PDF Y YOUTUBE) -->
    <!-- ======================================================== -->
    <?php elseif ($vista === 'materiales'): ?>
        <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
            <h2>📁 Materiales, Guías y Enlaces de YouTube (Sección <?php echo $seccion_actual; ?>)</h2>
            <p style="color: #64748b; font-size: 0.95rem;">Sube documentos PDF desde tu computadora o comparte enlaces de clases de YouTube para los estudiantes de esta sección.</p>
            
            <form action="subir_material.php" method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
                <input type="hidden" name="seccion" value="<?php echo $seccion_actual; ?>">
                
                <div style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Título del Recurso:</label>
                    <input type="text" name="titulo" required placeholder="Ej: Guía de Ejercicios N° 1" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Tipo de Publicación:</label>
                    <select name="tipo" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                        <option value="pdf">📄 Subir Guía / PDF desde la PC</option>
                        <option value="youtube">📺 Enlace de Video de YouTube</option>
                    </select>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Seleccionar Archivo PDF:</label>
                    <input type="file" name="archivo_pdf" style="padding: 5px;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">O Enlace de YouTube:</label>
                    <input type="url" name="enlace_youtube" placeholder="https://www.youtube.com/watch?v=..." style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 6px;">
                </div>

                <button type="submit" style="background: #2563eb; color: #fff; padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Publicar Contenido</button>
            </form>
        </div>

    <!-- ======================================================== -->
    <!-- VISTA 3: ESTUDIANTES Y NOTAS (4 UNIDADES) -->
    <!-- ======================================================== -->
    <?php elseif ($vista === 'estudiantes'): ?>
        <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
            <h2>👥 Control de Estudiantes y Notas (Sección <?php echo $seccion_actual; ?>)</h2>
            <p style="color: #64748b; font-size: 0.95rem;">Visualiza los estudiantes inscritos en tu sección y asigna las calificaciones de las 4 unidades.</p>
            
            <div style="overflow-x: auto; margin-top: 20px;">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="background: #f1f5f9; border-bottom: 2px solid #cbd5e1;">
                            <th style="padding: 12px;">Estudiante</th>
                            <th style="padding: 12px; text-align: center;">Unidad I</th>
                            <th style="padding: 12px; text-align: center;">Unidad II</th>
                            <th style="padding: 12px; text-align: center;">Unidad III</th>
                            <th style="padding: 12px; text-align: center;">Unidad IV</th>
                            <th style="padding: 12px; text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sec_param = $seccion_actual;
                        $query_est = $conexion->prepare("SELECT id_usuario, nombre FROM usuarios WHERE rol = 'estudiante' AND seccion = ?");
                        if ($query_est) {
                            $query_est->bind_param("s", $sec_param);
                            $query_est->execute();
                            $res_est = $query_est->get_result();
                            
                            if ($res_est->num_rows > 0) {
                                while ($row_est = $res_est->fetch_assoc()) {
                                    echo '<tr style="border-bottom: 1px solid #e2e8f0;">';
                                    echo '<td style="padding: 12px; font-weight: 600; color: #1e293b;">' . htmlspecialchars($row_est['nombre']) . '</td>';
                                    echo '<form action="guardar_notas.php" method="POST">';
                                    echo '<input type="hidden" name="id_estudiante" value="' . $row_est['id_usuario'] . '">';
                                    echo '<input type="hidden" name="seccion" value="' . $seccion_actual . '">';
                                    echo '<td style="padding: 12px; text-align: center;"><input type="number" step="0.1" name="nota1" style="width: 60px; text-align: center; padding: 5px; border: 1px solid #cbd5e1; border-radius: 4px;" placeholder="0-20"></td>';
                                    echo '<td style="padding: 12px; text-align: center;"><input type="number" step="0.1" name="nota2" style="width: 60px; text-align: center; padding: 5px; border: 1px solid #cbd5e1; border-radius: 4px;" placeholder="0-20"></td>';
                                    echo '<td style="padding: 12px; text-align: center;"><input type="number" step="0.1" name="nota3" style="width: 60px; text-align: center; padding: 5px; border: 1px solid #cbd5e1; border-radius: 4px;" placeholder="0-20"></td>';
                                    echo '<td style="padding: 12px; text-align: center;"><input type="number" step="0.1" name="nota4" style="width: 60px; text-align: center; padding: 5px; border: 1px solid #cbd5e1; border-radius: 4px;" placeholder="0-20"></td>';
                                    echo '<td style="padding: 12px; text-align: center;"><button type="submit" style="background: #059669; color: #fff; border: none; padding: 6px 12px; border-radius: 4px; font-weight: 600; cursor: pointer;">Guardar</button></td>';
                                    echo '</form>';
                                    echo '</tr>';
                                }
                            } else {
                                echo '<tr><td colspan="6" style="padding: 20px; text-align: center; color: #64748b;">No hay estudiantes registrados en la Sección ' . $sec_param . '.</td></tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    <!-- ======================================================== -->
    <!-- VISTA 4: HORARIO -->
    <!-- ======================================================== -->
    <?php elseif ($vista === 'horario'): ?>
        <div style="background: #fff; padding: 25px; border-radius: 12px; border: 1px solid #e2e8f0; margin-bottom: 25px;">
            <h2>📅 Gestión de Horario de Clases (Sección <?php echo $seccion_actual; ?>)</h2>
            <p style="color: #64748b; font-size: 0.95rem;">Sube el archivo oficial con los días y horas de clase para esta sección.</p>
            
            <form action="subir_horario.php" method="POST" enctype="multipart/form-data" style="margin-top: 20px;">
                <input type="hidden" name="seccion" value="<?php echo $seccion_actual; ?>">
                
                <div style="margin-bottom: 15px;">
                    <label style="font-weight: 600; display: block; margin-bottom: 5px;">Archivo de Horario (PDF o Word):</label>
                    <input type="file" name="archivo_horario" required style="padding: 5px;">
                </div>

                <button type="submit" style="background: #059669; color: #fff; padding: 10px 20px; border: none; border-radius: 6px; font-weight: 600; cursor: pointer;">Subir Horario</button>
            </form>
        </div>

    <?php endif; ?>

</div>
    
    <div class="user-info-text" style="text-align: right;">
        <span style="font-weight: bold; color: #0f172a;">"<?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Docente'); ?>"</span> 
        <span style="color: #64748b;">— Sección <?php echo htmlspecialchars($seccion_actual); ?></span>
        <br>
        <a href="logout.php" class="logout-link">Cerrar Sesión</a>
    </div>
</div>
    <?php if (!isset($_GET['vista']) || $_GET['vista'] === 'inicio'): ?>
    
    <div class="welcome-banner">
        <div>
            <h2>¡Hola, <?php echo $_SESSION['nombre']; ?>! 👋</h2>
            <p>Qué bueno tenerte de vuelta en el Entorno Virtual de la UPTAAPC. Selecciona una opción en el menú lateral o utiliza los accesos rápidos para administrar tus contenidos.</p>
        </div>
        <div style="font-size: 50px; opacity: 0.8;">🎓</div>
    </div>

    <!-- Botones de Acción Rápida -->
    <div class="action-buttons-grid">
        <a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=materiales" class="btn-accion btn-pdf">
            📄 Subir PDF / Guía
        </a>
        <a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=materiales" class="btn-accion btn-youtube">
            ▶️ Pegar Enlace YouTube
        </a>
        <a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=estudiantes" class="btn-accion btn-estudiantes">
            👥 Ver Estudiantes y Notas
        </a>
    </div>
<?php endif; ?>

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
                <div class="side-panel" id="crear-contenido">
                    <h3>Crear Nuevo Contenido</h3>
                    <form method="POST" action="" enctype="multipart/form-data">
                        <label><strong>Título del recurso:</strong></label>
                        <input type="text" name="titulo" placeholder="Ej: Guía de Estudio / Horario" required>

                        <label><strong>Tipo de Publicación:</strong></label>
                        <select name="tipo" id="tipoSelect" onchange="cambiarTipo()">
                           <a href="#crear-contenido" class="btn-accion btn-pdf">📄 Subir PDF / Guía</a>
<a href="#crear-contenido" class="btn-accion btn-youtube">▶️ Pegar Enlace YouTube</a>
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

    function seleccionarFormulario(tipo) {
        var selectTipo = document.getElementById('tipoSelect'); // Asegúrate que tu selector tenga este id
        if (selectTipo) {
            if (tipo === 'PDF') {
                selectTipo.value = 'Subir Guía / PDF'; // O el valor que tenga tu opción en el select
            } else if (tipo === 'Video') {
                selectTipo.value = 'Video Tutorial'; // O el valor que tenga tu opción de video
            }
            // Ejecutamos tu función existente para que muestre u oculte los campos correspondientes
            cambiarTipo(); 
        }
    }
</script>
    
    </script>
</body>
</html>