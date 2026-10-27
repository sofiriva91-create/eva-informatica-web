<?php
session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $seccion = $_POST['seccion'];
    $titulo = $_POST['titulo'];
    $tipo = $_POST['tipo'];
    $enlace_youtube = $_POST['enlace_youtube'] ?? '';
    
    $ruta_archivo = '';
    if ($tipo == 'pdf' && isset($_FILES['archivo_pdf']) && $_FILES['archivo_pdf']['error'] == 0) {
        $nombre_archivo = time() . '_' . $_FILES['archivo_pdf']['name'];
        $ruta_archivo = 'uploads/' . $nombre_archivo;
        if (!is_dir('uploads')) {
            mkdir('uploads', 0777, true);
        }
        move_uploaded_file($_FILES['archivo_pdf']['tmp_name'], $ruta_archivo);
    }

    $stmt = $conexion->prepare("INSERT INTO archivos (titulo, tipo, ruta_archivo, enlace_youtube, seccion_perteneciente) VALUES (?, ?, ?, ?, ?)");
    if ($stmt) {
        $stmt->bind_param("sssss", $titulo, $tipo, $ruta_archivo, $enlace_youtube, $seccion);
        $stmt->execute();
    }
    
header("Location: ../Web/panel_admin.php?seccion=$seccion&vista=materiales");
    exit();
}
?>