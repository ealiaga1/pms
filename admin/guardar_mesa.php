<?php
include '../db.php';

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// 1. CREAR MESA / ESPACIO
if ($accion == 'crear') {
    $nombre = $_POST['nombre']; 
    $zona = $_POST['zona']; // Nuevo campo
    
    $sql = "INSERT INTO mesas (nombre, estado, zona) VALUES ('$nombre', 'libre', '$zona')";
    
    if (mysqli_query($conexion, $sql)) {
        header("Location: restaurante.php");
    } else {
        echo "Error: " . mysqli_error($conexion);
    }
}

// 2. ELIMINAR MESA
if ($accion == 'eliminar') {
    $id = $_POST['id'];

    $check = mysqli_query($conexion, "SELECT estado FROM mesas WHERE id = '$id'");
    $mesa = mysqli_fetch_assoc($check);

    if ($mesa['estado'] == 'libre') {
        $sql = "DELETE FROM mesas WHERE id = '$id'";
        mysqli_query($conexion, $sql);
        header("Location: restaurante.php");
    } else {
        echo "<script>alert('No puedes eliminar un espacio OCUPADO.'); window.location='restaurante.php';</script>";
    }
}
?>