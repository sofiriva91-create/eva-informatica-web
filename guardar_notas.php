<?php
session_start();
include 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_estudiante = $_POST['id_estudiante'];
    $seccion = $_POST['seccion'];
    $nota1 = $_POST['nota1'] !== '' ? $_POST['nota1'] : 0;
    $nota2 = $_POST['nota2'] !== '' ? $_POST['nota2'] : 0;
    $nota3 = $_POST['nota3'] !== '' ? $_POST['nota3'] : 0;
    $nota4 = $_POST['nota4'] !== '' ? $_POST['nota4'] : 0;

    $check = $conexion->prepare("SELECT id_nota FROM notas WHERE id_estudiante = ?");
    $check->bind_param("i", $id_estudiante);
    $check->execute();
    $res = $check->get_result();

    if ($res->num_rows > 0) {
        $update = $conexion->prepare("UPDATE notas SET nota1=?, nota2=?, nota3=?, nota4=? WHERE id_estudiante=?");
        $update->bind_param("ddddi", $nota1, $nota2, $nota3, $nota4, $id_estudiante);
        $update->execute();
    } else {
        $insert = $conexion->prepare("INSERT INTO notas (id_estudiante, nota1, nota2, nota3, nota4) VALUES (?, ?, ?, ?, ?)");
        $insert->bind_param("idddd", $id_estudiante, $nota1, $nota2, $nota3, $nota4);
        $insert->execute();
    }

   header("Location: ../Web/panel_admin.php?seccion=$seccion&vista=estudiantes");
    exit();
}
?>