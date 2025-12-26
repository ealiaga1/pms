<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tulumayo Lodge | Chanchamayo</title>
    <link rel="icon" type="img/x-icon" href="img/favicon.png">
    <!-- Tu hoja de estilos -->
    <link rel="stylesheet" href="estilos.css">
    <!-- Iconos para el check del modal (FontAwesome) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <!-- Header / Navbar -->
 <?php include 'header.php'; ?>

    <!-- Slider Principal (Hero) -->
    <div class="hero-container">
        <img src="img/portada1.jpg" class="slide active">
        <img src="img/portada2.jpg" class="slide">
        <img src="img/portada3.jpg" class="slide">
        
        <div class="hero-text">
            <h1>NATURALEZA PURA</h1>
            <p style="color: white; letter-spacing: 2px; font-size: 0.9rem; text-transform: uppercase;">
                Selva Central, Perú
            </p>
        </div>
    </div>

    <!-- Barra de Reserva Flotante (Interacción JS) -->
    <div class="booking-container">
        <div class="booking-form">
            <div class="input-group">
                <label>Llegada</label>
                <input type="date" id="fechaLlegada" required>
            </div>
            <div class="input-group">
                <label>Salida</label>
                <input type="date" id="fechaSalida" required>
            </div>
            <div class="input-group">
                <label>Huéspedes</label>
                <select id="cantidadHuespedes">
                    <option value="1">1 Adulto</option>
                    <option value="2" selected>2 Adultos</option>
                    <option value="3">Familia (3-4)</option>
                </select>
            </div>
            
            <!-- Botón que activa la verificación y el modal -->
            <button type="button" id="btnConsultar" class="btn-search" onclick="verificarDisponibilidad()">
                Consultar Disponibilidad
            </button>
        </div>
    </div>
<!-- Sección 2: Servicios (Iconos Limpios) -->
    <div class="servicios-section">
        <div class="container">
            <h2>Experiencia Completa</h2>
            <p>Todo lo que necesitas para una estadía inolvidable.</p>
            
            <div class="servicios-grid">
                <!-- 1. Piscina -->
                <div class="servicio-item">
                    <i class="fas fa-water"></i> <!-- Cambié a water o swimming-pool según versión -->
                    <h4>Piscina Infinita</h4>
                </div>
                
                <!-- 2. Restaurante -->
                <div class="servicio-item">
                    <i class="fas fa-utensils"></i>
                    <h4>Restaurante Regional</h4>
                </div>
                
                <!-- 3. WiFi -->
                <div class="servicio-item">
                    <i class="fas fa-wifi"></i>
                    <h4>Wi-Fi Satelital</h4>
                </div>
                
                <!-- 4. Bar -->
                <div class="servicio-item">
                    <i class="fas fa-cocktail"></i>
                    <h4>Bar Exótico</h4>
                </div>
                
                <!-- 5. Estacionamiento -->
                <div class="servicio-item">
                    <i class="fas fa-car"></i>
                    <h4>Estacionamiento</h4>
                </div>

                <!-- 6. Aire Acondicionado (NUEVO) -->
                <div class="servicio-item">
                    <i class="fas fa-snowflake"></i>
                    <h4>Aire Acondicionado</h4>
                </div>

                <!-- 7. TV Cable (NUEVO) -->
                <div class="servicio-item">
                    <i class="fas fa-tv"></i>
                    <h4>TV Cable</h4>
                </div>

                <!-- 8. YouTube / Streaming (NUEVO) -->
                <div class="servicio-item">
                    <i class="fab fa-youtube"></i>
                    <h4>Smart TV / YouTube</h4>
                </div>

                <!-- 9. Servicio a la Habitación (NUEVO) -->
                <div class="servicio-item">
                    <i class="fas fa-concierge-bell"></i>
                    <h4>Room Service</h4>
                </div>

                <!-- 10. No Mascotas (NUEVO) -->
                <div class="servicio-item">
                    <i class="fas fa-ban" style="color: #e74c3c;"></i> <!-- Icono rojo para resaltar la prohibición -->
                    <h4>No Mascotas</h4>
                </div>
            </div>
            
            </div>
        </div>
    </div>

