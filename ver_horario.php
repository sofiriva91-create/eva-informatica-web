<?php
session_start();
include('conexion.php');

if (!isset($_SESSION['nombre']) || $_SESSION['rol'] != 'estudiante') {
    header("Location: login.php");
    exit();
}

$seccion_estudiante = $_SESSION['seccion'] ?? 'A';

// Buscar el archivo de tipo 'Horario Oficial' subido para su sección
$sql_horario = "SELECT * FROM archivos WHERE tipo = 'Horario Oficial' AND seccion = '$seccion_estudiante' LIMIT 1";
$resultado = $conexion->query($sql_horario);
$horario = ($resultado && $resultado->num_rows > 0) ? $resultado->fetch_assoc() : null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Horario Institucional</title>
</head>
<body>
    <h2>Horario Oficial - Sección <?php echo htmlspecialchars($seccion_estudiante); ?></h2>
    <p><a href="panel_estudiante.php">⬅ Volver a mi Panel</a> | <a href="logout.php">Cerrar Sesión</a></p>
    <hr>

    <?php if ($horario): ?>
        <p><strong><?php echo htmlspecialchars($horario['titulo']); ?></strong></p>
        <p>Haz clic en el siguiente enlace para ver o descargar el horario oficial emitido por la institución:</p>
        <p><a href="<?php echo htmlspecialchars($horario['enlace']); ?>" target="_blank" style="font-size: 18px; font-weight: bold;">📥 Abrir Horario Oficial en PDF</a></p>
        
        <br>
        <!-- Opcional: Mostrarlo directamente en pantalla si es PDF -->
        <iframe src="<?php echo htmlspecialchars($horario['enlace']); ?>" width="100%" height="500px"></iframe>
    <?php else: ?>
        <p>La coordinación aún no ha publicado el horario oficial en PDF para tu sección.</p>
    <?php endif; ?>
</body>
</html>