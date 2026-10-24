<?php
session_start();

// Simulación o conexión a base de datos (ajusta según tu archivo de conexión real)
// include 'conexion.php';

// Verificación de sesión del docente y su materia asignada
$nombre_docente = $_SESSION['nombre'] ?? $_usr_data['nombre'] ?? 'Docente';
// La materia se obtiene estrictamente de lo que tiene asignado el profesor al registrarse
$materia_docente = $_SESSION['materia'] ?? $_usr_data['materia'] ?? 'Informática Básica';

// El docente gestiona su materia asignada (no puede cambiar de materia)
$materia_actual = $materia_docente;
$seccion_actual = $_GET['seccion'] ?? 'A';
$vista_actual = $_GET['vista'] ?? 'dashboard';

// Procesamiento de formularios (Notas, Materiales, etc.)
$mensaje_alerta = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['guardar_calificacion'])) {
        $cedula_estudiante = $_POST['cedula'] ?? '';
        $nota = floatval($_POST['nota'] ?? 0);
        $condicion = ($nota >= 12) ? 'Aprobado (>= 12)' : 'Reprobado (< 12)';
        $mensaje_alerta = "Calificación registrada con éxito. Estado del alumno: " . $condicion;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entorno Virtual PIU - Panel Docente</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
    <div class="main-wrapper">
        <!-- Menú Lateral -->
        <div class="sidebar">
            <div class="logo-container">
                <h3>Entorno Virtual PIU</h3>
            </div>
            <ul class="menu-list">
                <li><a href="?vista=dashboard&seccion=<?php echo $seccion_actual; ?>" class="<?php echo ($vista_actual === 'dashboard') ? 'active' : ''; ?>">Inicio</a></li>
                <li><a href="?vista=estudiantes&seccion=<?php echo $seccion_actual; ?>" class="<?php echo ($vista_actual === 'estudiantes') ? 'active' : ''; ?>">Estudiantes</a></li>
                <li><a href="?vista=materiales&seccion=<?php echo $seccion_actual; ?>" class="<?php echo ($vista_actual === 'materiales') ? 'active' : ''; ?>">Materiales y Tareas</a></li>
                <li><a href="?vista=evaluacion&seccion=<?php echo $seccion_actual; ?>" class="<?php echo ($vista_actual === 'evaluacion') ? 'active' : ''; ?>">Plan de Evaluación</a></li>
                <li><a href="?vista=calificaciones&seccion=<?php echo $seccion_actual; ?>" class="<?php echo ($vista_actual === 'calificaciones') ? 'active' : ''; ?>">Calificaciones</a></li>
                <li><a href="?vista=horario&seccion=<?php echo $seccion_actual; ?>" class="<?php echo ($vista_actual === 'horario') ? 'active' : ''; ?>">Horario</a></li>
                <li><a href="?vista=soporte&seccion=<?php echo $seccion_actual; ?>" class="<?php echo ($vista_actual === 'soporte') ? 'active' : ''; ?>">Soporte</a></li>
            </ul>
        </div>

        <div class="content">
            <!-- Barra Superior: Asignatura fija del docente y Selector de Sección A / B -->
            <div class="top-bar">
                <h2><?php echo htmlspecialchars($materia_actual); ?> (Asignatura Asignada)</h2>
                <div class="secciones-selector">
                    <a href="?vista=<?php echo $vista_actual; ?>&seccion=A" class="btn-seccion <?php echo ($seccion_actual === 'A') ? 'active' : ''; ?>">Sección A</a>
                    <a href="?vista=<?php echo $vista_actual; ?>&seccion=B" class="btn-seccion <?php echo ($seccion_actual === 'B') ? 'active' : ''; ?>">Sección B</a>
                </div>
            </div>

            <?php if (!empty($mensaje_alerta)): ?>
                <div class="alert-success" style="padding: 15px; background: #d4edda; color: #155724; margin-bottom: 20px; border-radius: 5px;">
                    <?php echo htmlspecialchars($mensaje_alerta); ?>
                </div>
            <?php endif; ?>

            <!-- CONTENIDO DINÁMICO SEGÚN LA VISTA SELECCIONADA -->
            <div class="grid-layout">
                <div class="main-dashboard-area">

                    <?php if ($vista_actual === 'dashboard'): ?>
                        <!-- VISTA: INICIO / DASHBOARD -->
                        <div class="welcome-card">
                            <h2>Bienvenid@, <?php echo htmlspecialchars($nombre_docente); ?> 👋</h2>
                            <p>Panel de control principal del aula virtual — Materia: <strong><?php echo htmlspecialchars($materia_actual); ?></strong> | Sección Activa: <strong><?php echo htmlspecialchars($seccion_actual); ?></strong></p>
                        </div>

                        <div class="stats-cards-container">
                            <div class="card-stat">
                                <h4>Estudiantes (Sección <?php echo htmlspecialchars($seccion_actual); ?>)</h4>
                                <p class="stat-number">Ver listado en la pestaña Estudiantes</p>
                            </div>
                            <div class="card-stat">
                                <h4>Recursos Publicados</h4>
                                <p class="stat-number">Guías / PDFs / Videos</p>
                            </div>
                        </div>

                        <!-- Panel de Publicación Directa -->
                        <div class="card">
                            <div class="card-title">Publicar Contenido Directo — Para: <?php echo htmlspecialchars($materia_actual); ?> (Sección <?php echo htmlspecialchars($seccion_actual); ?>)</div>
                            <div class="publish-options-grid">
                                <div class="publish-box">
                                    <h4>Subir PDF / Guía</h4>
                                    <p>Carga material de estudio</p>
                                </div>
                                <div class="publish-box">
                                    <h4>Pegar Link YouTube</h4>
                                    <p>Clases y videotutoriales</p>
                                </div>
                                <div class="publish-box">
                                    <h4>Registrar Notas</h4>
                                    <p>Planilla de evaluaciones</p>
                                </div>
                            </div>
                        </div>

                    <?php elseif ($vista_actual === 'estudiantes'): ?>
                        <!-- VISTA: ESTUDIANTES -->
                        <div class="card">
                            <div class="card-title">Estudiantes Registrados - Sección <?php echo htmlspecialchars($seccion_actual); ?></div>
                            <div class="table-container" style="padding: 20px;">
                                <p>Aquí se visualizan los alumnos inscritos en <strong><?php echo htmlspecialchars($materia_actual); ?></strong> (Sección <?php echo htmlspecialchars($seccion_actual); ?>).</p>
                                <table style="width:100%; margin-top: 15px; border-collapse: collapse;">
                                    <thead>
                                        <tr style="background: #f1f1f1; text-align: left;">
                                            <th style="padding: 10px; border: 1px solid #ddd;">Cédula</th>
                                            <th style="padding: 10px; border: 1px solid #ddd;">Nombre Completo</th>
                                            <th style="padding: 10px; border: 1px solid #ddd;">Materia</th>
                                            <th style="padding: 10px; border: 1px solid #ddd;">Sección</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Ejemplo estático/dinámico de estudiantes de la sección -->
                                        <tr>
                                            <td style="padding: 10px; border: 1px solid #ddd;">V-12345678</td>
                                            <td style="padding: 10px; border: 1px solid #ddd;">Estudiante Ejemplo <?php echo htmlspecialchars($seccion_actual); ?></td>
                                            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($materia_actual); ?></td>
                                            <td style="padding: 10px; border: 1px solid #ddd;"><?php echo htmlspecialchars($seccion_actual); ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    <?php elseif ($vista_actual === 'materiales'): ?>
                        <!-- VISTA: MATERIALES Y TAREAS -->
                        <div class="card">
                            <div class="card-title">Materiales y Tareas - Subir Guías y Enlaces</div>
                            <div style="padding: 20px;">
                                <form action="?vista=materiales&seccion=<?php echo $seccion_actual; ?>" method="POST" enctype="multipart/form-data">
                                    <div style="margin-bottom: 15px;">
                                        <label><strong>Subir PDF / Guía de Estudio:</strong></label><br>
                                        <input type="file" name="archivo_pdf" accept=".pdf" style="margin-top: 5px;">
                                    </div>
                                    <div style="margin-bottom: 15px;">
                                        <label><strong>Pegar Link de YouTube (Clases / Videotutoriales):</strong></label><br>
                                        <input type="url" name="link_youtube" placeholder="https://www.youtube.com/watch?v=..." style="width: 100%; padding: 8px; margin-top: 5px;">
                                    </div>
                                    <button type="submit" class="btn-submit" style="padding: 10px 20px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer;">Publicar Material</button>
                                </form>
                            </div>
                        </div>

                    <?php elseif ($vista_actual === 'evaluacion'): ?>
                        <!-- VISTA: PLAN DE EVALUACIÓN -->
                        <div class="card">
                            <div class="card-title">Plan de Evaluación - <?php echo htmlspecialchars($materia_actual); ?></div>
                            <div style="padding: 20px;">
                                <p>Configura las evaluaciones, porcentajes y fechas de entrega para la Sección <?php echo htmlspecialchars($seccion_actual); ?>.</p>
                            </div>
                        </div>

                    <?php elseif ($vista_actual === 'calificaciones'): ?>
                        <!-- VISTA: CALIFICACIONES (Con lógica de aprobación < 12 o >= 12) -->
                        <div class="card">
                            <div class="card-title">Registro de Calificaciones — Sección <?php echo htmlspecialchars($seccion_actual); ?></div>
                            <div style="padding: 20px;">
                                <p>Asigna la nota definitiva (Escala del 1 al 20. <strong>Nota mínima aprobatoria: 12</strong>).</p>
                                <form action="?vista=calificaciones&seccion=<?php echo $seccion_actual; ?>" method="POST" style="margin-top: 15px;">
                                    <div style="margin-bottom: 15px;">
                                        <label>Cédula del Estudiante:</label><br>
                                        <input type="text" name="cedula" required placeholder="Ej: V-12345678" style="width: 100%; padding: 8px; margin-top: 5px;">
                                    </div>
                                    <div style="margin-bottom: 15px;">
                                        <label>Calificación (0 a 20):</label><br>
                                        <input type="number" name="nota" min="0" max="20" step="0.1" required placeholder="Ej: 14" style="width: 100%; padding: 8px; margin-top: 5px;">
                                    </div>
                                    <input type="hidden" name="guardar_calificacion" value="1">
                                    <button type="submit" style="padding: 10px 20px; background: #28a745; color: white; border: none; border-radius: 4px; cursor: pointer;">Guardar y Evaluar Nota</button>
                                </form>
                            </div>
                        </div>

                    <?php elseif ($vista_actual === 'horario'): ?>
                        <!-- VISTA: HORARIO -->
                        <div class="card">
                            <div class="card-title">Horario de Clases</div>
                            <div style="padding: 20px;">
                                <p>Horarios asignados para la materia <?php echo htmlspecialchars($materia_actual); ?>.</p>
                            </div>
                        </div>

                    <?php elseif ($vista_actual === 'soporte'): ?>
                        <!-- VISTA: SOPORTE -->
                        <div class="card">
                            <div class="card-title">Soporte Técnico</div>
                            <div style="padding: 20px;">
                                <p>Asistencia técnica del Entorno Virtual PIU.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                </div>

                <!-- Columna Lateral derecha: Materia Asignada Fija (Bloqueada al docente) -->
                <div class="sidebar-materias">
                    <div class="card">
                        <div class="card-title">Tu Materia Asignada</div>
                        <div class="lista-materias-container" style="padding: 15px;">
                            <div class="materia-item active" style="padding: 10px; background: #e9ecef; border-radius: 4px;">
                                <strong><?php echo htmlspecialchars($materia_docente); ?></strong>
                                <p style="font-size: 0.85rem; color: #666; margin-top: 5px;">(Asignada en tu registro)</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSubmenu(e) {
            e.preventDefault();
            var sub = document.getElementById('submenu-cursos');
            if (sub) {
                sub.style.display = (sub.style.display === 'block') ? 'none' : 'block';
            }
        }
    </script>
</body>
</html>