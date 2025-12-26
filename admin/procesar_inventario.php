<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima');
$ahora = date('Y-m-d H:i:s');

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// Recibimos los arrays
$stock_sistema = $_POST['stock_sistema']; // Array [id => cantidad]
$stock_real = $_POST['stock_real'];       // Array [id => cantidad]

foreach ($stock_real as $id_producto => $cantidad_real) {
    
    $cantidad_sistema = $stock_sistema[$id_producto];
    $diferencia = $cantidad_real - $cantidad_sistema;

    // Solo hacemos algo si hay diferencia (Sobra o Falta)
    if ($diferencia != 0) {
        
        // 1. Obtener nombre del producto para el Kardex
        $qProd = mysqli_query($conexion, "SELECT nombre FROM productos WHERE id = '$id_producto'");
        $dProd = mysqli_fetch_assoc($qProd);
        $nombre_prod = $dProd['nombre'];

        // 2. Definir tipo de movimiento
        if ($diferencia > 0) {
            // Sobra producto (Entrada por ajuste)
            $tipo = 'entrada';
            $obs = 'Ajuste Inventario (Sobrante)';
            $cant_mov = abs($diferencia);
        } else {
            // Falta producto (Salida por merma/pérdida)
            $tipo = 'salida';
            $obs = 'Ajuste Inventario (Faltante/Pérdida)';
            $cant_mov = abs($diferencia);
        }

        // 3. Actualizar Stock en Tabla Productos
        $sqlUpdate = "UPDATE productos SET stock = '$cantidad_real' WHERE id = '$id_producto'";
        mysqli_query($conexion, $sqlUpdate);

        // 4. Registrar en Kardex
        $sqlKardex = "INSERT INTO kardex (nombre_producto, tipo_movimiento, cantidad, fecha, observacion) 
                      VALUES ('$nombre_prod', '$tipo', '$cant_mov', '$ahora', '$obs')";
        mysqli_query($conexion, $sqlKardex);
    }
}

// Redirigir al reporte para ver los cambios
header("Location: reporte_inventario.php");
?>