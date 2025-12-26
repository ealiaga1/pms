<?php
session_start();
include '../db.php';

// Si ya está logueado, mandar directo al panel
if (isset($_SESSION['usuario'])) {
    header("Location: panel.php");
    exit();
}
registrar_auditoria($conexion, 'LOGIN', 'Ingreso exitoso al sistema');

if (isset($_POST['btn_login'])) {
    $usuario = mysqli_real_escape_string($conexion, $_POST['usuario']);
    $password = $_POST['password'];

    // 1. Buscamos el usuario
    $sql = "SELECT * FROM usuarios_admin WHERE usuario = '$usuario'";
    $resultado = mysqli_query($conexion, $sql);

    if (mysqli_num_rows($resultado) == 1) {
        $datos = mysqli_fetch_assoc($resultado);
        
        // 2. Verificamos la contraseña (Compatible con Encriptación)
        // Nota: Si aún usas claves sin encriptar (12345), esto fallará hasta que corras el script de encriptación.
        // Si necesitas entrar urgente con clave simple, usa temporalmente: if ($password == $datos['password']) {
        if (password_verify($password, $datos['password'])) {
            
            // 3. Crear variables de sesión completas (Necesarias para la Caja)
            $_SESSION['usuario'] = $datos['usuario'];
            $_SESSION['user_id'] = $datos['id']; 
            $_SESSION['rol'] = $datos['rol'];
            $_SESSION['nombre_completo'] = $datos['nombre_completo'];
            // NUEVA LÍNEA: Guardar permisos en la sesión
    $_SESSION['permisos'] = explode(',', $datos['permisos']); // Lo convertimos en un Array

            header("Location: panel.php");
            exit();
        } else {
            $error = "Contraseña incorrecta.";
        }
    } else {
        $error = "El usuario no existe.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Intranet | Tulumayo Lodge</title>
    <!-- Fuentes y Estilos -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* Reset y Base */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Montserrat', sans-serif; }
        
        body {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-image: url('../img/portada1.jpg'); /* Fondo de Selva */
            background-size: cover;
            background-position: center;
            position: relative;
        }

        /* Capa oscura sobre la foto para que se lea bien */
        body::before {
            content: '';
            position: absolute;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(0, 0, 0, 0.6); 
            z-index: 1;
        }

        /* Contenedor del Login */
        .login-card {
            background: rgba(255, 255, 255, 0.95); /* Blanco casi sólido */
            width: 100%;
            max-width: 400px;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.5);
            text-align: center;
            position: relative;
            z-index: 2; /* Encima de la capa oscura */
            backdrop-filter: blur(5px); /* Efecto vidrio sutil */
        }

        /* Logo */
        .logo-img {
            width: 120px;
            margin-bottom: 10px;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }

        h2 {
            color: #2E5C38; /* Verde corporativo */
            font-size: 1.5rem;
            margin-bottom: 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* Inputs */
        .input-group {
            position: relative;
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #888;
        }

        .input-group input {
            width: 100%;
            padding: 15px 15px 15px 45px; /* Espacio para el icono */
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95rem;
            outline: none;
            transition: 0.3s;
            background: #f9f9f9;
        }

        .input-group input:focus {
            border-color: #2E5C38;
            background: white;
            box-shadow: 0 0 0 3px rgba(46, 92, 56, 0.1);
        }

        /* Botón */
        .btn-login {
            background-color: #2E5C38;
            color: white;
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: bold;
            cursor: pointer;
            transition: 0.3s;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        .btn-login:hover {
            background-color: #244a2d;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 92, 56, 0.3);
        }

        /* Mensaje de Error */
        .error-msg {
            background-color: #ffebee;
            color: #c62828;
            padding: 12px;
            border-radius: 8px;
            font-size: 0.9rem;
            margin-bottom: 20px;
            border: 1px solid #ffcdd2;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Links */
        .footer-links {
            margin-top: 25px;
            font-size: 0.85rem;
        }
        .footer-links a {
            color: #666;
            text-decoration: none;
            transition: 0.3s;
        }
        .footer-links a:hover {
            color: #2E5C38;
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="login-card">
        <!-- Logo del Hotel -->
        <img src="../img/logo.png" alt="Tulumayo Logo" class="logo-img">
        
        <h2>Acceso Intranet</h2>

        <?php if(isset($error)): ?>
            <div class="error-msg">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="input-group">
                <i class="fas fa-user"></i>
                <input type="text" name="usuario" placeholder="Usuario / ID" required autocomplete="off">
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Contraseña" required>
            </div>

            <button type="submit" name="btn_login" class="btn-login">
                Iniciar Sesión
            </button>
        </form>

        <div class="footer-links">
            <a href="../index.php"><i class="fas fa-arrow-left"></i> Volver al sitio web</a>
        </div>
        
        <p style="margin-top: 30px; font-size: 0.7rem; color: #aaa;">
            Sistema de Gestión Hotelera v2.0
        </p>
    </div>

</body>
</html>