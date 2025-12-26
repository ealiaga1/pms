<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima');
$ahora = date('Y-m-d H:i:s'); 

// ---------------------------------------------------------
// 0. SEGURIDAD DE CAJA
// ---------------------------------------------------------
$usuario = $_SESSION['usuario'];
$qUser = mysqli_query($conexion, "SELECT id FROM usuarios_admin WHERE usuario = '$usuario'");
$fUser = mysqli_fetch_assoc($qUser);
$id_usuario = $fUser['id'];

$qCaja = mysqli_query($conexion, "SELECT id FROM caja_sesiones WHERE id_usuario = '$id_usuario' AND estado = 'abierta'");
$tiene_caja = (mysqli_num_rows($qCaja) > 0);

$accion = isset($_REQUEST['accion']) ? $_REQUEST['accion'] : '';

// Bloqueo solo para cobrar o vender
if (($accion == 'venta' || $accion == 'checkout' || $accion == 'agregar_adelanto') && !$tiene_caja) {
    echo "<script>alert('ERROR CRÍTICO: Necesitas una CAJA ABIERTA para realizar cobros o ventas.'); window.location='rack.php';</script>";
    exit();
}

// ---------------------------------------------------------
// 1. CHECK-IN
// ---------------------------------------------------------
if ($accion == 'checkin') {
    $id_hab = $_POST['id_habitacion'];
    $huesped = $_POST['huesped'];
    
    // Datos básicos
    $tipo_doc = $_POST['tipo_doc'];
    $num_doc = $_POST['num_doc'];
    $telefono = isset($_POST['telefono']) ? $_POST['telefono'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $adelanto = $_POST['adelanto'];
    $id_reserva_web = isset($_POST['id_reserva_web']) ? $_POST['id_reserva_web'] : '';

    // Datos MINCETUR
    $nro_personas = $_POST['nro_personas'];
    $procedencia = $_POST['procedencia'];
    $lugar_origen = $_POST['lugar_origen'];
    $motivo = $_POST['motivo_viaje'];

    // Datos Empresa
    $es_empresa = isset($_POST['es_empresa']) ? 1 : 0;
    $ruc_empresa = isset($_POST['ruc_empresa']) ? $_POST['ruc_empresa'] : '';
    $razon_social = isset($_POST['razon_social']) ? $_POST['razon_social'] : '';
    $direccion = isset($_POST['direccion']) ? $_POST['direccion'] : '';

    mysqli_query($conexion, "UPDATE habitaciones SET estado = 'ocupada' WHERE id = $id_hab");
    
    $sql = "INSERT INTO estancias (
                id_habitacion, nombre_huesped, tipo_doc, num_doc, 
                telefono, email, 
                lugar_origen, nro_personas, adelanto, fecha_ingreso,
                es_empresa, ruc_empresa, razon_social, direccion,
                procedencia, motivo_viaje
            ) VALUES (
                '$id_hab', '$huesped', '$tipo_doc', '$num_doc', 
                '$telefono', '$email',
                '$lugar_origen', '$nro_personas', '$adelanto', '$ahora',
                '$es_empresa', '$ruc_empresa', '$razon_social', '$direccion',
                '$procedencia', '$motivo'
            )";
            
    if(!mysqli_query($conexion, $sql)) { echo "Error SQL: " . mysqli_error($conexion); exit; }
    
    if (!empty($id_reserva_web)) {
        mysqli_query($conexion, "UPDATE reservaciones SET estado = 'checkin' WHERE id = '$id_reserva_web'");
    }
    
    header("Location: rack.php");
}

// ---------------------------------------------------------
// 2. VENTA
// ---------------------------------------------------------
if ($accion == 'venta') {
    $id_hab = $_POST['id_habitacion'];
    $producto = $_POST['producto'];
    $monto = $_POST['monto'];

    $query = mysqli_query($conexion, "SELECT id FROM estancias WHERE id_habitacion = '$id_hab' AND estado = 'activa'");
    $estancia = mysqli_fetch_assoc($query);
    $id_estancia = $estancia['id'];
    
    $sql = "INSERT INTO consumos (id_estancia, detalle, monto, fecha) VALUES ('$id_estancia', '$producto', '$monto', '$ahora')";
    mysqli_query($conexion, $sql);
    mysqli_query($conexion, "UPDATE estancias SET total_consumos = total_consumos + $monto WHERE id = $id_estancia");
    
    // Gestión Inventario
    $qProd = mysqli_query($conexion, "SELECT tipo FROM productos WHERE nombre = '$producto'");
    $dProd = mysqli_fetch_assoc($qProd);
    $tipo_producto = isset($dProd['tipo']) ? $dProd['tipo'] : 'producto'; 

    if ($tipo_producto == 'producto') {
        mysqli_query($conexion, "UPDATE productos SET stock = stock - 1 WHERE nombre = '$producto'");
        
        $qHab = mysqli_query($conexion, "SELECT nombre FROM habitaciones WHERE id = '$id_hab'");
        $dHab = mysqli_fetch_assoc($qHab);
        $nombre_hab = $dHab['nombre'];

        $sqlKardex = "INSERT INTO kardex (nombre_producto, tipo_movimiento, cantidad, fecha, observacion) 
                      VALUES ('$producto', 'salida', 1, '$ahora', 'Venta en $nombre_hab')";
        mysqli_query($conexion, $sqlKardex);
    }

    header("Location: rack.php");
}

