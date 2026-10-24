<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('conexion.php');

if (!isset($_SESSION['nombre']) || !isset($_SESSION['rol']) || strtolower($_SESSION['rol']) !== 'docente') {
    header("Location: login.php");
    exit();
}

$materia_docente = '';
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'docente') {
    $cedula_actual = $_SESSION['cedula'] ?? '';
    $stmt_doc = $conexion->prepare("SELECT materia FROM usuarios WHERE cedula = ?");
    $stmt_doc->bind_param("s", $cedula_actual);
    $stmt_doc->execute();
    $res_doc = $stmt_doc->get_result();
    if ($row_doc = $res_doc->fetch_assoc()) {
        $materia_docente = $row_doc['materia'];
    }

}
if (isset($_SESSION['rol']) && $_SESSION['rol'] === 'docente') {
    $materia_actual = $materia_docente;
} else {
    $materia_actual = isset($_GET['materia']) ? $_GET['materia'] : '';
}

$seccion_actual = isset($_GET['seccion']) ? $_GET['seccion'] : '';
$vista = isset($_GET['vista']) ? $_GET['vista'] : 'dashboard';
$tipo_solicitado = isset($_GET['tipo']) ? $_GET['tipo'] : '';
$mensaje = "";

$conexion->query("CREATE TABLE IF NOT EXISTS config_materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    materia VARCHAR(100) UNIQUE,
    docente_asignado VARCHAR(150)
)");

if (isset($_POST['guardar_docente_materia'])) {
    $docente_input = $conexion->real_escape_string($_POST['nombre_docente_materia']);
    $mat_target = $conexion->real_escape_string($_POST['materia_target']);
    
    $check = $conexion->query("SELECT id FROM config_materias WHERE materia = '$mat_target'");
    if ($check && $check->num_rows > 0) {
        $conexion->query("UPDATE config_materias SET docente_asignado = '$docente_input' WHERE materia = '$mat_target'");
    } else {
        $conexion->query("INSERT INTO config_materias (materia, docente_asignado) VALUES ('$mat_target', '$docente_input')");
    }
    $mensaje = "¡Nombre del docente guardado para $mat_target!";
}

function obtenerYouTubeID($url) {
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
    return isset($match[1]) ? $match[1] : false;
}


if (isset($_GET['eliminar_recurso'])) {
    $id_recurso = intval($_GET['eliminar_recurso']);
    
    
    $res_file = $conexion->query("SELECT enlace, tipo FROM archivos WHERE id = $id_recurso LIMIT 1");
    if ($res_file && $res_file->num_rows > 0) {
        $dat_file = $res_file->fetch_assoc();
        
        if ($dat_file['tipo'] !== 'Video Tutorial' && file_exists($dat_file['enlace'])) {
            @unlink($dat_file['enlace']);
        }
    }
    
    $conexion->query("DELETE FROM archivos WHERE id = $id_recurso");
    header("Location: panel_admin.php?materia=" . urlencode($materia_actual) . "&seccion=" . $seccion_actual . "&vista=materiales&msg=eliminado");
    exit();
}

if (isset($_GET['msg']) && $_GET['msg'] === 'eliminado') {
    $mensaje = "¡Recurso eliminado correctamente!";
}

// 1. Guardar o Actualizar Notas
if (isset($_POST['actualizar_notas'])) {
    $cedula_est = $conexion->real_escape_string($_POST['cedula']);
    $mat = $conexion->real_escape_string($_POST['materia_nota']);
    $sec = $conexion->real_escape_string($_POST['seccion_nota']);
    $u1 = $conexion->real_escape_string($_POST['unidad1']);
    $u2 = $conexion->real_escape_string($_POST['unidad2']);
    $u3 = $conexion->real_escape_string($_POST['unidad3']);
    $u4 = $conexion->real_escape_string($_POST['unidad4']);

    $verificar = $conexion->query("SELECT id FROM notas WHERE cedula_estudiante = '$cedula_est' AND materia = '$mat'");
    if ($verificar && $verificar->num_rows > 0) {
        $sql_guardar = "UPDATE notas SET unidad1='$u1', unidad2='$u2', unidad3='$u3', unidad4='$u4', seccion='$sec' WHERE cedula_estudiante='$cedula_est' AND materia='$mat'";
    } else {
        $sql_guardar = "INSERT INTO notas (cedula_estudiante, materia, seccion, unidad1, unidad2, unidad3, unidad4) VALUES ('$cedula_est', '$mat', '$sec', '$u1', '$u2', '$u3', '$u4')";
    }
    if ($conexion->query($sql_guardar)) {
        $mensaje = "¡Calificaciones guardadas exitosamente!";
    } else {
        $mensaje = "Error al guardar notas: " . $conexion->error;
    }
}

