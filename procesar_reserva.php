<?php
include 'db.php';

if (isset($_POST['btn_reservar'])) {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $llegada = $_POST['llegada'];
    $salida = $_POST['salida'];
    $habitacion = $_POST['habitacion'];

    $sql = "INSERT INTO reservaciones (nombre_cliente, email, telefono, fecha_llegada, fecha_salida, tipo_habitacion) 
            VALUES ('$nombre', '$email', '$telefono', '$llegada', '$salida', '$habitacion')";

    if (mysqli_query($conexion, $sql)) {
        echo "<script>alert('¡Reserva enviada con éxito! Nos pondremos en contacto pronto.'); window.location='index.php';</script>";
    } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conexion);
    }
}
?>