<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima');
$ahora = date('Y-m-d H:i:s');

// Verificar Sesión y Caja
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }
$usuario_actual = $_SESSION['usuario']; // Usamos esto como nombre del mozo

// Verificar ID Usuario para Caja
$qUser = mysqli_query($conexion, "SELECT id FROM usuarios_admin WHERE usuario = '$usuario_actual'");
$fUser = mysqli_fetch_assoc($qUser);
$id_usuario = $fUser['id'];

// Verificar si hay caja abierta (Necesaria para cobrar, no para pedir)
$qCaja = mysqli_query($conexion, "SELECT id FROM caja_sesiones WHERE id_usuario = '$id_usuario' AND estado = 'abierta'");
$tiene_caja = (mysqli_num_rows($qCaja) > 0);

$accion = isset($_REQUEST['accion']) ? $_REQUEST['accion'] : '';

// 1. ABRIR MESA
if ($accion == 'abrir') {
    $id_mesa = $_POST['id_mesa'];
    
    // Cambiar estado mesa
    mysqli_query($conexion, "UPDATE mesas SET estado = 'ocupada' WHERE id = $id_mesa");
    
    // Crear pedido
    $sql = "INSERT INTO pedidos_restaurante (id_mesa, mozo, fecha_apertura) VALUES ('$id_mesa', '$usuario_actual', '$ahora')";
    mysqli_query($conexion, $sql);
    
    header("Location: restaurante.php");
}

// 2. AGREGAR PLATO/PRODUCTO
if ($accion == 'agregar_item') {
    $id_mesa = $_POST['id_mesa'];
    $id_producto = $_POST['id_producto'];
    $cantidad = $_POST['cantidad'];
    $notas = $_POST['notas'];

    // Buscar pedido activo de esta mesa
    $qPed = mysqli_query($conexion, "SELECT id FROM pedidos_restaurante WHERE id_mesa = '$id_mesa' AND estado = 'abierto'");
    $ped = mysqli_fetch_assoc($qPed);
    $id_pedido = $ped['id'];

    // Buscar precio del producto
    $qProd = mysqli_query($conexion, "SELECT precio, tipo, nombre FROM productos WHERE id = '$id_producto'");
    $dProd = mysqli_fetch_assoc($qProd);
    $precio = $dProd['precio'];

    // Insertar detalle
    $sql = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario, notas) 
            VALUES ('$id_pedido', '$id_producto', '$cantidad', '$precio', '$notas')";
    mysqli_query($conexion, $sql);

    // Actualizar total del pedido cabecera
    $total_linea = $precio * $cantidad;
    mysqli_query($conexion, "UPDATE pedidos_restaurante SET total = total + $total_linea WHERE id = $id_pedido");

    // Si es PRODUCTO (no servicio), descontar stock
    if ($dProd['tipo'] == 'producto') {
        mysqli_query($conexion, "UPDATE productos SET stock = stock - $cantidad WHERE id = '$id_producto'");
        // Registrar en Kardex
        $sqlKardex = "INSERT INTO kardex (nombre_producto, tipo_movimiento, cantidad, fecha, observacion) 
                      VALUES ('".$dProd['nombre']."', 'salida', '$cantidad', '$ahora', 'Consumo Restaurante')";
        mysqli_query($conexion, $sqlKardex);
    }

    header("Location: restaurante.php");
}

// 3. COBRAR EN CAJA DIRECTA
if ($accion == 'cobrar_caja') {
    if(!$tiene_caja) { die("Error: Caja cerrada."); }

    $id_mesa = $_POST['id_mesa'];
    $id_pedido = $_POST['id_pedido'];
    $metodo = $_POST['metodo_pago'];
    $total = $_POST['total'];

    // Registrar en Ventas Directas (para que cuadre la caja)
    $sqlVenta = "INSERT INTO ventas_directas (detalle, monto, metodo_pago, fecha, id_usuario) 
                 VALUES ('Consumo Restaurante (Mesa $id_mesa)', '$total', '$metodo', '$ahora', '$id_usuario')";
    mysqli_query($conexion, $sqlVenta);

    // Cerrar Pedido y Liberar Mesa
    mysqli_query($conexion, "UPDATE pedidos_restaurante SET estado = 'cerrado' WHERE id = $id_pedido");
    mysqli_query($conexion, "UPDATE mesas SET estado = 'libre' WHERE id = $id_mesa");

    header("Location: restaurante.php?status=cobrado");
}

// 4. CARGAR A HABITACIÓN (CON DETALLE DE PLATOS)
if ($accion == 'cargar_habitacion') {
    $id_mesa = $_POST['id_mesa'];
    $id_pedido = $_POST['id_pedido'];
    $id_habitacion = $_POST['id_habitacion']; 
    $total = $_POST['total'];

    // 1. Buscar la estancia activa de la habitación destino
    $qEst = mysqli_query($conexion, "SELECT id FROM estancias WHERE id_habitacion = '$id_habitacion' AND estado = 'activa'");
    $estancia = mysqli_fetch_assoc($qEst);
    
    if (!$estancia) { die("Error: La habitación seleccionada no tiene huéspedes activos."); }
    $id_estancia = $estancia['id'];

    // 2. OBTENER EL DETALLE DE LOS PLATOS DEL PEDIDO
    $sqlDetalles = "SELECT d.cantidad, d.precio_unitario, p.nombre 
                    FROM detalle_pedido d 
                    JOIN productos p ON d.id_producto = p.id 
                    WHERE d.id_pedido = '$id_pedido'";
    $resDetalles = mysqli_query($conexion, $sqlDetalles);

    // 3. PASAR CADA PLATO A LA CUENTA DE LA HABITACIÓN
    while ($item = mysqli_fetch_assoc($resDetalles)) {
        $subtotal_plato = $item['cantidad'] * $item['precio_unitario'];
        
        // Creamos el texto: "Rest: 2 x Ceviche"
        $detalle_texto = "Rest: " . $item['cantidad'] . " x " . $item['nombre'];
        
        $sqlConsumo = "INSERT INTO consumos (id_estancia, detalle, monto, fecha) 
                       VALUES ('$id_estancia', '$detalle_texto', '$subtotal_plato', '$ahora')";
        mysqli_query($conexion, $sqlConsumo);
    }

    // 4. ACTUALIZAR EL TOTAL DE LA HABITACIÓN
    mysqli_query($conexion, "UPDATE estancias SET total_consumos = total_consumos + $total WHERE id = $id_estancia");

    // 5. CERRAR PEDIDO Y LIBERAR MESA
    mysqli_query($conexion, "UPDATE pedidos_restaurante SET estado = 'cerrado' WHERE id = $id_pedido");
    mysqli_query($conexion, "UPDATE mesas SET estado = 'libre' WHERE id = $id_mesa");

    header("Location: restaurante.php?status=cobrado");
}
?>