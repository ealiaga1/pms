<?php
include '../db.php';

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// 1. CREAR TEMPORADA
if ($accion == 'crear') {
    $nombre = $_POST['nombre'];
    // Recibimos fechas completas 2024-07-28 pero solo guardamos 07-28
    $inicio_full = $_POST['inicio'];
    $fin_full = $_POST['fin'];

    $inicio = date('m-d', strtotime($inicio_full));
    $fin = date('m-d', strtotime($fin_full));

    $sql = "INSERT INTO temporadas (nombre, inicio, fin) VALUES ('$nombre', '$inicio', '$fin')";
    mysqli_query($conexion, $sql);
}

// 2. ELIMINAR TEMPORADA
if ($accion == 'eliminar') {
    $id = $_POST['id'];
    $sql = "DELETE FROM temporadas WHERE id='$id'";
    mysqli_query($conexion, $sql);
}

header("Location: temporadas.php");
?>