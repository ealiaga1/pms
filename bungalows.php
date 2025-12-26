<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuestros Bungalows | Tulumayo Lodge</title>
    <link rel="icon" type="image/png" href="img/favicon.png">
    <link rel="stylesheet" href="estilos.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include 'header.php'; ?>

    <!-- HERO SECTION -->
<div class="page-hero" style="background-image: url('img/galeria2.jpg');">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1>Nuestros Refugios</h1>
            <p>Confort y privacidad en el corazón de la selva</p>
        </div>
    </div>

    <!-- SECCIÓN PRINCIPAL: FICHA DEL BUNGALOW -->
    <section class="detalle-principal" style="padding: 60px 20px; background: #F4F6F8;">
        <div class="container">
            
            <div class="ficha-bungalow">
                <!-- FOTO PRINCIPAL -->
                <div class="b-slider-container">
                    <img src="img/b6.JPEG" alt="Fachada Bungalow" style="width:100%; height:100%; object-fit:cover;">
                    <div class="b-tag">Vista Exterior</div>
                </div>

                <!-- CARACTERÍSTICAS Y PRECIO -->
                <div class="b-detalles">
                    <h3>Bungalow Tulumayo</h3>
                    <p class="descripcion">
                        Construidos con madera local y diseñados para mantener la frescura de la selva. 
                        Cada bungalow es independiente, garantizando tu privacidad. Cuentan con amplios ventanales, 
                        techos altos y una terraza perfecta para descansar escuchando el río.
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

                    <!-- PRECIO Y BOTÓN (Diseño corregido) -->
                    <div class="precio-action">
                        <div class="precio-box">
                            <div class="precio-principal">S/ 450.00</div>
                            <div class="precio-noche">por noche</div>
                        </div>
                        <button onclick="document.getElementById('modalReserva').style.display='block'" class="btn-reservar-grande">
                            RESERVAR AHORA
                        </button>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- GALERÍA DE DETALLES (INTERIOR / EXTERIOR) -->
    <section class="galeria-detalles" style="padding: 60px 20px; background: white;">
        <div class="container">
            <h2 style="margin-bottom: 40px;">Explora los Detalles</h2>
            
            <div class="grid-galeria-b">
                <!-- Foto 1 -->
                <div class="item-g">
                    <img src="img/b1.jpg" alt="Interior Cama">
                    <span>Habitaciones Amplias</span>
                </div>
                <!-- Foto 2 -->
                <div class="item-g">
                    <img src="img/b4.jpg" alt="Baño">
                    <span>Baños Modernos</span>
                </div>
                <!-- Foto 3 -->
                <div class="item-g">
                    <img src="img/b7.JPEG" alt="Terraza">
                    <span>Terraza Privada</span>
                </div>
                <!-- Foto 4 -->
                <div class="item-g">
                    <img src="img/galeria2.jpg" alt="Vistas">
                    <span>Rodeado de Verde</span>
                </div>
                <!-- Foto 5 -->
                <div class="item-g">
                    <img src="img/b2.jpg" alt="Detalles">
                    <span>Decoración Rústica</span>
                </div>
                <!-- Foto 6 -->
                <div class="item-g">
                    <img src="img/galeria8.jpg" alt="Entorno">
                    <span>Ambiente Natural</span>
                </div>
            </div>
        </div>
    </section>

    <!-- MODAL DE RESERVA -->
    <div id="modalReserva" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Reservar Estadía</h2>
                <span class="close-btn" onclick="document.getElementById('modalReserva').style.display='none'">&times;</span>
            </div>
            <div class="modal-body">
                <p style="color:var(--verde-profundo); margin-bottom:15px;"><i class="fas fa-check"></i> Solicitud para Bungalow Tulumayo</p>
                <form action="procesar_reserva.php" method="POST">
                    <div class="form-group-modal"><label>Nombre:</label><input type="text" name="nombre" required></div>
                    <div class="form-group-modal"><label>Teléfono:</label><input type="text" name="telefono" required></div>
                    <div class="form-group-modal"><label>Email:</label><input type="email" name="email" required></div>
                    <div style="display:flex; gap:10px;">
                        <div class="form-group-modal" style="flex:1;"><label>Llegada:</label><input type="date" name="llegada" required></div>
                        <div class="form-group-modal" style="flex:1;"><label>Salida:</label><input type="date" name="salida" required></div>
                    </div>
                    <div class="form-group-modal">
                        <label>Tipo de Habitación:</label>
                        <select name="habitacion">
                            <option value="Matrimonial">Matrimonial (1 Cama)</option>
                            <option value="Doble">Doble (2 Camas)</option>
                            <option value="Familiar">Familiar (3-4 Camas)</option>
                        </select>
                    </div>
                    <button type="submit" name="btn_reservar" class="btn-confirmar">ENVIAR SOLICITUD</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Script Modal -->
    <script>
        window.onclick = function(event) {
            if (event.target == document.getElementById('modalReserva')) {
                document.getElementById('modalReserva').style.display = "none";
            }
        }
    </script>

    <?php include 'footer.php'; ?>

</body>
</html>