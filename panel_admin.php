<?php
// Conexión y sesión (asegúrate de mantener tu lógica de sesión aquí arriba si ya la tienes)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="admin-body">
    <div class="main-wrapper">
        <div class="content">
            <div class="grid-layout">

                <!-- Tarjeta de Registrar Notas -->
                <div class="card">
                    <div class="card-title">
                        <span style="font-size: 1.8rem;">📊</span>
                        <h4>Registrar Notas</h4>
                        <p>Planilla de evaluaciones</p>
                    </div>
                </div>

                <!-- Asignaturas y Secciones (PIU) -->
                <div class="card">
                    <div class="card-title">Asignaturas y Secciones (PIU)</div>
                    <div class="lista-materias-container">
                        <?php
                        $materias_piu = ['Informática Básica', 'Matemática Inicial', 'Algorítmica y Programación', 'Técnicas de Estudio'];
                        foreach ($materias_piu as $mat) {
                            $es_materia_activa = (isset($materia_actual) && $materia_actual === $mat);
                            $q_a = $conexion->query("SELECT COUNT(*) as total FROM usuarios WHERE rol='estudiante' AND seccion='A'");
                            if ($q_a) {
                                $d_a = $q_a->fetch_assoc();
                                $total_a = $d_a['total'] ?? 0;
                            } else {
                                $total_a = 0;
                            }
                            $porcentaje_a = min(round(($total_a / 40) * 100), 100);
                        }
                        ?>
                    </div>
                </div>

                <!-- Asignatura Asignada -->
                <div class="card">
                    <div class="card-title">Asignatura Asignada</div>
                    <div class="lista-materias-container">
                        <?php
                        $materia_docente = '';
                        if (isset($usr_data['materia'])) {
                            $materia_docente = $usr_data['materia'];
                        } elseif (isset($_SESSION['materia'])) {
                            $materia_docente = $_SESSION['materia'];
                        } else {
                            $materia_docente = 'Informática Básica';
                        }
                        ?>
                    </div>
                </div>

            </div> <!-- Cierra grid-layout -->
        </div> <!-- Cierra content -->
    </div> <!-- Cierra main-wrapper -->

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