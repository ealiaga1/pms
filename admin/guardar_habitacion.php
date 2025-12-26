<?php
include '../db.php';

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// 1. CREAR HABITACIÓN
if ($accion == 'crear') {
    $nombre = $_POST['nombre'];
    $tipo = $_POST['tipo'];
    $capacidad = $_POST['capacidad'];
    $precio = $_POST['precio'];      // Precio Baja
    $precio_alta = $_POST['precio_alta']; // Precio Alta

    // Por defecto se crea como 'disponible'
    $sql = "INSERT INTO habitaciones (nombre, tipo, capacidad, precio_noche, precio_alta, estado) 
            VALUES ('$nombre', '$tipo', '$capacidad', '$precio', '$precio_alta', 'disponible')";
    
    if(!mysqli_query($conexion, $sql)) {
        echo "Error al crear: " . mysqli_error($conexion);
        exit;
    }
}

// 2. EDITAR HABITACIÓN INDIVIDUAL
if ($accion == 'editar') {
    $id = $_POST['id'];
    $nombre = $_POST['nombre'];
    $tipo = $_POST['tipo'];
    $capacidad = $_POST['capacidad'];
    $precio = $_POST['precio'];
    $precio_alta = $_POST['precio_alta'];

    $sql = "UPDATE habitaciones SET 
            nombre='$nombre', 
            tipo='$tipo', 
            capacidad='$capacidad', 
            precio_noche='$precio',
            precio_alta='$precio_alta'
            WHERE id='$id'";
            
    if(!mysqli_query($conexion, $sql)) {
        echo "Error al editar: " . mysqli_error($conexion);
        exit;
    }
}

// ---------------------------------------------------------
// 3. ACTUALIZAR PRECIOS EN BLOQUE (ESTA ES LA PARTE QUE FALTABA O FALLABA)
// ---------------------------------------------------------
if ($accion == 'actualizar_precios_lote') {
    // Recibimos los datos del formulario modal
    $tipo_seleccionado = $_POST['tipo_lote'];
    $precio_baja_nuevo = $_POST['precio_baja_lote'];
    $precio_alta_nuevo = $_POST['precio_alta_lote'];

    // Validamos que no estén vacíos
    if(!empty($tipo_seleccionado) && is_numeric($precio_baja_nuevo)) {
        
        // Actualiza TODAS las habitaciones que coincidan con el TIPO
        $sql = "UPDATE habitaciones SET 
                precio_noche = '$precio_baja_nuevo', 
                precio_alta = '$precio_alta_nuevo' 
                WHERE tipo = '$tipo_seleccionado'";
                
        if(!mysqli_query($conexion, $sql)) { 
            echo "Error al actualizar lote: " . mysqli_error($conexion); 
            exit; 
        }
    }
}

// 4. ELIMINAR HABITACIÓN
if ($accion == 'eliminar') {
    $id = $_POST['id'];
    
    // Verificar si está ocupada antes de borrar
    $check = mysqli_query($conexion, "SELECT estado FROM habitaciones WHERE id='$id'");
    $fila = mysqli_fetch_assoc($check);
    
    if ($fila['estado'] == 'disponible' || $fila['estado'] == 'sucia') {
        $sql = "DELETE FROM habitaciones WHERE id='$id'";
        mysqli_query($conexion, $sql);
    } else {
        echo "<script>alert('ERROR: No puedes eliminar una habitación OCUPADA. Primero haz Check-out.'); window.location='habitaciones.php';</script>";
        exit;
    }
}

// Volver a la lista
header("Location: habitaciones.php");
?>