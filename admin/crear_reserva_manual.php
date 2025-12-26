<?php
include '../db.php';
date_default_timezone_set('America/Lima');

if (isset($_POST['nombre'])) {
    $nombre = $_POST['nombre'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email']; // Puede estar vacío
    $llegada = $_POST['llegada'];
    $salida = $_POST['salida'];
    $habitacion = $_POST['habitacion'];
    
    // Fecha actual para el registro
    $fecha_solicitud = date('Y-m-d H:i:s');

    $sql = "INSERT INTO reservaciones (nombre_cliente, email, telefono, fecha_llegada, fecha_salida, tipo_habitacion, fecha_solicitud, estado) 
            VALUES ('$nombre', '$email', '$telefono', '$llegada', '$salida', '$habitacion', '$fecha_solicitud', 'pendiente')";

    if (mysqli_query($conexion, $sql)) {
        echo "ok";
    } else {
        echo "error: " . mysqli_error($conexion);
    }
}
?>