<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima');

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// 1. VERIFICAR CAJA ABIERTA
$usuario_actual = $_SESSION['usuario'];
$qUser = mysqli_query($conexion, "SELECT id FROM usuarios_admin WHERE usuario = '$usuario_actual'");
$fUser = mysqli_fetch_assoc($qUser);
$id_usuario_actual = $fUser['id'];

$qCaja = mysqli_query($conexion, "SELECT id FROM caja_sesiones WHERE id_usuario = '$id_usuario_actual' AND estado = 'abierta'");
$caja_abierta_php = (mysqli_num_rows($qCaja) > 0) ? 'true' : 'false';

// 2. OBTENER TIPOS DE HABITACIÓN (Para crear reserva)
$sqlHabitaciones = "SELECT tipo, MAX(precio_noche) as precio_baja, MAX(precio_alta) as precio_alta 
                    FROM habitaciones GROUP BY tipo";
$resHabitaciones = mysqli_query($conexion, $sqlHabitaciones);
$tipos_hab = array();
while($h = mysqli_fetch_assoc($resHabitaciones)){
    $tipos_hab[] = $h;
}

// 3. OBTENER TODAS LAS HABITACIONES (Para pre-asignar - ESTO FALTABA)
$sqlTodas = "SELECT id, nombre FROM habitaciones ORDER BY nombre ASC";
$resTodas = mysqli_query($conexion, $sqlTodas);
$todas_habitaciones = array();
while($row = mysqli_fetch_assoc($resTodas)) {
    $todas_habitaciones[] = $row;
}

