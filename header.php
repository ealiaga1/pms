<!-- Header / Navbar Maestro -->
<header class="main-header">
    <!-- 1. LOGO -->
    <div class="logo">
        <a href="index.php">
            <img src="img/logo.png" alt="Logo Tulumayo Lodge">
        </a>
    </div>
    
    <!-- 2. MARQUESINA (Mensajes Rotativos) -->
    <div class="news-ticker">
        <div class="ticker-wrapper">
            <div class="ticker-item">
                🎄✨ ¡Feliz Navidad y Próspero Año Nuevo! 🥂🎆 
                &nbsp;&bull;&nbsp; 
                🌿 ¡Bienvenidos a la Selva Central! 
                &nbsp;&bull;&nbsp; 
                📅 Reserva con 15 días de anticipación y obtén 10% OFF 
                &nbsp;&bull;&nbsp; 
                ☕ Desayuno Regional Incluido en todas las reservas.
            </div>        </div>
    </div>
    
    <!-- 3. BOTÓN HAMBURGUESA (Móvil) -->
    <div class="menu-toggle" id="mobile-menu" onclick="toggleMenu()">
        <i class="fas fa-bars"></i>
    </div>

    <!-- 4. MENÚ DE NAVEGACIÓN -->
    <nav class="main-nav">
        <ul id="nav-list">
            <li><a href="lodge.php">El Lodge</a></li>
            <li><a href="bungalows.php">Bungalows</a></li>
            <li><a href="galeria.php">Galería</a></li>
            <li><a href="contacto.php">Contacto</a></li>
            <li class="separador">|</li>
            <li><a href="admin/login.php" class="link-admin"><i class="fas fa-lock"></i> Intranet</a></li>
            <li><a href="https://mail.hostinger.com/" target="_blank" class="link-admin"><i class="fas fa-envelope"></i> Correo</a></li>
        </ul>
    </nav>
</header>

<!-- Script para el menú móvil -->
    <script>
       function toggleMenu() {
    // Buscamos la etiqueta nav completa para que el fondo blanco cubra todo el ancho
    const nav = document.querySelector('.main-nav'); 
    nav.classList.toggle('active');
}
    </script>