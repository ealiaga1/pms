<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima');
$ahora = date('Y-m-d H:i:s');

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// Obtener ID usuario
$usuario = $_SESSION['usuario'];
$qUser = mysqli_query($conexion, "SELECT id FROM usuarios_admin WHERE usuario = '$usuario'");
$fUser = mysqli_fetch_assoc($qUser);
$id_usuario = $fUser['id'];

// Recibir datos
$descripcion = $_POST['descripcion'];
$monto = $_POST['monto'];

// Guardar Gasto
$sql = "INSERT INTO gastos (descripcion, monto, fecha, id_usuario) 
        VALUES ('$descripcion', '$monto', '$ahora', '$id_usuario')";

if(mysqli_query($conexion, $sql)) {
    // Volver a la caja con mensaje de éxito (opcional)
    header("Location: caja.php");
} else {
    echo "Error al registrar gasto: " . mysqli_error($conexion);
}
?>