<?php
session_start();
include '../db.php';

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

$accion = isset($_REQUEST['accion']) ? $_REQUEST['accion'] : '';
$id_hab = isset($_REQUEST['id_habitacion']) ? $_REQUEST['id_habitacion'] : '';

// 1. AGREGAR ITEM A LA HABITACIÓN
if ($accion == 'agregar') {
    $id_item = $_POST['id_item'];
    $cantidad = $_POST['cantidad'];
    
    // Verificar si ya existe ese item en esa habitación para no duplicar
    $check = mysqli_query($conexion, "SELECT id FROM inventario_cuartos WHERE id_habitacion='$id_hab' AND id_item='$id_item'");
    
    if(mysqli_num_rows($check) > 0) {
        // Si ya existe, sumamos la cantidad
        $sql = "UPDATE inventario_cuartos SET cantidad = cantidad + $cantidad WHERE id_habitacion='$id_hab' AND id_item='$id_item'";
    } else {
        // Si no existe, lo creamos
        $sql = "INSERT INTO inventario_cuartos (id_habitacion, id_item, cantidad, estado) VALUES ('$id_hab', '$id_item', '$cantidad', 'Bueno')";
    }
    mysqli_query($conexion, $sql);
}

// 2. ACTUALIZAR ESTADO O CANTIDAD (Edición rápida)
if ($accion == 'actualizar') {
    $id_registro = $_POST['id_registro'];
    $estado = $_POST['estado'];
    $cantidad = $_POST['cantidad'];
    
    $sql = "UPDATE inventario_cuartos SET estado='$estado', cantidad='$cantidad' WHERE id='$id_registro'";
    mysqli_query($conexion, $sql);
}

// 3. ELIMINAR ITEM DE LA HABITACIÓN (Si lo sacaron definitivamente)
if ($accion == 'eliminar') {
    $id_registro = $_GET['id_reg'];
    mysqli_query($conexion, "DELETE FROM inventario_cuartos WHERE id='$id_registro'");
}

// 4. CREAR NUEVO TIPO DE ACTIVO (Catálogo)
if ($accion == 'crear_activo') {
    $nombre = $_POST['nombre'];
    $categoria = $_POST['categoria'];
    mysqli_query($conexion, "INSERT INTO activos_items (nombre, categoria) VALUES ('$nombre', '$categoria')");
}

// Redirigir siempre a la misma habitación que estábamos viendo
header("Location: inventario_habitaciones.php?id_hab=" . $id_hab);
?>