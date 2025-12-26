<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>El Lodge | Tulumayo Lodge</title>
    <link rel="icon" type="img/x-icon" href="img/favicon.png">
    <link rel="stylesheet" href="estilos.css">
    <!-- FontAwesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <!-- Header / Navbar -->
   <?php include 'header.php'; ?>

    <!-- Hero Section -->
    <div class="page-hero">
        <!-- Foto de fondo: Usa una foto general del hotel o paisaje -->
        <img src="img/portada3.jpg" alt="Vista General Tulumayo">
        <h1>Nuestra Esencia</h1>
    </div>

    <!-- Sección 1: Historia / Concepto -->
    <div class="split-section">
        <div class="split-text">
            <h2>Bienvenido al Paraíso</h2>
            <p>
                Tulumayo Lodge nació del sueño de integrar el confort moderno con la majestuosidad de la Selva Central. Ubicados estratégicamente en el valle de Chanchamayo, somos más que un hotel; somos un refugio para el alma.
            </p>
            <p>
                Nuestra arquitectura respeta el entorno, utilizando materiales de la zona como madera y piedra, permitiendo que la brisa del río y los sonidos de la naturaleza sean los protagonistas de tu estancia.
            </p>
        </div>
        <div class="split-img">
            <!-- Foto vertical o cuadrada bonita -->
            <img src="img/galeria2.jpg" alt="Arquitectura del Lodge">
        </div>
    </div>

    <!-- Sección 2: Servicios (Iconos Limpios) -->
    <div class="servicios-section">
        <div class="container">
            <h2>Experiencia Completa</h2>
            <p>Todo lo que necesitas para una estadía inolvidable.</p>
            
            <div class="servicios-grid">
                <!-- Icono 1 -->
                <div class="servicio-item">
                    <i class="fas fa-swimming-pool"></i>
                    <h4>Piscina Infinita</h4>
                </div>
                <!-- Icono 2 -->
                <div class="servicio-item">
                    <i class="fas fa-utensils"></i>
                    <h4>Restaurante Regional</h4>
                </div>
                <!-- Icono 3 -->
                <div class="servicio-item">
                    <i class="fas fa-wifi"></i>
                    <h4>Wi-Fi Satelital</h4>
                </div>
                <!-- Icono 4 -->
                <div class="servicio-item">
                    <i class="fas fa-cocktail"></i>
                    <h4>Bar</h4>
                </div>
                <!-- Icono 5 -->
                <div class="servicio-item">
                    <i class="fas fa-parking"></i>
                    <h4>Estacionamiento</h4>
                </div>
                <!-- Icono 6 -->
            
            </div>
        </div>
    </div>

    <!-- Sección 3: Naturaleza / Sostenibilidad -->
    <div class="split-section">
        <div class="split-img">
            <!-- Foto de naturaleza, rio o animales -->
            <img src="img/portada1.jpg" alt="Río Tulumayo">
        </div>
        <div class="split-text">
            <h2>Compromiso Natural</h2>
            <p>
                Nos encontramos a orillas del río Tulumayo, un ecosistema vibrante lleno de vida. En Tulumayo Lodge, practicamos un turismo responsable.
            </p>
            <p>
                Trabajamos de la mano con las comunidades locales para ofrecer insumos frescos en nuestro restaurante y promovemos el cuidado de la flora y fauna silvestre de Chanchamayo.
            </p>
        </div>
    </div>

    <!-- Call to Action (CTA) -->
    <div style="background-color: var(--verde-mate); padding: 60px 20px; text-align: center; color: white;">
        <h2 style="color: white; margin-bottom: 20px;">¿Listo para la aventura?</h2>
        <a href="index.php" style="
            display: inline-block;
            background: white; 
            color: var(--verde-mate); 
            padding: 15px 40px; 
            text-decoration: none; 
            font-weight: bold; 
            text-transform: uppercase;
            letter-spacing: 2px;">
            Reservar Ahora
        </a>
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