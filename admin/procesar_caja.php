<?php
session_start();
include '../db.php';

// 1. OBLIGAR ZONA HORARIA LIMA (CRUCIAL)
// Esto asegura que la apertura y cierre coincidan con las ventas
date_default_timezone_set('America/Lima');
$ahora = date('Y-m-d H:i:s'); 

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// Obtener ID del usuario de forma segura
if (isset($_SESSION['user_id'])) {
    $id_usuario = $_SESSION['user_id'];
} else {
    // Si no está en sesión, lo buscamos por el nombre
    $usuario = $_SESSION['usuario'];
    $queryUser = mysqli_query($conexion, "SELECT id FROM usuarios_admin WHERE usuario = '$usuario'");
    $filaUser = mysqli_fetch_assoc($queryUser);
    $id_usuario = $filaUser['id'];
}

// ---------------------------------------------------------
// 2. ABRIR CAJA
// ---------------------------------------------------------
if ($accion == 'abrir') {
    $monto_inicial = $_POST['monto_inicial'];
    
    // Verificar que no tenga una abierta
    $check = mysqli_query($conexion, "SELECT id FROM caja_sesiones WHERE id_usuario = '$id_usuario' AND estado = 'abierta'");
    
    if(mysqli_num_rows($check) == 0) {
        // CORRECCIÓN: Usamos '$ahora' en lugar de NOW()
        $sql = "INSERT INTO caja_sesiones (id_usuario, monto_inicial, fecha_apertura, estado) 
                VALUES ('$id_usuario', '$monto_inicial', '$ahora', 'abierta')";
        
        mysqli_query($conexion, $sql);
    }
}
registrar_auditoria($conexion, 'CAJA APERTURA', "Abrió caja con monto inicial: $monto_inicial");

// ---------------------------------------------------------
// 3. CERRAR CAJA
// ---------------------------------------------------------
if ($accion == 'cerrar') {
    $id_caja = $_POST['id_caja'];
    $monto_final = $_POST['monto_final']; // Lo que contó el usuario
    $total_sistema = $_POST['total_sistema']; // Lo que calculó el sistema
    
    // CORRECCIÓN: Usamos '$ahora' en lugar de NOW()
    $sql = "UPDATE caja_sesiones SET 
            fecha_cierre = '$ahora', 
            monto_final = '$monto_final', 
            total_ventas = '$total_sistema', 
            estado = 'cerrada' 
            WHERE id = '$id_caja'";
            
    mysqli_query($conexion, $sql);
}
registrar_auditoria($conexion, 'CAJA CIERRE', "Cerró caja. Sistema: $total_sistema | Real: $monto_final");

header("Location: caja.php");
?>