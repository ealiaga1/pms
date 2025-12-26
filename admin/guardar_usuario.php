<?php
session_start();
include '../db.php';

// Seguridad: Solo admin puede gestionar usuarios
// if ($_SESSION['rol'] != 'admin') { header("Location: panel.php"); exit(); }

$accion = isset($_POST['accion']) ? $_POST['accion'] : '';

// PROCESAR PERMISOS (Array de checkboxes a String separado por comas)
$permisos = isset($_POST['permisos']) ? implode(',', $_POST['permisos']) : '';

// 1. CREAR USUARIO
if ($accion == 'crear') {
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $usuario = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $rol = $_POST['rol'];
    $password = $_POST['password'];
    $pass_hash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios_admin (nombre_completo, usuario, password, rol, permisos) 
            VALUES ('$nombre', '$usuario', '$pass_hash', '$rol', '$permisos')";
    mysqli_query($conexion, $sql);
}
registrar_auditoria($conexion, 'USUARIO NUEVO', "Creó el usuario: $usuario con rol $rol");

// 2. EDITAR USUARIO
if ($accion == 'editar') {
    $id = $_POST['id'];
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $usuario = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $rol = $_POST['rol'];
    $password = $_POST['password'];

    // Lógica de password
    $update_pass = "";
    if (!empty($password)) {
        $pass_hash = password_hash($password, PASSWORD_DEFAULT);
        $update_pass = ", password='$pass_hash'";
    }

    $sql = "UPDATE usuarios_admin SET 
            nombre_completo='$nombre', 
            usuario='$usuario', 
            rol='$rol', 
            permisos='$permisos' 
            $update_pass 
            WHERE id='$id'";
    
    mysqli_query($conexion, $sql);
}

// 3. ELIMINAR USUARIO
if ($accion == 'eliminar') {
    $id = $_POST['id'];
    if($id != 1) { // Proteger al admin principal
        $sql = "DELETE FROM usuarios_admin WHERE id='$id'";
        mysqli_query($conexion, $sql);
    }
}

header("Location: usuarios.php");
?>