<!-- SECCIÓN BUNGALOW ÚNICO (Fondo Plomo) -->
    <section id="bungalows" class="bungalow-unico-section">
        <div class="container">
            <h2>Tu Refugio en la Selva</h2>
            <p style="text-align:center; max-width: 700px; margin: 0 auto 40px; color: #666;">
                Arquitectura rústica integrada con la naturaleza, diseñada para tu confort y privacidad.
            </p>

            <!-- TARJETA ÚNICA GRANDE -->
            <div class="ficha-bungalow">
                
                <!-- IZQUIERDA: SLIDER DE FOTOS -->
                <div class="b-slider-container">
                    <!-- Foto 1: Exterior -->
                    <div class="b-slide fade">
                        <img src="img/b6.JPEG" alt="Vista Exterior Bungalow">
                        <div class="b-tag">Vista Exterior</div>
                    </div>
                    
                    <!-- Foto 2: Interior Cama -->
                    <div class="b-slide fade">
                        <img src="img/b1.jpg" alt="Interior Dormitorio">
                        <div class="b-tag">Interior Confortable</div>
                    </div>

                    <!-- Foto 3: Baño/Detalle -->
                    <div class="b-slide fade">
                        <img src="img/b4.jpg" alt="Baño o Terraza">
                        <div class="b-tag">Baño Privado</div>
                    </div>


                </div>

                <!-- DERECHA: INFORMACIÓN -->
                <div class="b-detalles">
                    <h3>Bungalow Tulumayo</h3>
                    <p class="descripcion">
                        Disfruta de la experiencia Tulumayo en nuestros bungalows independientes construidos con Bambu local y techos altos para mayor frescura. 
                        Cada unidad cuenta con una terraza privada perfecta para escuchar el río y desconectarse del mundo.
                    </p>

                    <div class="iconos-grid">
                        <div class="icon-item"><i class="fas fa-bed"></i> 2 Camas </div>
                        <div class="icon-item"><i class="fas fa-bed"></i> Cama Queen </div>
                        <div class="icon-item"><i class="fas fa-shower"></i> Agua Caliente</div>
                        <div class="icon-item"><i class="fas fa-wifi"></i> Wi-Fi Satelital</div>
                        <div class="icon-item"><i class="fas fa-wind"></i> Aire Acondicinado</div>
                        <div class="icon-item"><i class="fas fa-tv"></i> TV Cable</div>
                        <div class="icon-item"><i class="fas fa-mountain"></i> Terraza Privada</div>
                    </div>

                    <div class="precio-action">
    <div class="precio-box">
        <!--<div class="precio-principal">S/ 450.00</div>-->
        <!--<div class="precio-noche">por noche</div>-->
    </div>
    
    <button onclick="document.getElementById('modalReserva').style.display='block'" class="btn-reservar-grande">
        RESERVAR AHORA
    </button>
