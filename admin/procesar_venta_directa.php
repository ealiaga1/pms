<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima');
$ahora = date('Y-m-d H:i:s');

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// Obtener ID usuario para saber quién vendió
$usuario = $_SESSION['usuario'];
$qUser = mysqli_query($conexion, "SELECT id FROM usuarios_admin WHERE usuario = '$usuario'");
$fUser = mysqli_fetch_assoc($qUser);
$id_usuario = $fUser['id'];

// Recibir datos
$producto = $_POST['producto'];
$monto = $_POST['monto'];
$metodo = $_POST['metodo_pago'];

// 1. GUARDAR LA VENTA DIRECTA (EL DINERO SIEMPRE SE REGISTRA)
$sql = "INSERT INTO ventas_directas (detalle, monto, metodo_pago, fecha, id_usuario) 
        VALUES ('$producto', '$monto', '$metodo', '$ahora', '$id_usuario')";
mysqli_query($conexion, $sql);

// 2. VERIFICAR TIPO DE ITEM (PRODUCTO VS SERVICIO)
// Consultamos a la base de datos qué tipo es lo que acabamos de vender
$qProd = mysqli_query($conexion, "SELECT tipo FROM productos WHERE nombre = '$producto'");
$dProd = mysqli_fetch_assoc($qProd);

// Si no encuentra el tipo (por si la columna es nueva), asumimos que es producto por seguridad
$tipo_producto = isset($dProd['tipo']) ? $dProd['tipo'] : 'producto';

// 3. SI ES PRODUCTO FÍSICO -> DESCONTAMOS STOCK Y KARDEX
if ($tipo_producto == 'producto') {
    
    // Descontar Stock
    $sqlStock = "UPDATE productos SET stock = stock - 1 WHERE nombre = '$producto'";
    mysqli_query($conexion, $sqlStock);

    // Registrar en Kardex
    $sqlKardex = "INSERT INTO kardex (nombre_producto, tipo_movimiento, cantidad, fecha, observacion) 
                  VALUES ('$producto', 'salida', 1, '$ahora', 'Venta Mostrador ($metodo)')";
    mysqli_query($conexion, $sqlKardex);
}

// Volver al Rack
header("Location: rack.php?venta=ok");
?>