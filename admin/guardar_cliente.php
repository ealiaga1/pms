<?php
include '../db.php';

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// 1. CREAR CLIENTE MANUAL
if ($accion == 'crear') {
    $nombre = $_POST['nombre'];
    $tipo_doc = $_POST['tipo_doc'];
    $num_doc = $_POST['num_doc'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];
    $obs = $_POST['observaciones'];

    $sql = "INSERT INTO clientes_manuales (nombre, tipo_doc, num_doc, telefono, email, direccion, observaciones) 
            VALUES ('$nombre', '$tipo_doc', '$num_doc', '$telefono', '$email', '$direccion', '$obs')";
    
    if(mysqli_query($conexion, $sql)) {
        header("Location: clientes.php");
    } else {
        echo "Error: " . mysqli_error($conexion);
    }
}

// 2. ELIMINAR (Solo permitimos borrar los manuales, no los de historial de reservas)
if ($accion == 'eliminar') {
    $id = $_POST['id'];
    mysqli_query($conexion, "DELETE FROM clientes_manuales WHERE id='$id'");
    header("Location: clientes.php");
}
?>