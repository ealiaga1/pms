<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima');
$ahora = date('Y-m-d H:i:s');

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// 1. CREAR NUEVO ÍTEM
if ($accion == 'crear') {
    $nombre = $_POST['nombre'];
    $tipo = $_POST['tipo'];
    
    $sql = "INSERT INTO blancos_items (nombre, tipo, stock_actual, stock_sucio) VALUES ('$nombre', '$tipo', 0, 0)";
    mysqli_query($conexion, $sql);
    header("Location: blancos.php");
}

// 2. MOVIMIENTOS (INGRESO O SALIDA)
if ($accion == 'movimiento') {
    $id_item = $_POST['id_item'];
    $cantidad = $_POST['cantidad'];
    $tipo_mov = $_POST['tipo_mov']; // 'compra' o 'entrega'
    $observacion = $_POST['observacion'];
    
    // Obtenemos datos del ítem para saber si es Lenceria
    $qItem = mysqli_query($conexion, "SELECT * FROM blancos_items WHERE id='$id_item'");
    $item = mysqli_fetch_assoc($qItem);
    
    // --- CASO A: INGRESO (FLECHA ARRIBA) ---
    if ($tipo_mov == 'compra') {
        $subtipo = isset($_POST['subtipo_mov']) ? $_POST['subtipo_mov'] : '';

        if ($subtipo == 'Retorno Lavandería' && $item['tipo'] == 'Lenceria') {
            // Saca de sucio -> Mete a limpio (Recuperación)
            $sql = "UPDATE blancos_items SET stock_actual = stock_actual + $cantidad, stock_sucio = stock_sucio - $cantidad WHERE id='$id_item'";
            $obs_log = "Retorno de Lavandería";
        } else {
            // Compra Nueva (Aumenta el patrimonio total)
            $sql = "UPDATE blancos_items SET stock_actual = stock_actual + $cantidad WHERE id='$id_item'";
            $obs_log = "Compra / Ingreso Nuevo";
        }
    }

    // --- CASO B: ENTREGA / SALIDA (FLECHA DERECHA) ---
    if ($tipo_mov == 'entrega') {
        if ($item['tipo'] == 'Lenceria') {
            // Saca de limpio -> Pasa a sucio/uso (No se pierde, solo se mueve)
            $sql = "UPDATE blancos_items SET stock_actual = stock_actual - $cantidad, stock_sucio = stock_sucio + $cantidad WHERE id='$id_item'";
            $obs_log = "Entrega a Habitación (Pasa a uso/sucio)";
        } else {
            // Amenities / Limpieza: Se gasta y desaparece
            $sql = "UPDATE blancos_items SET stock_actual = stock_actual - $cantidad WHERE id='$id_item'";
            $obs_log = "Consumo / Gasto";
        }
    }

    // Ejecutar actualización de stock
    mysqli_query($conexion, $sql); 

    // --- NUEVO: GUARDAR EN HISTORIAL ---
    // Obtenemos el nombre del usuario actual
    $usuario_log = $_SESSION['usuario']; 
    
    // Insertamos el registro
    $sqlHist = "INSERT INTO blancos_historial (id_item, tipo_movimiento, cantidad, fecha, observacion, usuario) 
                VALUES ('$id_item', '$obs_log', '$cantidad', '$ahora', '$observacion', '$usuario_log')";
    mysqli_query($conexion, $sqlHist);
    // -----------------------------------

    // Registrar en Historial (Opcional, si tienes tabla historial_blancos)
    // $sqlHist = "INSERT INTO historial_blancos ...";
    // mysqli_query($conexion, $sqlHist);

    header("Location: blancos.php");
}
// 3. ELIMINAR ÍTEM
if ($accion == 'eliminar') {
    $id = $_POST['id'];
    
    // Primero borramos el ítem
    $sql = "DELETE FROM blancos_items WHERE id='$id'";
    mysqli_query($conexion, $sql);

    // Opcional: También podrías borrar su historial para limpiar la base de datos
    // mysqli_query($conexion, "DELETE FROM blancos_historial WHERE id_item='$id'");

    header("Location: blancos.php");
}
?>