<?php
session_start();
include'../backend/ver_sesion.php';
include'../backend/conexion.php';

if (!isset($_SESSION['nombre']) || $_SESSION['rol'] !== 'estudiante') {
    header("Location: login.php");
    exit();
}

$seccion_estudiante = $_SESSION['seccion'] ?? 'A';


$stmt = $conexion->prepare("SELECT * FROM horarios WHERE seccion = ? ORDER BY id_horario DESC LIMIT 1");
$stmt->bind_param("s", $seccion_estudiante);
$stmt->execute();
$resultado = $stmt->get_result();
$horario = ($resultado && $resultado->num_rows > 0) ? $resultado->fetch_assoc() : null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Horario Institucional</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8fafc; color: #1e293b; padding: 30px; }
        .container { max-width: 800px; margin: 0 auto; background: #fff; padding: 30px; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .btn-volver { display: inline-block; margin-bottom: 20px; color: #2563eb; text-decoration: none; font-weight: 600; }
        .btn-descargar { display: inline-block; background: #2563eb; color: #fff; padding: 10px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; margin-top: 15px; }
        iframe { width: 100%; height: 500px; border: 1px solid #cbd5e1; border-radius: 8px; margin-top: 20px; }
        .no-horario { color: #dc2626; font-weight: 600; text-align: center; padding: 40px; }
    </style>
</head>
<body>

<div class="container">
    <a href="panel_estudiante.php" class="btn-volver">← Volver a mi panel</a>
    <h2>Horario Oficial - Sección <?php echo htmlspecialchars($seccion_estudiante); ?></h2>
    <hr style="border: 0; border-top: 1px solid #e2e8f0; margin-bottom: 20px;">

    <?php if ($horario): ?>
        <p>Aquí tienes disponible el horario oficial institucional correspondiente a tu sección:</p>
        <a href="<?php echo htmlspecialchars($horario['ruta_archivo']); ?>" target="_blank" class="btn-descargar">📥 Descargar / Ver Archivo en Grande</a>
        
        
        <iframe src="<?php echo htmlspecialchars($horario['ruta_archivo']); ?>"></iframe>
    <?php else: ?>
        <div class="no-horario">
            <p>La coordinación aún no ha publicado el horario oficial para la Sección <?php echo htmlspecialchars($seccion_estudiante); ?>.</p>
        </div>
    <?php endif; ?>
</div>

</body>
</html>