</div>
                </div>

            </div>
        </div>
    </section>

    <!-- SECCIÓN EXPERIENCIAS (Estilo Guizado Portillo) -->
    <section class="experiencias-section" style="padding: 60px 20px; background: #fff;">
        <div class="container">
            <h2>Vive Chanchamayo</h2>
            <p style="text-align:center; max-width: 700px; margin: 0 auto 40px;">
                No solo es un hotel, es tu conexión con la selva. Disfruta de actividades exclusivas.
            </p>

            <div class="grid-experiencias">
                <!-- Card 1 -->
                <div class="card-exp">
                    <img src="img/portada1.jpg" alt="Cataratas">
                    <div class="exp-content">
                        <h3>Aventura</h3>
                        <p>Tours a cataratas y caminatas.</p>
                    </div>
                </div>
                <!-- Card 2 -->
                <div class="card-exp">
                    <img src="img/galeria4.jpg" alt="Gastronomía">
                    <div class="exp-content">
                        <h3>Gastronomía</h3>
                        <p>Sabores exóticos de la selva.</p>
                    </div>
                </div>
                <!-- Card 3 -->
                <div class="card-exp">
                    <img src="img/galeria5.jpg" alt="Relax">
                    <div class="exp-content">
                        <h3>Relax Total</h3>
                        <p>Piscina con vista al río.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

   <!-- === NUEVA SECCIÓN DE VIDEO === -->
    <section class="video-section">
        <div class="container">
            <h2 class="titulo-moderno">Vive la Experiencia</h2>
            <p class="subtitulo-moderno">Descubre la magia que te espera en el corazón de Chanchamayo.</p>
            
            <div class="video-wrapper">
                <div class="video-container">
                    <!-- REEMPLAZA EL ID DEL VIDEO (lo que va después de /embed/) -->
                    <!-- Ejemplo: Si tu video es youtube.com/watch?v=dQw4w9WgXcQ, pon dQw4w9WgXcQ -->
                    <iframe src="https://www.youtube.com/embed/txXswqKi7cs?si=07SmvlT8zIhoqbPY"
                            title="Tulumayo Lodge Video" 
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" 
                            allowfullscreen>
                    </iframe>
                </div>
            </div>
        </div>
    </section>
    <!-- Sección Habitaciones (Carrusel) -->
    <div class="container" style="position: relative; padding: 0 50px;">
        
       <div class="container">
        <h2>Un respiro en la selva</h2>
        <p>Tulumayo Lodge es un espacio diseñado para desconectarse. Arquitectura sostenible, comida local y el sonido del río Tulumayo como única compañía.</p>
    </div>


        <!-- Botón Izquierda -->
        <button class="carrusel-btn btn-anterior" onclick="moverCarrusel(-1)">&#10094;</button>

        <div class="carrusel-viewport">
            <div class="carrusel-track" id="track">
                
                <!-- Tarjeta 1 -->
                <div class="card-carrusel">
                    <div class="card-img">
                        <img src="img/galeria1.jpg" alt="Suite Matrimonial">
                    </div>
                    <h3>Tulumayo</h3>
                    <span>Lodge</span>
                </div>
                
                <!-- Tarjeta 2 -->
                <div class="card-carrusel">
                    <div class="card-img">
                        <img src="img/galeria2.jpg" alt="Bungalow">
                    </div>
                    <h3>Tulumayo</h3>
                    <span>Lodge</span>
                </div>

                <!-- Tarjeta 3 -->
                <div class="card-carrusel">
                    <div class="card-img">
                        <img src="img/galeria3.jpg" alt="Doble">
                    </div>
                    <h3>Tulumayo</h3>
                    <span>Lodge</span>
                </div>

                <!-- Tarjeta 4 -->
                <div class="card-carrusel">
                    <div class="card-img">
                        <img src="img/galeria4.jpg" alt="Camping">
                    </div>
                    <h3>Tulumayo</h3>
                    <span>Lodge</span>
                </div>

                <!-- Tarjeta 5 -->
                <div class="card-carrusel">
                    <div class="card-img">
                        <img src="img/galeria5.jpg" alt="Vistas">
                    </div>
                    <h3>Tulumayo</h3>
                    <span>Lodge</span>
                </div>

            </div>
        </div>


        <!-- Botón Derecha -->
        <button class="carrusel-btn btn-siguiente" onclick="moverCarrusel(1)">&#10095;</button>

    </div>

    <!-- Footer Simple -->
  <?php include 'footer.php'; ?>

    <!-- EL MODAL (Formulario Real) -->
    <div id="modalReserva" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Completar Reserva</h2>
                <span class="close-btn" onclick="cerrarModal()">&times;</span>
            </div>
            
            <div class="modal-body">
                <p style="font-size: 0.9rem; color: green; margin-bottom: 15px;">
                    <i class="fas fa-check-circle"></i> ¡Fechas disponibles! Ingresa tus datos.
                </p>

                <form action="procesar_reserva.php" method="POST">
                    <!-- Datos Personales -->
                    <div class="form-group-modal">
                        <label>Nombre Completo:</label>
                        <input type="text" name="nombre" placeholder="Ej. Juan Pérez" required>
                    </div>
                    <div class="form-group-modal">
                        <label>Email:</label>
                        <input type="email" name="email" placeholder="correo@ejemplo.com" required>
                    </div>
                    <div class="form-group-modal">
                        <label>Teléfono / WhatsApp:</label>
                        <input type="text" name="telefono" placeholder="+51 999..." required>
                    </div>

                    <!-- Datos Ocultos (se llenan con JS desde la barra anterior) -->
                    <input type="hidden" name="llegada" id="modalLlegada">
                    <input type="hidden" name="salida" id="modalSalida">
                    <input type="hidden" name="habitacion" value="Por definir en counter"> 

                    <div class="form-group-modal">
                        <label>Tipo de Habitación Preferida:</label>
                        <select name="habitacion_pref">
                            <!--<option value="Matrimonial">Bungalow Matrimonial</option>-->

                            <option value="Bungalow">Bungalow</option>
                        </select>
                    </div>

                    <div class="form-group-modal">
                        <label>Comentarios Adicionales:</label>
                        <textarea name="comentarios" rows="2" style="width:100%; border:1px solid #ccc;"></textarea>
                    </div>

                    <button type="submit" name="btn_reservar" class="btn-confirmar">CONFIRMAR RESERVA</button>
                </form>
            </div>
        </div>
    </div>

    <!-- ================= SCRIPTS ================= -->

    <!-- 1. Script Slider Principal (Hero) -->
    <script>
        let slideIndex = 0;
        showSlides();
        function showSlides() {
            let i;
            let slides = document.getElementsByClassName("slide");
            for (i = 0; i < slides.length; i++) {
                slides[i].style.display = "none";  
            }
            slideIndex++;
            if (slideIndex > slides.length) {slideIndex = 1}    
            slides[slideIndex-1].style.display = "block";  
            setTimeout(showSlides, 5000); 
        }
    </script>

    <!-- 2. Script Carrusel Habitaciones -->
    <script>
        let posicionActual = 0;
        const track = document.getElementById('track');
        const cards = document.querySelectorAll('.card-carrusel');
        let autoSlideInterval; 

        function moverCarrusel(direccion) {
            const totalCards = cards.length;
            const anchoPantalla = window.innerWidth;
            let tarjetasVisibles;

            if (anchoPantalla > 1100) { tarjetasVisibles = 4; }
            else if (anchoPantalla > 900) { tarjetasVisibles = 3; }
            else if (anchoPantalla > 600) { tarjetasVisibles = 2; }
            else { tarjetasVisibles = 1; }
            
            const anchoTarjeta = cards[0].offsetWidth + 20; // 20 es el gap

            posicionActual += direccion;

            if (posicionActual > totalCards - tarjetasVisibles) {
                posicionActual = 0;
            } 
            else if (posicionActual < 0) {
                posicionActual = totalCards - tarjetasVisibles;
            }

            const movimiento = -(posicionActual * anchoTarjeta);
            track.style.transform = `translateX(${movimiento}px)`;
        }

        function iniciarAutoPlay() {
            autoSlideInterval = setInterval(() => {
                moverCarrusel(1);
            }, 3000); 
        }

        function detenerAutoPlay() {
            clearInterval(autoSlideInterval);
        }

        iniciarAutoPlay();

        const viewport = document.querySelector('.carrusel-viewport');
        viewport.addEventListener('mouseenter', detenerAutoPlay);
        viewport.addEventListener('mouseleave', iniciarAutoPlay);

        window.addEventListener('resize', () => {
            track.style.transform = `translateX(0px)`;
            posicionActual = 0;
        });
    </script>

    <!-- 3. SCRIPT DEL MODAL (Faltaba esto para que funcione el botón consultar) -->
    <script>
        function verificarDisponibilidad() {
            // Obtener valores de la barra
            let llegada = document.getElementById("fechaLlegada").value;
            let salida = document.getElementById("fechaSalida").value;
            let boton = document.getElementById("btnConsultar");

            // Validación básica
            if (llegada === "" || salida === "") {
                alert("Por favor, selecciona las fechas de llegada y salida.");
                return;
            }
            
            if (llegada >= salida) {
                alert("La fecha de salida debe ser posterior a la de llegada.");
                return;
            }

            // Simulación de carga
            boton.innerHTML = "Verificando...";
            boton.style.backgroundColor = "#ccc";

            setTimeout(() => {
                // Simulación: Siempre disponible
                boton.innerHTML = "¡Disponible! Reservar";
                boton.style.backgroundColor = "#2E7D32"; 
                
                // Abrir modal después de 0.5s
                setTimeout(() => {
                    abrirModal();
                    // Resetear botón
                    boton.innerHTML = "Consultar Disponibilidad"; 
                    boton.style.backgroundColor = "#3A5A40"; 
                }, 500);

            }, 1000); 
        }

        function abrirModal() {
            // Copiar fechas al formulario oculto
            document.getElementById("modalLlegada").value = document.getElementById("fechaLlegada").value;
            document.getElementById("modalSalida").value = document.getElementById("fechaSalida").value;

            // Mostrar modal
            document.getElementById("modalReserva").style.display = "block";
        }

        function cerrarModal() {
            document.getElementById("modalReserva").style.display = "none";
        }

        // Cerrar si clic fuera del modal
        window.onclick = function(event) {
            let modal = document.getElementById("modalReserva");
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    </script>
     <script>
        const menuBtn = document.getElementById('mobile-menu');
        const navList = document.getElementById('nav-list');

        menuBtn.addEventListener('click', () => {
            navList.classList.toggle('active');
        });
    </script>
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
<!-- SCRIPT PARA EL SLIDER DEL BUNGALOW -->
<script>
    let slideIndexB = 0;
    showSlidesB();

    function showSlidesB() {
        let i;
        let slides = document.getElementsByClassName("b-slide");
        
        // Ocultar todas
        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";  
        }
        
        slideIndexB++;
        if (slideIndexB > slides.length) {slideIndexB = 1}    
        
        // Mostrar la actual
        slides[slideIndexB-1].style.display = "block";  
        
        // Cambiar cada 3 segundos (3000ms)
        setTimeout(showSlidesB, 3000); 
    }
</script>
    <!-- Asegúrate de cerrar el body aquí -->
<script>
    // Script para el Slider del Bungalow
    let bIndex = 0;
    showBSlides();

    function showBSlides() {
        let i;
        let slides = document.getElementsByClassName("b-slide");
        // Si no encuentra slides (por ejemplo en otras paginas), se detiene para no dar error
        if (slides.length === 0) return; 

        for (i = 0; i < slides.length; i++) {
            slides[i].style.display = "none";  
        }
        bIndex++;
        if (bIndex > slides.length) {bIndex = 1}    
        
        slides[bIndex-1].style.display = "block";  
        setTimeout(showBSlides, 4000); // Cambia cada 4 segundos
    }
</script>
</body>
</html>