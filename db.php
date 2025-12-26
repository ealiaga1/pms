<?php
// Configuración de Servidor
$host = "localhost";
$user = "root";   
$pass = "";       
$db   = "tulumayo_db";

// 1. Conectar
$conexion = mysqli_connect($host, $user, $pass, $db);

if (!$conexion) {
    die("Error de conexión: " . mysqli_connect_error());
}

// 2. OBLIGAR A PHP A USAR HORA DE PERÚ
date_default_timezone_set('America/Lima');

// 3. OBLIGAR A MYSQL A USAR HORA DE PERÚ (La Solución Definitiva)
// Esto hace que NOW() y CURRENT_TIMESTAMP sean hora peruana
mysqli_query($conexion, "SET time_zone = '-05:00'");
mysqli_query($conexion, "SET lc_time_names = 'es_PE'");

function registrar_auditoria($conexion, $accion, $detalle) {
    // Intentamos obtener el ID del usuario de la sesión
    $id_usuario = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
    
    // Si no hay sesión (ej: login fallido), intentamos ver si se envió un usuario por POST
    if ($id_usuario == 0 && isset($_POST['usuario'])) {
        // Esto es solo referencial para intentos de login
        $detalle .= " (Intento: " . $_POST['usuario'] . ")";
    }

    // Obtener IP
    $ip = $_SERVER['REMOTE_ADDR'];

    // Limpiar textos
    $acc = mysqli_real_escape_string($conexion, $accion);
    $det = mysqli_real_escape_string($conexion, $detalle);

    $sql = "INSERT INTO auditoria (id_usuario, accion, detalle, ip) VALUES ('$id_usuario', '$acc', '$det', '$ip')";
    mysqli_query($conexion, $sql);
}
?>