// 2. Publicar recursos (Guías o YouTube)
if (isset($_POST['publicar'])) {
    $titulo = $conexion->real_escape_string($_POST['titulo']);
    $tipo = $conexion->real_escape_string($_POST['tipo']);
    $materia_pub = $conexion->real_escape_string($_POST['materia_pub']);
    $seccion_recurso = $conexion->real_escape_string($_POST['seccion_recurso']);
    $enlace_final = "";

    if ($tipo === 'Video Tutorial') {
        $enlace_final = trim($_POST['enlace']);
    } else {
        if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
            $nombre_archivo = time() . "_" . $_FILES['archivo']['name'];
            $ruta_destino = "uploads/" . $nombre_archivo;
            if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
            if (move_uploaded_file($_FILES['archivo']['tmp_name'], $ruta_destino)) {
                $enlace_final = $ruta_destino;
            }
        }
    }

    if ($enlace_final != "") {
        $conexion->query("INSERT INTO archivos (titulo, enlace, tipo, seccion, materia) VALUES ('$titulo', '$enlace_final', '$tipo', '$seccion_recurso', '$materia_pub')");
        $mensaje = "¡Publicación realizada con éxito!";
    }
}

// 3. Subir Plan de Evaluación PDF/Word
if (isset($_POST['subir_plan_evaluacion'])) {
    $mat_plan = $conexion->real_escape_string($_POST['materia_plan']);
    $sec_plan = $conexion->real_escape_string($_POST['seccion_plan']);
    if (isset($_FILES['archivo_plan']) && $_FILES['archivo_plan']['error'] == 0) {
        $nombre_p = "plan_" . time() . "_" . $_FILES['archivo_plan']['name'];
        $ruta_p = "uploads/" . $nombre_p;
        if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        if (move_uploaded_file($_FILES['archivo_plan']['tmp_name'], $ruta_p)) {
            $conexion->query("DELETE FROM archivos WHERE tipo = 'Plan_Evaluacion' AND materia = '$mat_plan' AND seccion = '$sec_plan'");
            $conexion->query("INSERT INTO archivos (titulo, enlace, tipo, seccion, materia) VALUES ('Plan de Evaluación Oficial', '$ruta_p', 'Plan_Evaluacion', '$sec_plan', '$mat_plan')");
            $mensaje = "¡Plan de evaluación subido con éxito!";
        }
    }
}

// 4. Subir Horario PDF
if (isset($_POST['subir_horario_pdf'])) {
    $sec_horario = $conexion->real_escape_string($_POST['seccion_horario']);
    if (isset($_FILES['pdf_horario']) && $_FILES['pdf_horario']['error'] == 0) {
        $nombre_h = "horario_" . $sec_horario . "_" . time() . ".pdf";
        $ruta_h = "uploads/" . $nombre_h;
        if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
        if (move_uploaded_file($_FILES['pdf_horario']['tmp_name'], $ruta_h)) {
            $conexion->query("DELETE FROM archivos WHERE tipo = 'Horario_Institucional' AND seccion = '$sec_horario'");
            $conexion->query("INSERT INTO archivos (titulo, enlace, tipo, seccion, materia) VALUES ('Horario Oficial Institucional', '$ruta_h', 'Horario_Institucional', '$sec_horario', 'General')");
            $mensaje = "¡Horario institucional subido con éxito!";
        }
    }
}

// Conteo para estadísticas dinámicas
$total_estudiantes = $conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE rol = 'estudiante' AND seccion = '$seccion_actual'")->fetch_assoc()['total'] ?? 0;
$total_guias = $conexion->query("SELECT COUNT(*) as total FROM archivos WHERE tipo != 'Video Tutorial' AND tipo != 'Horario_Institucional' AND tipo != 'Plan_Evaluacion'")->fetch_assoc()['total'] ?? 0;
$total_videos = $conexion->query("SELECT COUNT(*) as total FROM archivos WHERE tipo = 'Video Tutorial'")->fetch_assoc()['total'] ?? 0;

