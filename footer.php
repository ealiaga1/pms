<!-- Footer Profesional -->
    <footer style="background-color: #222; color: #bbb; padding: 60px 0 20px; font-size: 0.9rem;">
        <div class="container" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 40px; text-align: left;">
            
            <!-- Columna 1: Info -->
            <div>
                <h3 style="color:white; margin-bottom:20px; text-transform:uppercase;">Tulumayo Lodge</h3>
                <p>Tu refugio en la Selva Central. Confort, naturaleza y la mejor atención en Chanchamayo.</p>
                <br>
                <p><i class="fas fa-map-marker-alt" style="color:#C5A059;"></i>Av. Eduardo Velarde N°360 - Playa Hermosa -  San Ramón - Chanchamayo</p>
                <p><i class="fas fa-phone" style="color:#C5A059;"></i> +51 956 220 473</p>
                <p><i class="fas fa-envelope" style="color:#C5A059;"></i> reservas@tulumayolodge.com</p>
            </div>

            <!-- Columna 2: Enlaces -->
            <div>
                <h3 style="color:white; margin-bottom:20px; text-transform:uppercase;">Enlaces Rápidos</h3>
                <ul style="list-style:none; padding:0; line-height: 2;">
                    <li><a href="lodge.php" style="color:#bbb; text-decoration:none;">Nosotros</a></li>
                    <li><a href="galeria.php" style="color:#bbb; text-decoration:none;">Galería de Fotos</a></li>
                    <li><a href="#" onclick="document.getElementById('modalReserva').style.display='block'" style="color:#bbb; text-decoration:none;">Reservar Ahora</a></li>
                    <li><a href="contacto.php" style="color:#bbb; text-decoration:none;">Ubicación</a></li>
                </ul>
            </div>

            <!-- Columna 3: Mapa -->
            <div>
                <h3 style="color:white; margin-bottom:20px; text-transform:uppercase;">Ubicación</h3>
                <!-- Mapa pequeño -->
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2444.165078971386!2d-75.35670730160525!3d-11.132583999999984!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x91090d00104975ab%3A0x6c918ab989528fe1!2sHotel%20Tulumayo%20lodge!5e1!3m2!1ses-419!2spe!4v1765127179222!5m2!1ses-419!2spe" width="100%" height="150" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>

        <div style="text-align: center; margin-top: 50px; border-top: 1px solid #444; padding-top: 20px;">
            <p>&copy; 2025 Tulumayo Lodge. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- BOTONES FLOTANTES -->
    <div class="floating-container">
        <a href="https://www.facebook.com/hoteltulumayolodge" target="_blank" class="social-btn facebook-btn" title="Síguenos en Facebook">
            <i class="fab fa-facebook-f"></i>
        </a>
        <a href="https://wa.me/51956220473?text=Hola,%20quisiera%20información%20sobre%20una%20reserva" target="_blank" class="social-btn whatsapp-btn" title="Chatea con nosotros">
            <i class="fab fa-whatsapp"></i>
        </a>
    </div>

    <!-- SCRIPT MENÚ MÓVIL -->
    <script>
        const menuBtn = document.getElementById('mobile-menu');
        const navList = document.getElementById('nav-list');

        if(menuBtn){
            menuBtn.addEventListener('click', () => {
                navList.classList.toggle('active');
            });
        }
    </script>
</body>
</html>