// ---------------------------------------------------------
// 3. CHECK-OUT (CORREGIDO)
// ---------------------------------------------------------
if ($accion == 'checkout') {
    $id_hab = $_GET['id'];
    $descuento = isset($_GET['descuento']) ? $_GET['descuento'] : 0;

    $query = mysqli_query($conexion, "
        SELECT e.id, e.fecha_ingreso, e.total_consumos, h.precio_noche 
        FROM estancias e 
        JOIN habitaciones h ON e.id_habitacion = h.id 
        WHERE e.id_habitacion = '$id_hab' AND e.estado = 'activa'
    ");
    $estancia = mysqli_fetch_assoc($query);
    
    if($estancia) {
        $inicio = new DateTime($estancia['fecha_ingreso']);
        $fin = new DateTime($ahora);
        $diff = $inicio->diff($fin);
        $dias = $diff->days; 
        if($dias == 0) $dias = 1; 

        $total_hospedaje = $estancia['precio_noche'] * $dias;

        // Cerrar estancia
        $sql = "UPDATE estancias SET 
                estado = 'finalizada', 
                fecha_salida = '$ahora', 
                total_habitacion = '$total_hospedaje',
                descuento = '$descuento'
                WHERE id = " . $estancia['id'];
        mysqli_query($conexion, $sql);
        
        // CORRECCIÓN 1: La habitación pasa a SUCIA (no disponible)
        mysqli_query($conexion, "UPDATE habitaciones SET estado = 'sucia' WHERE id = $id_hab");

        // CORRECCIÓN 2: Auditoría ANTES de redirigir
        registrar_auditoria($conexion, 'COBRO HOTEL', "Check-out habitación ID: $id_hab. Total Hospedaje: $total_hospedaje");
        
        // Redirigir al ticket
        header("Location: ticket_checkout.php?id=" . $estancia['id']);
        exit();
    } else {
        header("Location: rack.php");
    }
}

// ---------------------------------------------------------
// 4. LIMPIEZA TERMINADA
// ---------------------------------------------------------
if ($accion == 'limpiar') {
    $id_hab = $_GET['id'];
    mysqli_query($conexion, "UPDATE habitaciones SET estado = 'disponible' WHERE id = $id_hab");
    header("Location: rack.php");
}

// ---------------------------------------------------------
// 5. REPORTAR AVERÍA
// ---------------------------------------------------------
if ($accion == 'reportar_averia') {
    $id_hab = $_POST['id_habitacion'];
    $descripcion = $_POST['descripcion'];
    $prioridad = $_POST['prioridad'];
    
    mysqli_query($conexion, "UPDATE habitaciones SET estado = 'mantenimiento' WHERE id = '$id_hab'");
    
    $id_usuario = $_SESSION['user_id']; 
    $sql = "INSERT INTO mantenimiento (id_habitacion, descripcion, prioridad, usuario_reporto) 
            VALUES ('$id_hab', '$descripcion', '$prioridad', '$id_usuario')";
    mysqli_query($conexion, $sql);
    
    header("Location: rack.php");
}

// ---------------------------------------------------------
// 6. FINALIZAR MANTENIMIENTO
// ---------------------------------------------------------
if ($accion == 'finalizar_mantenimiento') {
    $id_hab = $_GET['id_habitacion'];
    
    $qInc = mysqli_query($conexion, "SELECT id FROM mantenimiento WHERE id_habitacion = '$id_hab' AND estado = 'pendiente' ORDER BY id DESC LIMIT 1");
    $inc = mysqli_fetch_assoc($qInc);
    
    if ($inc) {
        $ahora = date('Y-m-d H:i:s');
        mysqli_query($conexion, "UPDATE mantenimiento SET estado = 'resuelto', fecha_solucion = '$ahora' WHERE id = " . $inc['id']);
    }
    
    mysqli_query($conexion, "UPDATE habitaciones SET estado = 'sucia' WHERE id = '$id_hab'");
    
    header("Location: rack.php");
}

// ---------------------------------------------------------
// 7. AGREGAR ADELANTO
// ---------------------------------------------------------
if ($accion == 'agregar_adelanto') {
    if(!$tiene_caja) { die("Error: Caja cerrada."); }

    $id_hab = $_POST['id_habitacion'];
    $monto = $_POST['monto_adelanto'];
    $metodo = $_POST['metodo_pago'];

    $qEst = mysqli_query($conexion, "SELECT id FROM estancias WHERE id_habitacion = '$id_hab' AND estado = 'activa'");
    $estancia = mysqli_fetch_assoc($qEst);
    $id_estancia = $estancia['id'];

    $qNom = mysqli_query($conexion, "SELECT nombre FROM habitaciones WHERE id = '$id_hab'");
    $dNom = mysqli_fetch_assoc($qNom);
    $nom_hab = $dNom['nombre'];

    $detalle_pago = "Adelanto/Pago a cuenta: $nom_hab";
    $sqlVenta = "INSERT INTO ventas_directas (detalle, monto, metodo_pago, fecha, id_usuario) 
                 VALUES ('$detalle_pago', '$monto', '$metodo', '$ahora', '$id_usuario')";
    mysqli_query($conexion, $sqlVenta);

    $sqlUpdate = "UPDATE estancias SET adelanto = adelanto + $monto WHERE id = '$id_estancia'";
    mysqli_query($conexion, $sqlUpdate);

    header("Location: rack.php?venta=ok");
}
?>