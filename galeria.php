<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galería | Tulumayo Lodge</title>
    <link rel="icon" type="img/x-icon" href="img/favicon.png">
    <link rel="stylesheet" href="estilos.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <!-- Header / Navbar (Idéntico a index.php) -->
    <?php include 'header.php'; ?>

    <!-- Hero Section (Portada de la página) -->
    <div class="page-hero">
        <!-- Usa una foto bonita para el encabezado -->
        <img src="img/portada1.jpg" alt="Galería Tulumayo">
        <h1>Galería</h1>
    </div>

    <!-- Introducción -->
    <div class="container" style="margin-top: 60px; margin-bottom: 20px;">
        <h2>Rincones Mágicos</h2>
        <p>Un recorrido visual por nuestras instalaciones y la belleza natural de Chanchamayo.</p>
    </div>

    <!-- Grilla de Fotos -->
    <div class="galeria-section">
        <div class="galeria-grid">
            
            <!-- Foto 1 -->
            <div class="foto-item">
                <img src="img/galeria1.jpg" alt="Nuestra Piscina">
                <div class="foto-overlay">Tulumayo Lodge</div>
            </div>

            <!-- Foto 2 -->
            <div class="foto-item">
                <img src="img/galeria2.jpg" alt="Bungalows">
                <div class="foto-overlay">Tulumayo Lodge</div>
            </div>

            <!-- Foto 3 -->
            <div class="foto-item">
                <img src="img/galeria3.jpg" alt="Habitaciones">
                <div class="foto-overlay">Tulumayo Lodge</div>
            </div>

            <!-- Foto 4 -->
            <div class="foto-item">
                <img src="img/galeria4.jpg" alt="Gastronomía">
                <div class="foto-overlay">Tulumayo Lodge</div>
            </div>

            <!-- Foto 5 -->
            <div class="foto-item">
                <img src="img/galeria5.jpg" alt="Vistas al Río">
                <div class="foto-overlay">Tulumayo Lodge</div>
            </div>

            <!-- Foto 6 -->
            <div class="foto-item">
                <img src="img/portada2.jpg" alt="Naturaleza">
                <div class="foto-overlay">Tulumayo Lodge</div>
            </div>

            <!-- Foto 7 -->
            <div class="foto-item">
                <img src="img/galeria7.jpg" alt="Gastronomía">
                <div class="foto-overlay">Tulumayo Lodge</div>
            </div>

            <!-- Foto 8 -->
            <div class="foto-item">
                <img src="img/galeria8.jpg" alt="Vistas al Río">
                <div class="foto-overlay">Tulumayo Lodge</div>
            </div>

            <!-- Foto 9 -->
            <div class="foto-item">
                <img src="img/galeria9.jpg" alt="Naturaleza">
                <div class="foto-overlay">Tulumayo Lodge</div>
            </div>

            <!-- Agrega más divs .foto-item aquí si tienes más fotos -->

        </div>
    </div>

    <!-- Footer (Idéntico a index.php) -->
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