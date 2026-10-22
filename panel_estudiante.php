<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('conexion.php');

if (!isset($_SESSION['nombre']) || !isset($_SESSION['rol']) || strtolower($_SESSION['rol']) !== 'estudiante') {
    header("Location: login.php");
    exit();
}

$nombre_estudiante = $_SESSION['nombre'];


$res_user = $conexion->query("SELECT * FROM usuarios WHERE nombre = '$nombre_estudiante' AND rol = 'estudiante' LIMIT 1");
if ($res_user && $res_user->num_rows > 0) {
    $usr_data = $res_user->fetch_assoc();
    $cedula_estudiante = $usr_data['cedula'];
    $seccion_estudiante = $usr_data['seccion'];
} else {
    $cedula_estudiante = $_SESSION['cedula'] ?? '';
    $seccion_estudiante = $_SESSION['seccion'] ?? 'A';
}

$materia_actual = isset($_GET['materia']) ? $_GET['materia'] : 'Informática Básica';
$vista = isset($_GET['vista']) ? $_GET['vista'] : 'materiales';

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
    <title>Panel del Estudiante - Aula Virtual PIU</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="panel-body">

    
    <div class="sidebar">
        <div class="sidebar-brand">
            🖥️ Aula Virtual PIU
        </div>
        <ul class="sidebar-menu">
            <li>
                <a href="#" onclick="toggleSubmenu(event)">📚 Mis Materias ▾</a>
                <ul id="submenu-cursos" class="submenu" style="display: block;">
                    <li><a href="panel_estudiante.php?materia=Informática Básica&vista=materiales" class="<?php echo ($materia_actual === 'Informática Básica') ? 'active' : ''; ?>">Informática Básica</a></li>
                    <li><a href="panel_estudiante.php?materia=Matemática Inicial&vista=materiales" class="<?php echo ($materia_actual === 'Matemática Inicial') ? 'active' : ''; ?>">Matemática Inicial</a></li>
                    <li><a href="panel_estudiante.php?materia=Algorítmica y Programación&vista=materiales" class="<?php echo ($materia_actual === 'Algorítmica y Programación') ? 'active' : ''; ?>">Algorítmica y Programación</a></li>
                    <li><a href="panel_estudiante.php?materia=Técnicas de Estudio&vista=materiales" class="<?php echo ($materia_actual === 'Técnicas de Estudio') ? 'active' : ''; ?>">Técnicas de Estudio</a></li>
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
            <div class="header-title">
                <?php echo htmlspecialchars($materia_actual); ?> — Sección <?php echo htmlspecialchars($seccion_estudiante); ?>
            </div>
            <div class="user-info">
                <div class="user-details">
                    <strong><?php echo htmlspecialchars($_SESSION['nombre']); ?> (Estudiante)</strong>
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
    </script>
</body>
</html>