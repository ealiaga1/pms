<?php
// Lógica simple para procesar el formulario (Simulación)
$mensaje_enviado = false;

if (isset($_POST['btn_mensaje'])) {
    // Aquí podrías guardar en base de datos o enviar mail real
    // Por ahora, solo simulamos éxito
    $nombre = $_POST['nombre'];
    // $sql = "INSERT INTO mensajes ..."; 
    $mensaje_enviado = true;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contacto | Tulumayo Lodge</title>
    <link rel="icon" type="img/x-icon" href="img/favicon.png">
    <link rel="stylesheet" href="estilos.css">
    <!-- Iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <!-- Header / Navbar (Mismo que index) -->
    <?php include 'header.php'; ?>

    <!-- Hero Section (Imagen de fondo) -->
    <div class="contact-hero">
        <!-- Puedes usar otra foto aquí, ej: img/rio.jpg -->
        <img src="img/portada2.jpg" alt="Contacto Selva">
        <h1>Contáctanos</h1>
    </div>

    <!-- Contenido Principal -->
    <div class="contact-grid">
        
        <!-- Columna Izquierda: Información -->
        <div class="contact-info">
            <h3>Estamos para ayudarte</h3>
            <p style="margin-bottom: 30px;">
                ¿Tienes dudas sobre cómo llegar o quieres organizar un evento especial? 
                Escríbenos y te responderemos a la brevedad.
            </p>

            <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <div>
                    <h4>Ubicación</h4>
                    <p>Av. Eduardo Velarde N°360 - Playa Hermosa<br>San Ramon - Chanchamayo</p>
                </div>
            </div>

            <div class="info-item">
                <i class="fas fa-phone-alt"></i>
                <div>
                    <h4>Teléfonos</h4>
                    <p>+51 996 000 091<br>+51 956 220 473</p>
                </div>
            </div>

            <div class="info-item">
                <i class="fas fa-envelope"></i>
                <div>
                    <h4>Email</h4>
                    <p>reservas@tulumayolodge.com<br>administracion@tulumayolodge.com</p>
                </div>
            </div>

            <div class="info-item">
                <i class="fab fa-whatsapp"></i>
                <div>
                    <h4>WhatsApp</h4>
                    <p>Atención 24/7: <a href="#" style="color:var(--verde-mate);">Chat Directo</a></p>
                </div>
            </div>
        </div>

        <!-- Columna Derecha: Formulario -->
        <div class="contact-form-box">
            
            <?php if($mensaje_enviado): ?>
                <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <strong>¡Gracias <?php echo $nombre; ?>!</strong><br>
                    Tu mensaje ha sido enviado correctamente.
                </div>
            <?php endif; ?>

            <form action="" method="POST">
                <h3 style="margin-bottom: 20px;">Envíanos un mensaje</h3>
                
                <input type="text" name="nombre" placeholder="Nombre Completo" required>
                
                <input type="email" name="email" placeholder="Correo Electrónico" required>
                
                <input type="text" name="asunto" placeholder="Asunto (Ej. Reserva Grupo)" required>
                
                <textarea name="mensaje" rows="5" placeholder="¿En qué podemos ayudarte?" required></textarea>
                
                <button type="submit" name="btn_mensaje" class="btn-enviar">ENVIAR MENSAJE</button>
            </form>
        </div>

    </div>

    <!-- Mapa de Google -->
    <div class="map-container">
        <!-- He puesto una ubicación genérica en San Ramón/Chanchamayo. 
             Para poner la exacta del lodge, ve a Google Maps, busca el hotel, da clic en "Compartir" -> "Insertar mapa" y copia el HTML aquí -->
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2444.165078971386!2d-75.35670730160525!3d-11.132583999999984!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x91090d00104975ab%3A0x6c918ab989528fe1!2sHotel%20Tulumayo%20lodge!5e1!3m2!1ses-419!2spe!4v1765127179222!5m2!1ses-419!2spe" width="600" height="500" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>

    <!-- Footer -->
    <?php include 'footer.php'; ?>

<!-- BOTONES FLOTANTES (REDES SOCIALES) -->
    <div class="floating-container">
        
        <!-- Botón Facebook -->
        <a href="https://www.facebook.com/hoteltulumayolodge" target="_blank" class="social-btn facebook-btn" title="Síguenos en Facebook">
            <i class="fab fa-facebook-f"></i>
        </a>

        <!-- Botón WhatsApp -->
        <!-- Reemplaza el número 51999999999 con tu número real -->
        <a href="https://wa.me/51956220473?text=Hola,%20quisiera%20información%20sobre%20una%20reserva" target="_blank" class="social-btn whatsapp-btn" title="Chatea con nosotros">
            <i class="fab fa-whatsapp"></i>
        </a>

    </div>

    <!-- Asegúrate de cerrar el body aquí -->
</body>
</html>