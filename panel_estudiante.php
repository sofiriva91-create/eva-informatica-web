<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('conexion.php');
// Obtener datos del estudiante actual para evitar errores
$cedula_estudiante = '';
$seccion_estudiante = 'A';
$nombre_estudiante = $_SESSION['nombre'] ?? '';

$res_user = $conexion->query("SELECT * FROM usuarios WHERE nombre = '$nombre_estudiante' AND rol = 'estudiante' LIMIT 1");
if ($res_user && $res_user->num_rows > 0) {
    $usr_data = $res_user->fetch_assoc();
    $cedula_estudiante = $usr_data['cedula'] ?? '';
    $seccion_estudiante = $usr_data['seccion'] ?? 'A';
}
// 1. Consultar las materias de la base de datos de forma segura
$query_materias = "SELECT DISTINCT materia FROM config_materias WHERE materia IS NOT NULL AND materia != ''";
$res_materias = $conexion->query($query_materias);

$vista = isset($_GET['vista']) ? $_GET['vista'] : 'inicio';

// 2. Capturar la materia actual de la URL
$materia_actual = isset($_GET['materia']) ? trim($_GET['materia']) : '';

// Si no hay materia en la URL y no estamos en inicio, tomamos la primera disponible
if (empty($materia_actual) && $vista !== 'inicio' && $vista !== 'soporte') {
    if ($res_materias && $res_materias->num_rows > 0) {
        $res_materias->data_seek(0); // Reiniciar puntero
        $row_def = $res_materias->fetch_assoc();
        $materia_actual = $row_def['materia'];
    } else {
        $materia_actual = 'Informática Básica';
    }
}
function obtenerYouTubeID($url) {
    preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $url, $match);
    return isset($match[1]) ? $match[1] : false;
}

$res_doc = $conexion->query("SELECT docente_asignado FROM config_materias WHERE materia = '$materia_actual' LIMIT 1");
$row_doc = ($res_doc && $res_doc->num_rows > 0) ? $res_doc->fetch_assoc() : ['docente_asignado' => 'Por asignar'];
$docente_asignado = $row_doc['docente_asignado'];

$res_notas = $conexion->query("SELECT * FROM notas WHERE cedula_estudiante = '$cedula_estudiante' AND materia = '$materia_actual' LIMIT 1");
$mis_notas = ($res_notas && $res_notas->num_rows > 0) ? $res_notas->fetch_assoc() : ['unidad1' => '0', 'unidad2' => '0', 'unidad3' => '0', 'unidad4' => '0'];

