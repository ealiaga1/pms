<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima');
$ahora = date('Y-m-d H:i:s');

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }
$id_usuario = $_SESSION['user_id'];

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// 1. CREAR NUEVO INSUMO
if ($accion == 'crear') {
    $nombre = $_POST['nombre'];
    $unidad = $_POST['unidad'];
    $minimo = $_POST['minimo'];
    
    $sql = "INSERT INTO cocina_insumos (nombre, unidad, stock_actual, stock_minimo) 
            VALUES ('$nombre', '$unidad', 0, '$minimo')"; // Empieza en 0
    mysqli_query($conexion, $sql);
}

// 2. REGISTRAR MOVIMIENTO (COMPRA O CONSUMO)
if ($accion == 'movimiento') {
    $id_insumo = $_POST['id_insumo'];
    $tipo = $_POST['tipo']; // 'compra' o 'consumo'
    $cantidad = $_POST['cantidad'];
    $obs = $_POST['observacion'];

    // Validar stock negativo
    if ($tipo == 'consumo') {
        $qStock = mysqli_query($conexion, "SELECT stock_actual FROM cocina_insumos WHERE id='$id_insumo'");
        $dStock = mysqli_fetch_assoc($qStock);
        if ($dStock['stock_actual'] < $cantidad) {
            echo "<script>alert('Error: No hay suficiente stock para esta salida.'); window.location='cocina.php';</script>";
            exit();
        }
    }

    // Registrar en Kardex Cocina
    $sqlKardex = "INSERT INTO cocina_kardex (id_insumo, tipo, cantidad, fecha, usuario_id, observacion) 
                  VALUES ('$id_insumo', '$tipo', '$cantidad', '$ahora', '$id_usuario', '$obs')";
    mysqli_query($conexion, $sqlKardex);

    // Actualizar Stock Principal
    if ($tipo == 'compra') {
        $sqlUpdate = "UPDATE cocina_insumos SET stock_actual = stock_actual + $cantidad WHERE id = '$id_insumo'";
    } else {
        $sqlUpdate = "UPDATE cocina_insumos SET stock_actual = stock_actual - $cantidad WHERE id = '$id_insumo'";
    }
    mysqli_query($conexion, $sqlUpdate);
}

header("Location: cocina.php");
?>