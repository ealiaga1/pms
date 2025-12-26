<?php
include '../db.php';
date_default_timezone_set('America/Lima');
$ahora = date('Y-m-d H:i:s');

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// 1. CREAR
if ($accion == 'crear') {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $tipo   = $_POST['tipo']; // Nuevo campo
    
    // Si es servicio, el stock no importa, pero ponemos 0 o 999 para que no de error
    $stock  = ($tipo == 'servicio') ? 999 : $_POST['stock'];

    $sql = "INSERT INTO productos (nombre, precio, stock, tipo) VALUES ('$nombre', '$precio', '$stock', '$tipo')";
    mysqli_query($conexion, $sql);

    // Kardex solo si es producto
    if($tipo == 'producto'){
        mysqli_query($conexion, "INSERT INTO kardex (nombre_producto, tipo_movimiento, cantidad, fecha, observacion) VALUES ('$nombre', 'entrada', '$stock', '$ahora', 'Inventario Inicial')");
    }
}

// 2. EDITAR
if ($accion == 'editar') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $tipo   = $_POST['tipo']; // Nuevo campo
    $nuevo_stock = ($tipo == 'servicio') ? 999 : $_POST['stock'];

    // Calculamos diferencia para Kardex (Solo productos)
    if($tipo == 'producto'){
        $qAnt = mysqli_query($conexion, "SELECT stock FROM productos WHERE id='$id'");
        $dAnt = mysqli_fetch_assoc($qAnt);
        $stock_anterior = $dAnt['stock'];
        $diferencia = $nuevo_stock - $stock_anterior;
        
        if ($diferencia > 0) {
            mysqli_query($conexion, "INSERT INTO kardex (nombre_producto, tipo_movimiento, cantidad, fecha, observacion) VALUES ('$nombre', 'entrada', '$diferencia', '$ahora', 'Reposición')");
        } elseif ($diferencia < 0) {
            $dif_positiva = abs($diferencia);
            mysqli_query($conexion, "INSERT INTO kardex (nombre_producto, tipo_movimiento, cantidad, fecha, observacion) VALUES ('$nombre', 'salida', '$dif_positiva', '$ahora', 'Ajuste')");
        }
    }

    $sql = "UPDATE productos SET nombre='$nombre', precio='$precio', stock='$nuevo_stock', tipo='$tipo' WHERE id='$id'";
    mysqli_query($conexion, $sql);
}

// 3. ELIMINAR
if ($accion == 'eliminar') {
    $id = $_POST['id'];
    $sql = "DELETE FROM productos WHERE id='$id'";
    mysqli_query($conexion, $sql);
}

header("Location: productos.php");
?>