if ($vista === 'plan_evaluacion') {
    $resultado_plan = $conexion->query("SELECT * FROM archivos WHERE tipo = 'Plan_Evaluacion' AND materia = '$materia_actual' AND seccion = '$seccion_estudiante'");
} elseif ($vista === 'horario') {
    $resultado_horario = $conexion->query("SELECT * FROM archivos WHERE tipo = 'Horario_Institucional' AND seccion = '$seccion_estudiante'");
} else {
    
    $resultado_archivos = $conexion->query("SELECT * FROM archivos WHERE seccion = '$seccion_estudiante' AND materia = '$materia_actual' AND tipo != 'Horario_Institucional' AND tipo != 'Plan_Evaluacion'");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Estudiante - Entorno Virtual</title>
    <link rel="icon" type="image/png" href="logo.png">
    <link rel="stylesheet" href="style.css">

    <style>
        body { overflow-x: hidden; }
    </style>
</head>
<body class="panel-body">

    
    <div class="sidebar">
        <div class="sidebar-brand">
            🖥️ Entorno Virtual PIU
        </div>
        <ul class="sidebar-menu">
    <li>
        <a href="panel_estudiante.php?vista=inicio" class="<?php echo ($vista == 'inicio') ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Inicio
        </a>
    </li>
                <li>
    <a href="#" onclick="toggleSubmenu(event)">
        📚 Mis Materias <span class="arrow" style="font-size: 11px; margin-left: 5px;">▾</span>
    </a>
    <ul id="submenu-cursos" class="submenu" style="display: block;">
        <?php if ($res_materias && $res_materias->num_rows > 0): ?>
            <?php $res_materias->data_seek(0); ?>
            <?php while ($mat = $res_materias->fetch_assoc()): ?>
                <?php 
                    $nombre_materia = $mat['materia']; 
                    $es_activa = ($materia_actual === $nombre_materia) ? 'active' : '';
                ?>
                <li>
                    <a href="panel_estudiante.php?materia=<?php echo urlencode($nombre_materia); ?>&vista=materiales" class="<?php echo $es_activa; ?>">
                        <?php echo htmlspecialchars($nombre_materia); ?>
                    </a>
                </li>
            <?php endwhile; ?>
        <?php else: ?>
            <li><a href="#">No hay materias disponibles</a></li>
        <?php endif; ?>
    </ul>
</li> 
        
            <li><a href="panel_estudiante.php?materia=<?php echo urlencode($materia_actual); ?>&vista=materiales" class="<?php echo ($vista === 'materiales') ? 'active' : ''; ?>">📂 Materiales y Clases</a></li>
            <li><a href="panel_estudiante.php?materia=<?php echo urlencode($materia_actual); ?>&vista=plan_evaluacion" class="<?php echo ($vista === 'plan_evaluacion') ? 'active' : ''; ?>">📊 Plan de Evaluación</a></li>
            <li><a href="panel_estudiante.php?materia=<?php echo urlencode($materia_actual); ?>&vista=calificaciones" class="<?php echo ($vista === 'calificaciones') ? 'active' : ''; ?>">📝 Mis Calificaciones</a></li>
            <li><a href="panel_estudiante.php?materia=<?php echo urlencode($materia_actual); ?>&vista=horario" class="<?php echo ($vista === 'horario') ? 'active' : ''; ?>">📅 Horario de Clases</a></li>
            <li><a href="panel_estudiante.php?vista=soporte" class="<?php echo ($vista === 'soporte') ? 'active' : ''; ?>">🛠️ Soporte</a></li>
        </ul>
    </div>

    
    <div class="main-wrapper">
        
        <div class="header">
            <?php if ($vista === 'inicio') { ?>
    <!-- PEGA TU BLOQUE AQUÍ -->
    
    <!-- Banner de Bienvenida -->
    <div class="welcome-banner">
        <div class="welcome-content">
            <h2>¡Hola, <?php echo htmlspecialchars($nombre_estudiante); ?>! 👋</h2>
            <p>Qué bueno tenerte de vuelta en el Entorno Virtual. Revisa tus materias y mantén el ritmo este semestre.</p>
        </div>
        <div class="welcome-icon">🎓</div>
    </div>

    <!-- Botones de Acceso Rápido -->
    <div class="quick-access-container">
        <a href="panel_estudiante.php?vista=horario" class="quick-card">
            <div class="quick-icon">📅</div>
            <div class="quick-info">
                <h3>Horario de Clases</h3>
                <p>Consulta tus días y horas</p>
            </div>
        </a>

        <a href="panel_estudiante.php?vista=plan_evaluacion" class="quick-card">
            <div class="quick-icon">📋</div>
            <div class="quick-info">
                <h3>Plan de Evaluación</h3>
                <p>Fechas y ponderaciones</p>
            </div>
        </a>

        <a href="panel_estudiante.php?vista=calificaciones" class="quick-card">
            <div class="quick-icon">📊</div>
            <div class="quick-info">
                <h3>Mis Calificaciones</h3>
                <p>Revisa tus notas actuales</p>
            </div>
        </a>
    </div>

    <!-- Frase de Motivación del Día -->
    <div class="daily-quote-card">
        <div class="quote-header">
            <h4>💡 Reflexión del Día</h4>
        </div>
<blockquote>
            "<?php echo htmlspecialchars($frase_del_dia ?? 'La educación es el arma más poderosa que puedes usar para cambiar el mundo.'); ?>"
        </blockquote>
        <?php if (!empty($autor_frase)): ?>
            <cite>— <?php echo htmlspecialchars($autor_frase); ?></cite>
        <?php endif; ?>
    </div>

<?php } ?>
            <!-- Botón de menú desplegable para celulares -->
<button class="menu-toggle" onclick="toggleMenu()">☰ Menú</button>
            <div class="header-title">
                <?php echo htmlspecialchars($materia_actual); ?> — Sección <?php echo htmlspecialchars($seccion_estudiante); ?>
            </div>
            <div class="user-info">
                <div class="user-details">
                    <strong><?php echo htmlspecialchars($_SESSION['nombre'] ?? 'Estudiante'); ?> (Estudiante)</strong>
                    <a href="logout.php" class="logout-link">Cerrar Sesión</a>
                </div>
            </div>
        </div>

        
        <div class="content">

            <div class="page-header">
                <h2><?php echo htmlspecialchars($materia_actual); ?></h2>
                <p>Docente a cargo: <strong><?php echo htmlspecialchars($docente_asignado); ?></strong></p>
            </div>

            <?php if ($vista === 'plan_evaluacion'): ?>
                <div class="card">
                    <div class="card-title">Plan de Evaluación Oficial</div>
                    <?php if ($resultado_plan && $resultado_plan->num_rows > 0): ?>
                        <?php $plan = $resultado_plan->fetch_assoc(); ?>
                        <div class="resource-item">
                            <div class="icon-box">PLAN</div>
                            <div class="resource-details">
                                <h4><?php echo htmlspecialchars($plan['titulo']); ?></h4>
                                <p>Documento de ponderaciones y fechas emitido por el docente.</p>
                                <a href="<?php echo htmlspecialchars($plan['enlace']); ?>" target="_blank">📥 Descargar / Ver Plan de Evaluación</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <p>El docente aún no ha subido el plan de evaluación para esta materia.</p>
                    <?php endif; ?>
                </div>

            <?php elseif ($vista === 'calificaciones'): ?>
                <div class="card">
                    <div class="card-title">Mis Calificaciones — <?php echo htmlspecialchars($materia_actual); ?> (Cédula: <?php echo htmlspecialchars($cedula_estudiante); ?>)</div>
                    <table>
                        <thead>
                            <tr>
                                <th>Evaluación</th>
                                <th>Nota Obtenida</th>
                                <th>Estatus</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Unidad 1</td>
                                <td><strong><?php echo htmlspecialchars($mis_notas['unidad1']); ?> pts</strong></td>
                                <td><?php echo ($mis_notas['unidad1'] >= 10) ? 'Aprobado' : 'Pendiente / Reprobado'; ?></td>
                            </tr>
                            <tr>
                                <td>Unidad 2</td>
                                <td><strong><?php echo htmlspecialchars($mis_notas['unidad2']); ?> pts</strong></td>
                                <td><?php echo ($mis_notas['unidad2'] >= 10) ? 'Aprobado' : 'Pendiente / Reprobado'; ?></td>
                            </tr>
                            <tr>
                                <td>Unidad 3</td>
                                <td><strong><?php echo htmlspecialchars($mis_notas['unidad3']); ?> pts</strong></td>
                                <td><?php echo ($mis_notas['unidad3'] >= 10) ? 'Aprobado' : 'Pendiente / Reprobado'; ?></td>
                            </tr>
                            <tr>
                                <td>Unidad 4</td>
                                <td><strong><?php echo htmlspecialchars($mis_notas['unidad4']); ?> pts</strong></td>
                                <td><?php echo ($mis_notas['unidad4'] >= 10) ? 'Aprobado' : 'Pendiente / Reprobado'; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            <?php elseif ($vista === 'horario'): ?>
                <div class="card">
                    <div class="card-title">Horario Oficial de Clases (Sección <?php echo htmlspecialchars($seccion_estudiante); ?>)</div>
                    <?php if ($resultado_horario && $resultado_horario->num_rows > 0): ?>
                        <?php $horario = $resultado_horario->fetch_assoc(); ?>
                        <div class="resource-item">
                            <div class="icon-box">PDF</div>
                            <div class="resource-details">
                                <h4>Horario Institucional</h4>
                                <p>Documento oficial emitido por la institución.</p>
                                <a href="<?php echo htmlspecialchars($horario['enlace']); ?>" target="_blank">📥 Abrir Documento PDF</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <p>No se ha subido el horario institucional para la Sección <?php echo htmlspecialchars($seccion_estudiante); ?>.</p>
                    <?php endif; ?>
                </div>

            <?php elseif ($vista === 'soporte'): ?>
                <div class="card">
                    <div class="card-title">Soporte Técnico</div>
                    <p>Si tienes problemas para acceder a tus contenidos o visualizar tus notas, contacta con la coordinación académica del PIU.</p>
                </div>

            <?php else: ?>
                
                <div class="card">
                    <div class="card-title">Materiales de Estudio y Clases Publicadas</div>
                    <?php if ($resultado_archivos && $resultado_archivos->num_rows > 0): ?>
                        <?php while($recurso = $resultado_archivos->fetch_assoc()): ?>
                            <?php $yt_id = obtenerYouTubeID($recurso['enlace']); ?>
                            <div class="resource-item">
                                <div class="icon-box <?php echo ($yt_id) ? 'video' : ''; ?>">
                                    <?php echo ($yt_id) ? '▶' : 'PDF'; ?>
                                </div>
                                <div class="resource-details">
                                    <h4><?php echo htmlspecialchars($recurso['titulo']); ?></h4>
                                    <p>Tipo: <?php echo htmlspecialchars($recurso['tipo']); ?></p>
                                    <?php if ($yt_id): ?>
                                        <div>
                                            <iframe width="100%" height="220" src="https://www.youtube.com/embed/<?php echo $yt_id; ?>" frameborder="0" allowfullscreen></iframe>
                                        </div>
                                    <?php else: ?>
                                        <a href="<?php echo htmlspecialchars($recurso['enlace']); ?>" target="_blank">📥 Descargar Documento / Guía</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>No hay guías ni videos cargados para esta materia en la Sección <?php echo htmlspecialchars($seccion_estudiante); ?>.</p>
                    <?php endif; ?>
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
    function toggleMenu() {
    var sidebar = document.querySelector('.sidebar');
    if (sidebar.style.display === 'block') {
        sidebar.style.display = 'none';
    } else {
        sidebar.style.display = 'block';
    }
}
function toggleSubmenu(e) {
    e.preventDefault();
    var submenu = document.getElementById('submenu-cursos');
    if (submenu.style.display === 'none' || submenu.style.display === '') {
        submenu.style.display = 'block';
    } else {
        submenu.style.display = 'none';
    }
}
    </script>
</body>
</html>