// Consultar docente asignado
$res_doc = $conexion->query("SELECT docente_asignado FROM config_materias WHERE materia = '$materia_actual' LIMIT 1");
$row_doc = ($res_doc && $res_doc->num_rows > 0) ? $res_doc->fetch_assoc() : ['docente_asignado' => 'No asignado'];
$docente_actual_materia = $row_doc['docente_asignado'];

// Consultas según la vista
if ($vista === 'estudiantes_general') {
    $resultado_estudiantes_general = $conexion->query("SELECT * FROM usuarios WHERE rol = 'estudiante' AND seccion = '$seccion_actual'");
} elseif ($vista === 'calificaciones') {
    $resultado_estudiantes = $conexion->query("SELECT * FROM usuarios WHERE rol = 'estudiante' AND seccion = '$seccion_actual'");
} elseif ($vista === 'plan_evaluacion') {
    $resultado_plan = $conexion->query("SELECT * FROM archivos WHERE tipo = 'Plan_Evaluacion' AND materia = '$materia_actual' AND seccion = '$seccion_actual'");
} elseif ($vista === 'horario') {
    $resultado_horario = $conexion->query("SELECT * FROM archivos WHERE tipo = 'Horario_Institucional' AND seccion = '$seccion_actual'");
} elseif ($vista === 'materiales') {
    $resultado_archivos = $conexion->query("SELECT * FROM archivos WHERE seccion = '$seccion_actual' AND materia = '$materia_actual' AND tipo != 'Horario_Institucional' AND tipo != 'Plan_Evaluacion'");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Docente - Entorno Virtual</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">

    <!-- BARRA LATERAL (SIDEBAR) -->
    <div class="sidebar">
        <div class="sidebar-brand">
            🖥️Entorno Virtual PIU
        </div>
        <ul class="sidebar-menu">
            <li><a href="panel_admin.php?vista=dashboard" class="<?php echo ($vista==='dashboard')?'active':''; ?>">🏠 Inicio</a></li>

            </li>
            <li><a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=estudiantes_general" class="<?php echo ($vista==='estudiantes_general')?'active':''; ?>">👨‍🎓 Estudiantes</a></li>
            <li><a href="panel_admin.php?materia=<?php echo urlencode($materia_actual); ?>&seccion=<?php echo $seccion_actual; ?>&vista=materiales" class="<?php echo ($vista==='materiales')?'active':''; ?>">📂 Materiales y Tareas</a></li>
            <li><a href="panel_admin.php?materia=<?php echo urlencode($materia_actual); ?>&seccion=<?php echo $seccion_actual; ?>&vista=plan_evaluacion" class="<?php echo ($vista==='plan_evaluacion')?'active':''; ?>">📊 Plan de Evaluación</a></li>
            <li><a href="panel_admin.php?materia=<?php echo urlencode($materia_actual); ?>&seccion=<?php echo $seccion_actual; ?>&vista=calificaciones" class="<?php echo ($vista==='calificaciones')?'active':''; ?>">📝 Calificaciones</a></li>
            <li><a href="panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=horario" class="<?php echo ($vista==='horario')?'active':''; ?>">📅 Horario</a></li>
            <li><a href="panel_admin.php?vista=soporte" class="<?php echo ($vista==='soporte')?'active':''; ?>">🛠️ Soporte</a></li>
        </ul>
    </div>

    <!-- CONTENEDOR PRINCIPAL -->
    <div class="main-wrapper">
        <!-- HEADER SUPERIOR -->
        <div class="header">
            <!-- Botón de menú desplegable para celulares -->
<button class="menu-toggle" onclick="toggleMenu()">☰ Menú</button>
            <div class="header-title" style="display: flex; align-items: center; gap: 15px;">
                <span><?php echo htmlspecialchars($materia_actual); ?></span>
                <div class="section-badges">
                    <a href="panel_admin.php?materia=<?php echo urlencode($materia_actual); ?>&seccion=A&vista=<?php echo $vista; ?>" class="badge-btn <?php echo ($seccion_actual==='A')?'active':''; ?>">Sección A</a>
                    <a href="panel_admin.php?materia=<?php echo urlencode($materia_actual); ?>&seccion=B&vista=<?php echo $vista; ?>" class="badge-btn <?php echo ($seccion_actual==='B')?'active':''; ?>">Sección B</a>
                </div>
            </div>
            <div class="user-info">
                <div class="user-details">
                    <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?> (Docente)</strong>
                    <a href="logout.php" class="logout-link">Cerrar Sesión</a>
                </div>
            </div>
        </div>

        <!-- AREA DE CONTENIDO -->
        <div class="content">
            
            <?php if ($mensaje != ""): ?>
                <div class="alert-success"><?php echo $mensaje; ?></div>
            <?php endif; ?>

            <?php if ($vista === 'dashboard'): ?>
                <div class="page-header">
                    <h2>Bienvenid@, <?php echo htmlspecialchars($_SESSION['nombre']); ?> 👋</h2>
                    <p style="color: #6c757d; font-size: 0.9rem;">Panel de control principal del aula virtual — Sección Activa: <strong><?php echo $seccion_actual; ?></strong></p>
                </div>

                <!-- TARJETAS MÉTRICAS SUPERIORES CON EFECTO HOVER -->
                <div class="stats-container">
                    <div class="stat-card hover-effect" onclick="window.location.href='panel_admin.php?seccion=<?php echo $seccion_actual; ?>&vista=estudiantes_general'">
                        <div class="stat-icon blue">🎓</div>
                        <div class="stat-info">
                            <span>Estudiantes (Sección <?php echo $seccion_actual; ?>)</span>
                            <h3><?php echo $total_estudiantes; ?> Alumnos</h3>
                        </div>
                    </div>

                    <div class="stat-card hover-effect" onclick="window.location.href='panel_admin.php?materia=<?php echo urlencode($materia_actual); ?>&seccion=<?php echo $seccion_actual; ?>&vista=materiales'">
                        <div class="stat-icon green">📑</div>
                        <div class="stat-info">
                            <span>Recursos Publicados</span>
                            <h3><?php echo $total_guias; ?> Guías / PDFs</h3>
                        </div>
                    </div>

                    <div class="stat-card hover-effect" onclick="window.location.href='panel_admin.php?materia=<?php echo urlencode($materia_actual); ?>&seccion=<?php echo $seccion_actual; ?>&vista=materiales&tipo=youtube'">
                        <div class="stat-icon red">▶️</div>
                        <div class="stat-info">
                            <span>Enlaces YouTube</span>
                            <h3><?php echo $total_videos; ?> Clases / Videos</h3>
                        </div>
                    </div>
                </div>

                <div class="grid-layout">
                    <div class="card">
                        <div class="card-title">Publicar Contenido Directo — <small style="color: #6c757d;">Para: <?php echo htmlspecialchars($materia_actual); ?> (Sección <?php echo htmlspecialchars($seccion_actual); ?>)</small></div>
                        
                        <div class="action-grid">
                            <a href="panel_admin.php?materia=<?php echo urlencode($materia_actual); ?>&seccion=<?php echo $seccion_actual; ?>&vista=materiales" class="action-card-btn">
                                <span style="font-size: 1.8rem;">📄</span>
                                <h4>Subir PDF / Guía</h4>
                                <p>Carga material de estudio</p>
                            </a>

                            <a href="panel_admin.php?materia=<?php echo urlencode($materia_actual); ?>&seccion=<?php echo $seccion_actual; ?>&vista=materiales&tipo=youtube" class="action-card-btn">
                                <span style="font-size: 1.8rem;">▶️</span>
                                <h4>Pegar Link YouTube</h4>
                                <p>Clases y videotutoriales</p>
                            </a>

                            <a href="panel_admin.php?materia=<?php echo urlencode($materia_actual); ?>&seccion=<?php echo $seccion_actual; ?>&vista=calificaciones" class="action-card-btn">
                                <span style="font-size: 1.8rem;">📊</span>
                                <h4>Registrar Notas</h4>
                                <p>Planilla de evaluaciones</p>
                            </a>
                        </div>
                    </div>

                    <div class="card">
    <div class="card-title">Asignaturas y Secciones (PIU)</div>
    
    <div class="lista-materias-container">
        <?php 
        $materias_piu = ['Informática Básica', 'Matemática Inicial', 'Algorítmica y Programación', 'Técnicas de Estudio'];
        foreach ($materias_piu as $mat):
            $es_materia_activa = ($materia_actual === $mat);
            
            // Ejemplo rápido: Contar cuántos alumnos hay en la Sección A para esta materia
            $q_a = $conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='estudiante' AND seccion='A'");
            $d_a = $q_a->fetch_assoc();
            $total_a = $d_a['total'];
            $porcentaje_a = min(round(($total_a / 40) * 100), 100); // Asumiendo 40 cupos máximos
        ?>
        <div class="card">
    <div class="card-title">Asignatura Asignada</div>
    
    <div class="lista-materias-container">
        <?php 
        // Obtenemos la materia asignada al docente actual desde la base de datos
        $materia_docente = $usr_data['materia'] ?? ($materia_actual ?: 'Informática Básica');
        
        // Consultar alumnos para la Sección A de esta materia
        $q_a = $conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='estudiante' AND materia='$materia_docente' AND seccion='A'");
        $d_a = $q_a->fetch_assoc();
        $total_a = $d_a['total'];
        $porcentaje_a = min(round(($total_a / 40) * 100), 100); // Base de 40 cupos máximos

        // Consultar alumnos para la Sección B de esta materia
        $q_b = $conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='estudiante' AND materia='$materia_docente' AND seccion='B'");
        $d_b = $q_b->fetch_assoc();
        $total_b = $d_b['total'];
        $porcentaje_b = min(round(($total_b / 40) * 100), 100);
        ?>
        
        <div class="card">
    <div class="card-title">Asignatura Asignada</div>
    
    <div class="lista-materias-container">
        <?php 
        // Obtenemos la materia del docente de forma segura
        $materia_docente = '';
        if (isset($usr_data['materia'])) {
            $materia_docente = $usr_data['materia'];
        } elseif (isset($_SESSION['materia'])) {
            $materia_docente = $_SESSION['materia'];
        } else {
            $materia_docente = 'Informática Básica';
        }
        
        // Consultar alumnos para la Sección A de esta materia
        $total_a = 0;
        if (isset($conexion)) {
            $q_a = $conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='estudiante' AND materia='$materia_docente' AND seccion='A'");
            if ($q_a) {
                $d_a = $q_a->fetch_assoc();
                $total_a = $d_a['total'] ?? 0;
            }
        }
        $porcentaje_a = min(round(($total_a / 40) * 100), 100);

        // Consultar alumnos para la Sección B de esta materia
        $total_b = 0;
        if (isset($conexion)) {
            $q_b = $conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='estudiante' AND materia='$materia_docente' AND seccion='B'");
            if ($q_b) {
                $d_b = $q_b->fetch_assoc();
                $total_b = $d_b['total'] ?? 0;
            }
        }
        $porcentaje_b = min(round(($total_b / 40) * 100), 100);

        $sec_activa = isset($seccion_actual) ? $seccion_actual : 'A';
        ?>
        
        <div class="materia-item-card">
            <h4><?php echo htmlspecialchars($materia_docente); ?></h4>
            
            <div class="secciones-progreso-wrapper">
                <!-- Sección A -->
                <div class="seccion-progreso-box">
                    <a href="panel_admin.php?materia=<?php echo urlencode($materia_docente); ?>&seccion=A&vista=dashboard" 
                       class="enlace-seccion <?php echo ($sec_activa === 'A') ? 'activo' : ''; ?>">
                       Sec. A
                    </a>
                    <div class="progress-ring" style="--p: <?php echo $porcentaje_a; ?>;">
                        <span class="progress-value"><?php echo $porcentaje_a; ?>%</span>
                    </div>
                    <span class="info-alumnos"><?php echo $total_a; ?> alumnos</span>
                </div>

                <!-- Sección B -->
                <div class="seccion-progreso-box">
                    <a href="panel_admin.php?materia=<?php echo urlencode($materia_docente); ?>&seccion=B&vista=dashboard" 
                       class="enlace-seccion <?php echo ($sec_activa === 'B') ? 'activo' : ''; ?>">
                       Sec. B
                    </a>
                    <div class="progress-ring" style="--p: <?php echo $porcentaje_b; ?>;">
                        <span class="progress-value"><?php echo $porcentaje_b; ?>%</span>
                    </div>
                    <span class="info-alumnos"><?php echo $total_b; ?> alumnos</span>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
} elseif ($vista === 'estudiantes_general') { 
?>
                <div class="page-header">
                    <h2>Estudiantes Inscritos - Sección <?php echo htmlspecialchars($seccion_actual); ?></h2>
                </div>
                <div class="card">
                    <div class="card-title">Listado Oficial de Alumnos (Sección <?php echo htmlspecialchars($seccion_actual); ?>)</div>
                    <?php if ($resultado_estudiantes_general && $resultado_estudiantes_general->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Cédula</th>
                                    <th>Nombre del Estudiante</th>
                                    <th>Sección</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($est_g = $resultado_estudiantes_general->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($est_g['cedula']); ?></td>
                                        <td><?php echo htmlspecialchars($est_g['nombre']); ?></td>
                                        <td>Sección <?php echo htmlspecialchars($est_g['seccion']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No hay estudiantes registrados en la Sección <?php echo $seccion_actual; ?>.</p>
                    <?php endif; ?>
                </div>

            <?php elseif ($vista === 'plan_evaluacion'): ?>
                <div class="page-header">
                    <h2>Plan de Evaluación — <?php echo htmlspecialchars($materia_actual); ?> (Sección <?php echo htmlspecialchars($seccion_actual); ?>)</h2>
                </div>
                <div class="grid-layout">
                    <div class="card">
                        <div class="card-title">Plan de Evaluación Vigente</div>
                        <?php if ($resultado_plan && $resultado_plan->num_rows > 0): ?>
                            <?php $plan_actual = $resultado_plan->fetch_assoc(); ?>
                            <div class="resource-item">
                                <div class="icon-box">PLAN</div>
                                <div class="resource-details">
                                    <h4><?php echo htmlspecialchars($plan_actual['titulo']); ?></h4>
                                    <p>Documento oficial con las fechas y ponderaciones de evaluaciones.</p>
                                    <a href="<?php echo htmlspecialchars($plan_actual['enlace']); ?>" target="_blank">📥 Ver / Descargar Plan de Evaluación</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <p>No se ha cargado el plan de evaluación para esta materia y sección todavía.</p>
                        <?php endif; ?>
                    </div>

                    <div class="card">
                        <div class="card-title">Subir Plan de Evaluación</div>
                        <form method="POST" action="panel_admin.php?materia=<?php echo urlencode($materia_actual); ?>&seccion=<?php echo $seccion_actual; ?>&vista=plan_evaluacion" enctype="multipart/form-data">
                            <input type="hidden" name="materia_plan" value="<?php echo htmlspecialchars($materia_actual); ?>">
                            <input type="hidden" name="seccion_plan" value="<?php echo htmlspecialchars($seccion_actual); ?>">
                            <label>Seleccionar Archivo (PDF / Word):</label>
                            <input type="file" name="archivo_plan" accept=".pdf,.doc,.docx" required>
                            <button type="submit" name="subir_plan_evaluacion" class="btn-action">Guardar y Publicar Plan</button>
                        </form>
                    </div>
                </div>

            <?php elseif ($vista === 'horario'): ?>
                <div class="page-header">
                    <h2>Horario Institucional Oficial (PDF) — Sección <?php echo htmlspecialchars($seccion_actual); ?></h2>
                </div>
                <div class="grid-layout">
                    <div class="card">
                        <div class="card-title">Horario Actual - Sección <?php echo htmlspecialchars($seccion_actual); ?></div>
                        <?php if ($resultado_horario && $resultado_horario->num_rows > 0): ?>
                            <?php $horario_actual = $resultado_horario->fetch_assoc(); ?>
                            <div class="resource-item">
                                <div class="icon-box">PDF</div>
                                <div class="resource-details">
                                    <h4>Horario Oficial Institucional - Sección <?php echo $seccion_actual; ?></h4>
                                    <p>Documento emitido por la coordinación académica.</p>
                                    <a href="<?php echo htmlspecialchars($horario_actual['enlace']); ?>" target="_blank">📥 Abrir Documento PDF</a>
                                </div>
                            </div>
                        <?php else: ?>
                            <p>No se ha subido el horario para esta sección todavía.</p>
                        <?php endif; ?>
                    </div>

                    <div class="card">
                        <div class="card-title">Subir Horario</div>
                        <form method="POST" action="panel_admin.php?materia=<?php echo urlencode($materia_actual); ?>&seccion=<?php echo $seccion_actual; ?>&vista=horario" enctype="multipart/form-data">
                            <input type="hidden" name="seccion_horario" value="<?php echo $seccion_actual; ?>">
                            <label>Seleccionar Documento PDF:</label>
                            <input type="file" name="pdf_horario" accept=".pdf" required>
                            <button type="submit" name="subir_horario_pdf" class="btn-action">Cargar Horario PDF</button>
                        </form>
                    </div>
                </div>

            <?php elseif ($vista === 'calificaciones'): ?>
                <div class="page-header">
                    <h2>Registro de Calificaciones por Unidad — <?php echo htmlspecialchars($materia_actual); ?></h2>
                </div>
                <div class="card">
                    <div class="card-title"><?php echo htmlspecialchars($materia_actual); ?> (Sección <?php echo $seccion_actual; ?>)</div>
                    <?php if ($resultado_estudiantes && $resultado_estudiantes->num_rows > 0): ?>
                        <table>
                            <thead>
                                <tr>
                                    <th>Cédula</th>
                                    <th>Estudiante</th>
                                    <th>Unidad 1</th>
                                    <th>Unidad 2</th>
                                    <th>Unidad 3</th>
                                    <th>Unidad 4</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while($est = $resultado_estudiantes->fetch_assoc()): ?>
                                    <?php
                                    $ced_est = $est['cedula'];
                                    $res_n = $conexion->query("SELECT * FROM notas WHERE cedula_estudiante = '$ced_est' AND materia = '$materia_actual' LIMIT 1");
                                    $row_n = ($res_n && $res_n->num_rows > 0) ? $res_n->fetch_assoc() : ['unidad1'=>'0','unidad2'=>'0','unidad3'=>'0','unidad4'=>'0'];
                                    ?>
                                    <tr>
                                        <form method="POST" action="panel_admin.php?materia=<?php echo urlencode($materia_actual); ?>&seccion=<?php echo $seccion_actual; ?>&vista=calificaciones">
                                            <td>
                                                <?php echo htmlspecialchars($est['cedula']); ?>
                                                <input type="hidden" name="cedula" value="<?php echo $est['cedula']; ?>">
                                                <input type="hidden" name="materia_nota" value="<?php echo htmlspecialchars($materia_actual); ?>">
                                                <input type="hidden" name="seccion_nota" value="<?php echo htmlspecialchars($seccion_actual); ?>">
                                            </td>
                                            <td><?php echo htmlspecialchars($est['nombre']); ?></td>
                                            <td><input type="text" name="unidad1" value="<?php echo htmlspecialchars($row_n['unidad1']); ?>" class="input-score"></td>
                                            <td><input type="text" name="unidad2" value="<?php echo htmlspecialchars($row_n['unidad2']); ?>" class="input-score"></td>
                                            <td><input type="text" name="unidad3" value="<?php echo htmlspecialchars($row_n['unidad3']); ?>" class="input-score"></td>
                                            <td><input type="text" name="unidad4" value="<?php echo htmlspecialchars($row_n['unidad4']); ?>" class="input-score"></td>
                                            <td><button type="submit" name="actualizar_notas" class="btn-action" style="padding: 5px 10px; width: auto;">Guardar</button></td>
                                        </form>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No hay estudiantes registrados en la Sección <?php echo $seccion_actual; ?>.</p>
                    <?php endif; ?>
                </div>

            <?php elseif ($vista === 'soporte'): ?>
                <div class="card">
                    <div class="card-title">Soporte Técnico del Sistema</div>
                    <p>¿Tienes problemas con la plataforma? Contacta con el departamento de administración de sistemas del PIU.</p>
                </div>

            <?php else: ?>
                <!-- VISTA MATERIALES Y TAREAS -->
                <div class="page-header">
                    <h2>Materiales y Tareas - <?php echo htmlspecialchars($materia_actual); ?> (Sección <?php echo htmlspecialchars($seccion_actual); ?>)</h2>
                </div>

                <div class="grid-layout">
                    <div>
                        <div class="card">
                            <div class="card-title">Asignación de Profesor a esta Materia</div>
                            <form method="POST" action="panel_admin.php?materia=<?php echo urlencode($materia_actual); ?>&seccion=<?php echo $seccion_actual; ?>&vista=materiales" style="display: flex; gap: 10px;">
                                <input type="hidden" name="materia_target" value="<?php echo htmlspecialchars($materia_actual); ?>">
                                <input type="text" name="nombre_docente_materia" value="<?php echo htmlspecialchars($docente_actual_materia); ?>" required style="margin-bottom:0;">
                                <button type="submit" name="guardar_docente_materia" class="btn-action" style="width: 180px;">Guardar Nombre</button>
                            </form>
                        </div>

                        <div class="card">
                            <div class="card-title">Material de Clase y Actividades Publicadas</div>
                            <?php if ($resultado_archivos && $resultado_archivos->num_rows > 0): ?>
                                <?php while($recurso = $resultado_archivos->fetch_assoc()): ?>
                                    <?php $yt_id = obtenerYouTubeID($recurso['enlace']); ?>
                                    <div class="resource-item" style="display: flex; justify-content: space-between; align-items: flex-start;">
                                        <div style="display: flex; gap: 15px; flex: 1;">
                                            <div class="icon-box <?php echo ($yt_id)?'video':''; ?>">
                                                <?php echo ($yt_id)?'▶':'PDF'; ?>
                                            </div>
                                            <div class="resource-details" style="flex:1;">
                                                <h4><?php echo htmlspecialchars($recurso['titulo']); ?></h4>
                                                <p>Tipo: <?php echo htmlspecialchars($recurso['tipo']); ?></p>
                                                <?php if ($yt_id): ?>
                                                    <div style="margin-top:10px;">
                                                        <iframe width="100%" height="200" src="https://www.youtube.com/embed/<?php echo $yt_id; ?>" frameborder="0" allowfullscreen style="border-radius:6px;"></iframe>
                                                    </div>
                                                <?php else: ?>
                                                    <a href="<?php echo htmlspecialchars($recurso['enlace']); ?>" target="_blank">📥 Ver / Descargar Documento</a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <!-- BOTÓN ELIMINAR RECURSO -->
                                        <div>
                                            <a href="panel_admin.php?materia=<?php echo urlencode($materia_actual); ?>&seccion=<?php echo $seccion_actual; ?>&vista=materiales&eliminar_recurso=<?php echo $recurso['id']; ?>" 
                                               onclick="return confirm('¿Estás seguro de que deseas eliminar este recurso?');" 
                                               style="background: #dc3545; color: white; padding: 6px 12px; border-radius: 6px; text-decoration: none; font-size: 0.85rem; font-weight: bold; display: inline-block;">
                                               🗑️ Eliminar
                                            </a>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p>No hay materiales o videos publicados en esta materia para la Sección <?php echo $seccion_actual; ?> todavía.</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div>
                        <div class="card">
                            <div class="card-title">Crear Nuevo Contenido</div>
                            <form method="POST" action="panel_admin.php?materia=<?php echo urlencode($materia_actual); ?>&seccion=<?php echo $seccion_actual; ?>&vista=materiales" enctype="multipart/form-data">
                                <label>Título del Recurso:</label>
                                <input type="text" name="titulo" placeholder="Ej: Guía de Estudio 1" required>

                                <label>Tipo de Recurso:</label>
                                <select name="tipo" id="tipoSelect" onchange="cambiarTipo()">
                                    <option value="Guía PDF" <?php echo ($tipo_solicitado !== 'youtube') ? 'selected' : ''; ?>>📄 Subir PDF / Guía</option>
                                    <option value="Video Tutorial" <?php echo ($tipo_solicitado === 'youtube') ? 'selected' : ''; ?>>▶️ Añadir Enlace a YouTube</option>
                                </select>

                                <div id="campoEnlace" style="display: <?php echo ($tipo_solicitado === 'youtube') ? 'block' : 'none'; ?>;">
                                    <label>Enlace de YouTube:</label>
                                    <input type="text" name="enlace" placeholder="https://www.youtube.com/watch?v=...">
                                </div>

                                <div id="campoArchivo" style="display: <?php echo ($tipo_solicitado === 'youtube') ? 'none' : 'block'; ?>;">
                                    <label>Seleccionar Archivo:</label>
                                    <input type="file" name="archivo" accept=".pdf,.doc,.docx">
                                </div>

                                <input type="hidden" name="materia_pub" value="<?php echo $materia_actual; ?>">
                                <input type="hidden" name="seccion_recurso" value="<?php echo $seccion_actual; ?>">

                                <button type="submit" name="publicar" class="btn-action">Publicar Contenido</button>
                            </form>
                        </div>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </div>

    <script>
    function toggleSubmenu(e) {
        e.preventDefault();
        var sub = document.getElementById('submenu-cursos');
        sub.style.display = (sub.style.display === 'block') ? 'none' : 'block';
    }

    function cambiarTipo() {
        var select = document.getElementById('tipoSelect').value;
        if (select === 'Video Tutorial') {
            document.getElementById('campoEnlace').style.display = 'block';
            document.getElementById('campoArchivo').style.display = 'none';
        } else {
            document.getElementById('campoEnlace').style.display = 'none';
            document.getElementById('campoArchivo').style.display = 'block';
        }
    }
    function toggleMenu() {
    var sidebar = document.querySelector('.sidebar');
    if (sidebar.style.display === 'block') {
        sidebar.style.display = 'none';
    } else {
        sidebar.style.display = 'block';
    }
}
    </script>
</body>
</html>