// 4. OBTENER TEMPORADAS ALTAS
$sqlTemp = "SELECT * FROM temporadas";
$resTemp = mysqli_query($conexion, $sqlTemp);
$temporadas_db = array();
while($t = mysqli_fetch_assoc($resTemp)){
    $temporadas_db[] = array(
        'nombre' => $t['nombre'],
        'start'  => $t['inicio'],
        'end'    => $t['fin']
    );
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrativo | Tulumayo Lodge</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>

    <style>
        /* Estilos Base */
        body { margin: 0; font-family: 'Montserrat', sans-serif; background-color: #f4f7f6; display: flex; height: 100vh; }
        
        .sidebar { width: 250px; background-color: #2E5C38; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background: #244a2d; margin: 0; font-size: 1.1rem; border-bottom: 1px solid #3A5A40; }
        
        .sidebar a { padding: 15px 20px; color: white; text-decoration: none; display: flex; align-items: center; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.3s; }
        .sidebar a:hover { background-color: #3A5A40; padding-left: 25px; } 
        .sidebar a.active { background-color: #2ecc71; font-weight: bold; }
        .sidebar .btn-destacado { background-color: #f39c12; color: #fff; font-weight: bold; }
        .sidebar .btn-destacado:hover { background-color: #e67e22; }

        .main-content { flex: 1; padding: 20px; overflow-y: auto; }

        .calendar-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .btn-new { background-color: #2E5C38; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 8px; }

        #calendar { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); height: 80vh; }
        
        /* Modales */
        .modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.6); backdrop-filter: blur(3px); }
        .modal-content { background-color: white; margin: 2% auto; padding: 0; width: 450px; border-radius: 10px; overflow: hidden; box-shadow: 0 10px 25px rgba(0,0,0,0.2); animation: slideDown 0.3s; }
        @keyframes slideDown { from {transform: translateY(-50px); opacity: 0;} to {transform: translateY(0); opacity: 1;} }

        .modal-header { background: #2E5C38; color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { margin: 0; font-size: 1.1rem; }
        .close { cursor: pointer; font-size: 1.5rem; }
        .modal-body { padding: 20px; }
        
        .form-label { display: block; font-size: 0.85rem; font-weight: bold; margin-bottom: 5px; color: #333; margin-top: 10px; }
        .form-input, .form-select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; font-family: inherit; }
        .info-group { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 15px; }
        .info-item { font-size: 0.9rem; color: #555; }
        .info-item i { color: #2E5C38; margin-right: 5px; }
        .info-item strong { display: block; color: #333; margin-top: 2px; }
        
        .btn-group { display: flex; gap: 10px; margin-top: 20px; }
        .btn { flex: 1; padding: 12px; border: none; border-radius: 4px; color: white; cursor: pointer; font-weight: bold; font-size: 0.95rem; }
        .btn-confirm { background: #2ecc71; } .btn-confirm:hover { background: #27ae60; }
        .btn-cancel { background: #e74c3c; } .btn-cancel:hover { background: #c0392b; }

        .box-confirmado { background: #e8f5e9; border: 1px solid #c8e6c9; padding: 15px; border-radius: 5px; text-align: center; color: #2e7d32; }
        
        /* Caja de cálculo de precio */
        .precio-estimado { background: #fdfdfd; border: 1px solid #ddd; padding: 15px; border-radius: 5px; margin-top: 15px; font-size: 0.9rem; }
        .tag-temp { padding: 2px 6px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; color: white; }
        .temp-alta { background: #e74c3c; }
        .temp-baja { background: #3498db; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <div class="calendar-header">
            <h2 style="margin:0; color:#333;">Calendario de Reservas</h2>
            
            <!-- AVISO VISUAL SI CAJA ESTÁ CERRADA -->
            <?php if($caja_abierta_php == 'false'): ?>
                <span style="color:#e74c3c; font-weight:bold; font-size:0.9rem;">
                    <i class="fas fa-exclamation-triangle"></i> Caja Cerrada (Solo modo lectura/creación)
                </span>
            <?php endif; ?>

            <div style="display:flex; gap:10px;">
                <!-- BOTÓN AIRBNB (Rojo) -->
                <a href="importar_airbnb.php" style="background-color: #FF5A5F; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-sync"></i> Sincronizar Airbnb
                </a>

                <!-- BOTÓN BOOKING (Azul) - NUEVO -->
                <a href="importar_booking.php" style="background-color: #003580; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; display: flex; align-items: center; gap: 8px;">
                    <i class="fas fa-sync"></i> Sincronizar Booking
                </a>

                <!-- BOTÓN NUEVA RESERVA (Verde) -->
                <button class="btn-new" onclick="abrirModalCrear()"><i class="fas fa-plus-circle"></i> Nueva Reserva</button>
            </div>
        </div>
        <div id='calendar'></div>
    </div>

    <!-- MODAL 1: GESTIONAR EXISTENTE -->
    <div id="modalGestion" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitulo">Gestionar Reserva</h3>
                <span class="close" onclick="cerrarModalGestion()">&times;</span>
            </div>
            <div class="modal-body">
                <div class="info-group">
                    <div class="info-item"><i class="fas fa-calendar"></i> Fechas<strong id="modalFechas"></strong></div>
                    <div class="info-item"><i class="fas fa-bed"></i> Habitación<strong id="modalHabitacion"></strong></div>
                    <div class="info-item"><i class="fas fa-user"></i> Cliente<strong id="modalCliente"></strong></div>
                    <div class="info-item"><i class="fas fa-phone"></i> Contacto<strong id="modalTelefono"></strong></div>
                </div>

                <div id="bloquePendiente" style="background: #fdfdfd; padding: 20px; border: 1px solid #ddd; border-radius: 8px;">
                    <p style="margin: 0 0 10px 0; font-weight: bold; color: #f39c12;">⚠ Confirmar Reserva / Pago</p>
                    
                    <!-- SELECTOR DE HABITACIÓN ASIGNADA -->
                    <label class="form-label" style="color:#2980b9;">Pre-asignar Habitación (Opcional):</label>
                    <select id="inputHabAsignada" class="form-select">
                        <option value="0">-- Sin asignar (Decidir al llegar) --</option>
                        <?php foreach($todas_habitaciones as $h): ?>
                            <option value="<?php echo $h['id']; ?>"><?php echo $h['nombre']; ?></option>
                        <?php endforeach; ?>
                    </select>
                    
                    <label class="form-label">Método de Pago:</label>
                    <select id="inputMetodo" class="form-select">
                        <option value="">-- Seleccionar --</option>
                        <option value="Efectivo">Efectivo</option>
                        <option value="Yape">Qr YziPay</option>
                        <option value="Transferencia BCP">Transferencia Bancaria</option>
                        <option value="Booking.com (Tarjeta Virtual)">Booking.com (Cobrado)</option>
                        <option value="Booking.com (Pago en Hotel)">Booking.com (Pago en Hotel)</option>
                        <option value="Airbnb">Airbnb</option>
                        <option value="Expedia">Expedia</option>
                    </select>

                    <label class="form-label">Monto Adelanto (S/):</label>
                    <input type="number" id="inputMontoReal" class="form-input" placeholder="0.00" step="0.50">
                    <label class="form-label">Nota / Código Operación:</label>
                    <input type="text" id="inputPagoNota" class="form-input">
                    <label class="form-label">Hora Llegada:</label>
                    <input type="time" id="inputHora" class="form-input">
                    <input type="hidden" id="idReservaActual">
                    
                    <div class="btn-group">
                        <button class="btn btn-confirm" onclick="procesarReserva('confirmar')">CONFIRMAR</button>
                    </div>
                </div>

                <div id="bloqueConfirmado" style="display:none;">
                    <div class="box-confirmado">
                        <i class="fas fa-check-circle" style="font-size: 3rem;"></i>
                        <h3>Confirmada</h3>
                        <p style="color:#555;">Adelanto: S/ <span id="txtMonto"></span> (<span id="txtMetodo"></span>)</p>
                        
                        <div id="txtAsignada" style="margin-top:10px; font-weight:bold; color:#2980b9;"></div>
                    </div>
                </div>

                <div style="margin-top: 10px; text-align: center;">
                    <a href="#" onclick="procesarReserva('cancelar')" style="color: #e74c3c;">Eliminar Reserva</a>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: CREAR NUEVA RESERVA MANUAL -->
    <div id="modalCrear" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus"></i> Nueva Reserva Manual</h3>
                <span class="close" onclick="document.getElementById('modalCrear').style.display='none'">&times;</span>
            </div>
            <div class="modal-body">
                <form id="formCrearReserva" onsubmit="guardarReservaManual(event)">
                    <label class="form-label">Nombre del Cliente:</label>
                    <input type="text" name="nombre" class="form-input" required placeholder="Ej. Carlos Torres">
                    <div class="info-group">
                        <div><label class="form-label">Teléfono:</label><input type="text" name="telefono" class="form-input" required></div>
                        <div><label class="form-label">Email (Op):</label><input type="email" name="email" class="form-input"></div>
                    </div>
                    
                    <div class="info-group">
                        <div><label class="form-label">Llegada:</label><input type="date" name="llegada" id="crearLlegada" class="form-input" required onchange="calcularCotizacion()"></div>
                        <div><label class="form-label">Salida:</label><input type="date" name="salida" id="crearSalida" class="form-input" required onchange="calcularCotizacion()"></div>
                    </div>

                    <label class="form-label">Tipo Habitación:</label>
                    <select name="habitacion" id="selectHabitacion" class="form-select" onchange="calcularCotizacion()">
                        <?php foreach($tipos_hab as $t): ?>
                            <option value="<?php echo $t['tipo']; ?>" 
                                    data-baja="<?php echo $t['precio_baja']; ?>" 
                                    data-alta="<?php echo $t['precio_alta']; ?>">
                                <?php echo $t['tipo']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <div class="precio-estimado" id="boxCotizacion" style="display:none;">
                        <strong><i class="fas fa-calculator"></i> Cotización Estimada:</strong><br>
                        <span id="detalleTemporada"></span><br>
                        Total: <span id="totalEstimado" style="font-size:1.2rem; font-weight:bold; color:#2E5C38;">S/ 0.00</span>
                    </div>

                    <button type="submit" class="btn btn-confirm" style="margin-top:20px;">CREAR RESERVA</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const CAJA_ABIERTA = <?php echo $caja_abierta_php; ?>;
        const temporadasAltas = <?php echo json_encode($temporadas_db); ?>;
        const listaHabitaciones = <?php echo json_encode($todas_habitaciones); ?>;

        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                locale: 'es',
                selectable: true,
                headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,listMonth' },
                events: 'api_reservas.php',
                eventClick: function(info) { abrirModalGestion(info.event); },
                dateClick: function(info) { abrirModalCrear(info.dateStr); }
            });
            calendar.render();
            window.miCalendario = calendar;
        });

        function esTemporadaAlta(fechaStr) {
            let fecha = new Date(fechaStr + "T00:00:00");
            let mes = (fecha.getMonth() + 1).toString().padStart(2, '0');
            let dia = fecha.getDate().toString().padStart(2, '0');
            let fechaCorta = mes + '-' + dia;
            for (let t of temporadasAltas) {
                if (fechaCorta >= t.start && fechaCorta <= t.end) return t.nombre;
            }
            return false;
        }

        function calcularCotizacion() {
            let llegada = document.getElementById('crearLlegada').value;
            let salida = document.getElementById('crearSalida').value;
            let select = document.getElementById('selectHabitacion');
            let box = document.getElementById('boxCotizacion');
            
            if(llegada && salida) {
                let precioBaja = parseFloat(select.options[select.selectedIndex].getAttribute('data-baja'));
                let precioAlta = parseFloat(select.options[select.selectedIndex].getAttribute('data-alta'));
                let d1 = new Date(llegada); let d2 = new Date(salida);
                let diff = Math.abs(d2 - d1); let noches = Math.ceil(diff / (1000 * 60 * 60 * 24));
                if(noches == 0) noches = 1;

                let nombreTemp = esTemporadaAlta(llegada);
                let precioFinal = nombreTemp ? precioAlta : precioBaja;
                let etiqueta = nombreTemp ? `<span class="tag-temp temp-alta">Alta: ${nombreTemp}</span>` : `<span class="tag-temp temp-baja">Temporada Baja</span>`;
                let total = precioFinal * noches;

                document.getElementById('detalleTemporada').innerHTML = `${noches} noche(s) x S/ ${precioFinal} <br> ${etiqueta}`;
                document.getElementById('totalEstimado').innerText = "S/ " + total.toFixed(2);
                box.style.display = 'block';
            }
        }

        var modalG = document.getElementById("modalGestion");
        function abrirModalGestion(evento) {
            let props = evento.extendedProps;
            document.getElementById('modalTitulo').innerText = "Reserva #" + evento.id;
            document.getElementById('modalCliente').innerText = evento.title;
            document.getElementById('modalFechas').innerText = evento.start.toLocaleDateString();
            document.getElementById('modalHabitacion').innerText = props.habitacion;
            document.getElementById('modalTelefono').innerText = props.telefono;
            document.getElementById('idReservaActual').value = evento.id;

            // CARGAR HABITACIÓN ASIGNADA
            document.getElementById('inputHabAsignada').value = props.id_habitacion_asignada || 0;

            if (props.estado === 'confirmada') {
                document.getElementById('bloquePendiente').style.display = 'none';
                document.getElementById('bloqueConfirmado').style.display = 'block';
                document.getElementById('txtMonto').innerText = props.pago_monto; 
                document.getElementById('txtMetodo').innerText = props.metodo; 
                
                let habAsig = props.id_habitacion_asignada;
                if(habAsig && habAsig != 0) {
                    let nombreHab = "Asignada";
                    for(let h of listaHabitaciones){ if(h.id == habAsig) nombreHab = h.nombre; }
                    document.getElementById('txtAsignada').innerText = "🏠 Asignada a: " + nombreHab;
                } else {
                    document.getElementById('txtAsignada').innerText = "";
                }

            } else {
                document.getElementById('bloquePendiente').style.display = 'block';
                document.getElementById('bloqueConfirmado').style.display = 'none';
                document.getElementById('inputMetodo').value = "";
                document.getElementById('inputMontoReal').value = "";
            }
            modalG.style.display = "block";
        }
        function cerrarModalGestion() { modalG.style.display = "none"; }

        function procesarReserva(accion) {
            let id = document.getElementById('idReservaActual').value;
            let metodo = document.getElementById('inputMetodo').value;
            let monto_real = document.getElementById('inputMontoReal').value;
            let pago_nota = document.getElementById('inputPagoNota').value;
            let hora = document.getElementById('inputHora').value;
            let hab_asignada = document.getElementById('inputHabAsignada').value;

            // --- VALIDACIÓN DE CAJA ---
            if(accion === 'confirmar') {
                if(!CAJA_ABIERTA) {
                    alert("⛔ CAJA CERRADA\n\nNo puedes confirmar pagos si la caja está cerrada.");
                    return; 
                }
                if(metodo === "") { alert("Selecciona Método de Pago"); return; }
                if(monto_real === "") { alert("Ingresa el monto cobrado"); return; }
            }
            
            if(accion === 'cancelar' && !confirm("¿Eliminar reserva?")) return;

            let formData = new FormData();
            formData.append('id', id);
            formData.append('accion', accion);
            formData.append('metodo', metodo);
            formData.append('monto_real', monto_real);
            formData.append('pago_nota', pago_nota);
            formData.append('hora', hora);
            formData.append('id_habitacion_asignada', hab_asignada);

            fetch('cambiar_estado.php', { method: 'POST', body: formData })
            .then(r => r.text())
            .then(data => {
                if(data.trim() == 'ok') {
                    alert(accion === 'confirmar' ? "¡Reserva Confirmada!" : "Reserva Eliminada.");
                    cerrarModalGestion();
                    window.miCalendario.refetchEvents();
                } else { alert("Error: " + data); }
            });
        }

        var modalC = document.getElementById("modalCrear");
        function abrirModalCrear(fechaInicio = '') {
            document.getElementById('formCrearReserva').reset();
            document.getElementById('boxCotizacion').style.display = 'none';
            if(fechaInicio) {
                document.getElementById('crearLlegada').value = fechaInicio;
                let f = new Date(fechaInicio); f.setDate(f.getDate() + 2);
                document.getElementById('crearSalida').value = f.toISOString().split('T')[0];
                calcularCotizacion();
            }
            modalC.style.display = "block";
        }

        function guardarReservaManual(e) {
            e.preventDefault();
            // AQUÍ SÍ PERMITIMOS CREAR SIN CAJA (PENDIENTE)
            let formData = new FormData(document.getElementById('formCrearReserva'));
            fetch('crear_reserva_manual.php', { method: 'POST', body: formData })
            .then(r => r.text())
            .then(data => {
                if(data.trim() == 'ok') {
                    alert("¡Reserva Creada (Pendiente)!");
                    modalC.style.display = 'none';
                    window.miCalendario.refetchEvents();
                } else { alert("Error: " + data); }
            });
        }

        window.onclick = function(e) { 
            if (e.target == modalG) modalG.style.display = "none"; 
            if (e.target == modalC) modalC.style.display = "none"; 
        }
    </script>
</body>
</html>