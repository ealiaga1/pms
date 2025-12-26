<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima'); 

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

$rol_usuario = $_SESSION['rol']; 
$usuario = $_SESSION['usuario'];

$qUser = mysqli_query($conexion, "SELECT id FROM usuarios_admin WHERE usuario = '$usuario'");
$fUser = mysqli_fetch_assoc($qUser);
$id_usuario = $fUser['id'];

$qCaja = mysqli_query($conexion, "SELECT id FROM caja_sesiones WHERE id_usuario = '$id_usuario' AND estado = 'abierta'");
$tiene_caja = (mysqli_num_rows($qCaja) > 0) ? 'true' : 'false'; 

// 2. CONSULTA RACK (Agregamos 'e.descuento')
$sql = "SELECT h.*, e.nombre_huesped, e.fecha_ingreso, e.total_consumos, e.adelanto, e.descuento 
        FROM habitaciones h 
        LEFT JOIN estancias e ON h.id = e.id_habitacion AND e.estado = 'activa'
        ORDER BY h.nombre ASC";
$resultado = mysqli_query($conexion, $sql);

// 3. PRODUCTOS
$res_prod = mysqli_query($conexion, "SELECT * FROM productos ORDER BY nombre ASC");
$lista_productos = array();
while($p = mysqli_fetch_assoc($res_prod)){ $lista_productos[] = $p; }

// 4. RESERVAS WEB HOY
$hoy = date('Y-m-d');
$sqlRes = "SELECT * FROM reservaciones WHERE estado='confirmada' AND fecha_llegada = '$hoy'";
$resRes = mysqli_query($conexion, $sqlRes);
$reservas_hoy = array();
while($r = mysqli_fetch_assoc($resRes)){ $reservas_hoy[] = $r; }

// 5. TEMPORADAS
$sqlTemp = "SELECT * FROM temporadas";
$resTemp = mysqli_query($conexion, $sqlTemp);
$temporadas = array();
while($t = mysqli_fetch_assoc($resTemp)){ $temporadas[] = $t; }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control Hotelero | Tulumayo Lodge</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #ecf0f1; margin: 0; display: flex; height: 100vh; }
        .sidebar { width: 250px; background: #2c3e50; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { padding: 20px; background: #1a252f; margin: 0; font-size: 1.1rem; text-align: center; }
        .sidebar a { padding: 15px 20px; color: white; text-decoration: none; border-bottom: 1px solid #34495e; display: block; }
        .sidebar a:hover, .sidebar a.active { background: #34495e; color: white; }
        
        .main { flex: 1; padding: 30px; overflow-y: auto; }
        .header-main { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
        
        .rack-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 20px; }
        
        .room-card { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); text-align: center; transition: transform 0.2s; cursor: pointer; position: relative; border-top: 5px solid #bdc3c7; }
        .room-card:hover { transform: translateY(-5px); }
        
        .status-disponible { border-color: #2ecc71; } 
        .status-disponible .icon-box { color: #2ecc71; background: #eafaf1; }
        
        .status-ocupada { border-color: #e74c3c; } 
        .status-ocupada .icon-box { color: #e74c3c; background: #fdedec; }

        .status-sucia { border-color: #f1c40f; } 
        .status-sucia .icon-box { color: #f1c40f; background: #fcf3cf; }

        .status-mantenimiento { border-color: #7f8c8d; background: #f4f6f7; }
        .status-mantenimiento .icon-box { color: #7f8c8d; background: #e5e8e8; }
        .status-mantenimiento .room-name { color: #7f8c8d; }

        .icon-box { width: 60px; height: 60px; border-radius: 50%; margin: 0 auto 15px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .room-name { font-weight: 700; color: #34495e; margin-bottom: 5px; }
        .room-status { font-size: 0.8rem; text-transform: uppercase; letter-spacing: 1px; color: #7f8c8d; }
        .room-price { margin-top: 10px; font-weight: 600; color: #2c3e50; }
        .price-high { color: #e67e22; font-weight: bold; } 
        
        .guest-active { color: #c0392b; font-weight: bold; font-size: 0.9rem; margin-top: 5px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 100; }
        .modal-content { background: white; width: 500px; margin: 2% auto; padding: 25px; border-radius: 8px; position: relative; max-height: 95vh; overflow-y: auto; }

        .info-pago-reserva { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 15px; display: none; font-size: 0.95rem; }
        .alerta-caja { background: #e74c3c; color: white; padding: 15px; border-radius: 5px; text-align: center; margin-bottom: 15px; }
        .alerta-caja a { color: #fff; font-weight: bold; text-decoration: underline; }
        .lista-detallada { background: #fff; border: 1px solid #eee; margin-top: 5px; max-height: 150px; overflow-y: auto; display: none; }
        .lista-detallada div { padding: 5px 10px; border-bottom: 1px solid #f9f9f9; font-size: 0.85rem; display: flex; justify-content: space-between; }
        .btn-ver-detalle { cursor: pointer; color: #3498db; font-weight: bold; font-size: 0.8rem; text-decoration: underline; margin-left: 5px; }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main">
        <div class="header-main">
            <h1>Estado de Habitaciones</h1>
            <?php if($tiene_caja == 'true'): ?>
                <span style="color: #2ecc71; font-weight: bold;"><i class="fas fa-check-circle"></i> Caja Abierta</span>
                <button onclick="abrirModalVentaDirecta()" style="padding: 10px 20px; background: #8e44ad; color: white; border: none; border-radius: 5px; margin-left: 10px; font-weight: bold; cursor: pointer;"><i class="fas fa-shopping-cart"></i> Venta Mostrador</button>
            <?php else: ?>
                <span style="color: #e74c3c; font-weight: bold;"><i class="fas fa-exclamation-circle"></i> Caja Cerrada</span>
            <?php endif; ?>
            <button onclick="location.reload()" style="padding: 10px 20px; background: #2c3e50; color: white; border: none; border-radius: 5px; margin-left:10px;"><i class="fas fa-sync"></i> Actualizar</button>
        </div>

        <div class="rack-grid">
            <?php while($hab = mysqli_fetch_assoc($resultado)) { 
                // --- A. CALCULAR TEMPORADA ---
                $precio_hoy = $hab['precio_noche'];
                $es_temporada_alta = false;
                $hoy_md = date('m-d');

                foreach($temporadas as $t) {
                    if ($hoy_md >= $t['inicio'] && $hoy_md <= $t['fin']) {
                        $precio_hoy = $hab['precio_alta']; 
                        $es_temporada_alta = true;
                        break; 
                    }
                }
                if($precio_hoy <= 0) $precio_hoy = $hab['precio_noche'];

                // --- B. AVISO RESERVA FUTURA ---
                $aviso_reserva = "";
                $hoy_sql = date('Y-m-d');
                $sqlProx = "SELECT fecha_llegada, fecha_salida, nombre_cliente FROM reservaciones WHERE id_habitacion_asignada = '".$hab['id']."' AND estado = 'confirmada' AND fecha_llegada >= '$hoy_sql' ORDER BY fecha_llegada ASC LIMIT 1";
                $resProx = mysqli_query($conexion, $sqlProx);
                $prox = mysqli_fetch_assoc($resProx);

                if ($prox) {
                    $llegada = date("d/m", strtotime($prox['fecha_llegada']));
                    $salida  = date("d/m", strtotime($prox['fecha_salida']));
                    $nombre  = substr($prox['nombre_cliente'], 0, 15) . "..";
                    $estilo = ($prox['fecha_llegada'] == $hoy_sql) ? "background:#ffe0b2; color:#e65100; border:1px solid #ffcc80;" : "background:#e8f5e9; color:#2e7d32; border:1px solid #c8e6c9;";
                    $aviso_reserva = "<div style='font-size:0.75rem; padding:4px; border-radius:4px; margin-top:8px; $estilo text-align:center; line-height:1.3;'><div>📅 <strong>$llegada</strong> al <strong>$salida</strong></div><div style='margin-top:1px;'><i class='fas fa-user'></i> $nombre</div></div>";
                }

                $cssClass = "status-disponible"; $icono = "fa-door-open"; $textoEstado = "Disponible"; $infoExtra = "";
                
                $dias_calc = 0; $total_hab_calc = 0; $consumos_calc = 0; 
                $adelanto_calc = 0; $deuda_total = 0; $fecha_ingreso_fmt = "";
                $descuento_calc = 0;

                if ($hab['estado'] == 'ocupada') {
                    $cssClass = "status-ocupada"; $icono = "fa-user-check"; $textoEstado = "Ocupada";
                    $infoExtra = '<div class="guest-active"><i class="fas fa-user"></i> '.$hab['nombre_huesped'].'</div>';

                    $inicio = new DateTime($hab['fecha_ingreso']); $inicio->setTime(0, 0, 0); 
                    $fin = new DateTime(); $fin->setTime(0, 0, 0); 
                    $diff = $inicio->diff($fin);
                    $dias_calc = $diff->days; if($dias_calc == 0) $dias_calc = 1; 

                    $total_hab_calc = $dias_calc * $precio_hoy;
                    $consumos_calc = $hab['total_consumos'];
                    $adelanto_calc = $hab['adelanto'];
                    $descuento_calc = $hab['descuento'] ? $hab['descuento'] : 0; 

                    // RESTAMOS DESCUENTO DEL TOTAL
                    $deuda_total = ($total_hab_calc + $consumos_calc) - $adelanto_calc - $descuento_calc;
                    $fecha_ingreso_fmt = date("d/m/Y H:i", strtotime($hab['fecha_ingreso']));
                    
                } elseif ($hab['estado'] == 'sucia') {
                    $cssClass = "status-sucia"; $icono = "fa-broom"; $textoEstado = "Limpieza";
                    
                } elseif ($hab['estado'] == 'mantenimiento') {
                    $cssClass = "status-mantenimiento"; $icono = "fa-tools"; $textoEstado = "Mantenimiento";
                }
            ?>
                <div class="room-card <?php echo $cssClass; ?>" 
                     onclick="gestionarHabitacion(
                        <?php echo $hab['id']; ?>, 
                        '<?php echo $hab['estado']; ?>', 
                        '<?php echo $hab['nombre']; ?>', 
                        <?php echo $precio_hoy; ?>, 
                        <?php echo $dias_calc; ?>, 
                        <?php echo $consumos_calc; ?>, 
                        <?php echo $adelanto_calc; ?>, 
                        <?php echo $deuda_total; ?>, 
                        '<?php echo $fecha_ingreso_fmt; ?>',
                        <?php echo $descuento_calc; ?> 
                     )">
                    <div class="icon-box"><i class="fas <?php echo $icono; ?>"></i></div>
                    <div class="room-name"><?php echo $hab['nombre']; ?></div>
                    <div class="room-status"><?php echo $textoEstado; ?></div>
                    <?php echo $infoExtra; ?>
                    
                    <?php if($hab['estado'] == 'ocupada'): ?>
                        <div style="font-size:0.8rem; color:#e74c3c; font-weight:bold; margin-top:5px;">Debe: S/ <?php echo number_format($deuda_total, 2); ?></div>
                    <?php else: ?>
                        <div class="room-price <?php echo $es_temporada_alta ? 'price-high' : ''; ?>">
                            <?php if($es_temporada_alta) echo '<i class="fas fa-fire"></i> '; ?>
                            S/ <?php echo number_format($precio_hoy, 2); ?>
                        </div>
                    <?php endif; ?>
                    <?php echo $aviso_reserva; ?>
                </div>
            <?php } ?>
        </div>
    </div>

    <!-- MODALES (Gestión y Venta) -->
    <div id="modalHotel" class="modal">
        <div class="modal-content">
            <span onclick="document.getElementById('modalHotel').style.display='none'" style="float:right; cursor:pointer; font-size:1.5rem;">&times;</span>
            <h2 id="tituloModal">Gestión Habitación</h2>
            <div id="contenidoModal"></div>
        </div>
    </div>
    
    <div id="modalVentaDirecta" class="modal">
        <div class="modal-content" style="border-top: 5px solid #8e44ad;">
            <span onclick="document.getElementById('modalVentaDirecta').style.display='none'" style="float:right; cursor:pointer; font-size:1.5rem;">&times;</span>
            <h2 style="color: #8e44ad;">Venta de Mostrador</h2>
            <form action="procesar_venta_directa.php" method="POST">
                <label>Producto:</label>
                <select id="selectProductoDirecto" name="producto" onchange="actualizarPrecioDirecto()" style="width:100%; padding:10px; margin-bottom:10px;">
                    <option value="">-- Seleccionar --</option>
                    <?php foreach($lista_productos as $p): ?>
                        <option value="<?php echo $p['nombre']; ?>" data-precio="<?php echo $p['precio']; ?>"><?php echo $p['nombre']; ?> - S/ <?php echo $p['precio']; ?></option>
                    <?php endforeach; ?>
                </select>
                <label>Precio (S/):</label>
                <input type="number" id="inputPrecioDirecto" name="monto" step="0.50" readonly style="width:100%; padding:10px; margin-bottom:10px;">
                <label>Método de Pago:</label>
                <select name="metodo_pago" required style="width:100%; padding:10px; margin-bottom:20px;">
                    <option value="Efectivo">Efectivo</option><option value="Yape">Qr IziPay</option><option value="Tarjeta">Tarjeta</option>
                </select>
                <button type="submit" style="width:100%; padding:12px; background:#8e44ad; color:white; border:none; font-weight:bold; cursor:pointer;">COBRAR</button>
            </form>
        </div>
    </div>

    <!-- SCRIPT -->
    <?php if(isset($_GET['venta']) && $_GET['venta'] == 'ok'): ?>
    <script>alert("✅ ¡VENTA REGISTRADA CON ÉXITO!\nSe sumó a la Caja."); if(window.history.replaceState){window.history.replaceState(null,null,window.location.pathname);}</script>
    <?php endif; ?>

    <script>
        const CAJA_ABIERTA = <?php echo $tiene_caja; ?>;
        const ES_ADMIN = <?php echo ($rol_usuario == 'admin') ? 'true' : 'false'; ?>; 
        const productosDb = <?php echo json_encode($lista_productos); ?>;
        const reservasHoy = <?php echo json_encode($reservas_hoy); ?>;
        let precioHabitacionActual = 0; 

        function gestionarHabitacion(id, estado, nombre, precio, dias, consumos, adelanto, total, fechaIngreso, descuento) {
            let modal = document.getElementById('modalHotel');
            let contenido = document.getElementById('contenidoModal');
            document.getElementById('tituloModal').innerText = nombre;
            precioHabitacionActual = precio;
            modal.style.display = 'block';

            // --- CASO 1: DISPONIBLE (CHECK-IN) ---
            if (estado === 'disponible') {
                let opcionesReservas = '<option value="">-- Huésped Directo --</option>';
                reservasHoy.forEach((res, index) => {
                    opcionesReservas += `<option value="${index}">Reserva Web: ${res.nombre_cliente}</option>`;
                });

                let btnAveria = `<div style="text-align:right; margin-bottom:10px;"><a href="#" onclick="mostrarFormMantenimiento(${id})" style="color:#e74c3c; font-size:0.8rem;"><i class="fas fa-tools"></i> Reportar Avería</a></div>`;

                contenido.innerHTML = btnAveria + `
                    <h3 style="color: #2ecc71;">Check-in (Registro)</h3>
                    <label style="font-weight:bold; color:#2c3e50;">¿Viene con Reserva Web?</label>
                    <select id="selectReserva" onchange="cargarDatosReserva()" style="width:100%; padding:8px; margin-bottom:10px; border:1px solid #2ecc71;">${opcionesReservas}</select>
                    <div id="infoPago" class="info-pago-reserva"></div>

                    <form action="procesar_hotel.php" method="POST">
                        <input type="hidden" name="accion" value="checkin">
                        <input type="hidden" name="id_habitacion" value="${id}">
                        <input type="hidden" name="id_reserva_web" id="inputIdReservaWeb" value="">
                        
                        <!-- ADELANTO EN CHECK-IN -->
                        <div style="background:#e8f5e9; padding:10px; border:1px solid #a5d6a7; border-radius:5px; margin-bottom:10px;">
                            <label style="font-weight:bold; color:#2e7d32;">Pago a Cuenta / Adelanto (S/):</label>
                            <input type="number" name="adelanto" id="inputAdelanto" value="0.00" step="0.50" style="width:100%; padding:8px; font-weight:bold;">
                        </div>

                        <!-- DATOS HUÉSPED -->
                        <div style="background:#f0f9ff; padding:10px; border-radius:5px; margin-bottom:10px; border:1px solid #bce0fd;">
                            <label style="font-weight:bold; font-size:0.9rem;">Datos del Huésped Principal:</label>
                            <input type="text" id="inputHuesped" name="huesped" required placeholder="Nombre Completo" style="width:100%; padding:5px; margin-bottom:5px;">
                            <div style="display:flex; gap:5px; margin-bottom:5px;">
                                <select name="tipo_doc" style="padding:5px;"><option>DNI</option><option>PASAPORTE</option><option>CE</option></select>
                                <input type="text" name="num_doc" placeholder="N° Documento" style="flex:1; padding:5px;">
                            </div>
                            
                            <!-- NUEVOS CAMPOS: CELULAR Y CORREO -->
                            <div style="display:flex; gap:5px;">
                                <input type="text" id="inputTelefono" name="telefono" placeholder="Celular / WhatsApp" style="flex:1; padding:5px;">
                                <input type="email" id="inputEmail" name="email" placeholder="Correo Electrónico" style="flex:1; padding:5px;">
                            </div>
                        </div>

                        <!-- DATOS MINCETUR (Simplificados) -->
                        <div style="background:#fff3cd; padding:10px; border-radius:5px; margin-bottom:10px; border:1px solid #ffeeba;">
                            <label style="font-weight:bold; font-size:0.8rem; color:#856404;">DATOS ESTADÍSTICOS (MINCETUR):</label>
                            <div style="display:flex; gap:5px; margin-bottom:5px;">
                                <div style="flex:1;"><label style="font-size:0.8rem;">N° Personas:</label><input type="number" name="nro_personas" value="2" min="1" style="width:100%; padding:5px;"></div>
                                <div style="flex:1;"><label style="font-size:0.8rem;">Motivo:</label><select name="motivo_viaje" style="width:100%; padding:5px;"><option value="Vacaciones">Vacaciones</option><option value="Trabajo">Trabajo/Negocios</option><option value="Salud">Salud</option><option value="Visita">Visita Familia</option></select></div>
                            </div>
                            <label style="font-size:0.8rem;">Procedencia:</label>
                            <div style="display:flex; gap:5px;"><select name="procedencia" id="selProcedencia" style="padding:5px;"><option value="peru">Perú (Región)</option><option value="extranjero">Extranjero (País)</option></select><input type="text" name="lugar_origen" placeholder="Ej: Lima / USA" required style="flex:1; padding:5px;"></div>
                        </div>

                        <!-- FACTURA EMPRESA -->
                        <div style="margin-bottom:10px;"><label style="font-weight:bold; color:#2c3e50; cursor:pointer;"><input type="checkbox" name="es_empresa" id="checkEmpresa" value="1" onchange="toggleEmpresa()"> ¿Requiere Factura a Empresa?</label></div>
                        <div id="boxEmpresa" style="display:none; background:#e3f2fd; padding:10px; border-radius:5px; margin-bottom:10px; border:1px solid #90caf9;">
                            <label style="font-weight:bold; font-size:0.9rem; color:#1565c0;"><i class="fas fa-building"></i> Datos de Facturación:</label>
                            <label style="font-size:0.8rem; display:block; margin-top:5px;">RUC:</label><input type="number" name="ruc_empresa" placeholder="20..." style="width:100%; padding:5px;">
                            <label style="font-size:0.8rem; display:block; margin-top:5px;">Razón Social:</label><input type="text" name="razon_social" placeholder="Ej. Transportes SAC" style="width:100%; padding:5px;">
                            <label style="font-size:0.8rem; display:block; margin-top:5px;">Dirección Fiscal:</label><input type="text" name="direccion" placeholder="Av. Principal 123" style="width:100%; padding:5px;">
                        </div>
                        <button type="submit" style="width:100%; padding:12px; background:#2ecc71; color:white; border:none; border-radius:5px; font-weight:bold;">REGISTRAR INGRESO</button>
                    </form>
                `;
            } else if (estado === 'sucia') {
                contenido.innerHTML = `<div style='text-align:center; padding:20px;'><i class='fas fa-broom' style='font-size:3rem; color:#f1c40f;'></i><h3>Limpieza</h3><button onclick="location.href='procesar_hotel.php?accion=limpiar&id=${id}'" style='padding:10px 20px; background:#f1c40f; border:none; cursor:pointer; font-weight:bold;'>MARCAR LIMPIA</button><br><br><a href="#" onclick="mostrarFormMantenimiento(${id})" style="color:#e74c3c; font-size:0.8rem;">Reportar Daño</a></div>`;
            } else if (estado === 'mantenimiento') {
                contenido.innerHTML = `<div style="text-align:center; padding:20px;"><h3>En Reparación</h3><button onclick="if(confirm('¿Listo?')) location.href='procesar_hotel.php?accion=finalizar_mantenimiento&id_habitacion=${id}'">REPARACIÓN TERMINADA</button></div>`;
            } else {
                // --- CASO 4: OCUPADA ---
                let fTotal = parseFloat(total).toFixed(2);
                let fHab = (dias * precio).toFixed(2);
                let fConsumos = parseFloat(consumos).toFixed(2);
                let fAdelanto = parseFloat(adelanto).toFixed(2);
                let fDescuento = parseFloat(descuento).toFixed(2);

                // --- BOTONES (INCLUYENDO ADELANTO) ---
                let botonesAccion = `
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px; margin-bottom:10px;">
                        <button onclick="cargarVenta(${id})" style="padding:10px; background:#3498db; color:white; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">
                            <i class="fas fa-utensils"></i> Consumo
                        </button>
                        
                        <!-- BOTÓN NUEVO: ADELANTO -->
                        <button onclick="mostrarFormAdelanto()" style="padding:10px; background:#27ae60; color:white; border:none; border-radius:5px; cursor:pointer; font-weight:bold;">
                            <i class="fas fa-money-bill-wave"></i> + Adelanto
                        </button>
                    </div>
                `;

                // --- FORMULARIO OCULTO DE ADELANTO ---
                let formAdelantoHTML = `
                    <div id="formAdelantoBox" style="display:none; background:#e8f5e9; padding:10px; border-radius:5px; margin-bottom:15px; border:1px solid #a5d6a7;">
                        <h5 style="margin:0 0 5px 0; color:#2e7d32;">Registrar Pago a Cuenta</h5>
                        <form action="procesar_hotel.php" method="POST">
                            <input type="hidden" name="accion" value="agregar_adelanto">
                            <input type="hidden" name="id_habitacion" value="${id}">
                            <div style="display:flex; gap:5px;">
                                <input type="number" name="monto_adelanto" placeholder="Monto S/" required step="0.50" style="flex:1; padding:5px; border:1px solid #ccc; border-radius:3px;">
                                <select name="metodo_pago" style="flex:1; padding:5px; border:1px solid #ccc; border-radius:3px;">
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Yape">Yape</option>
                                    <option value="Tarjeta">Tarjeta</option>
                                </select>
                            </div>
                            <button type="submit" style="width:100%; margin-top:5px; background:#2e7d32; color:white; border:none; padding:8px; border-radius:3px; cursor:pointer;">GUARDAR PAGO</button>
                        </form>
                    </div>
                `;

                let alertaCaja = (!CAJA_ABIERTA) ? `<div class='alerta-caja' style='margin-bottom:15px;'><strong>⚠️ ¡Caja Cerrada!</strong><br><a href='caja.php' style='color:white;'>ABRIR CAJA</a></div>` : "";
                
                let seccionCobro = `
                    <div style="background:#eee; padding:10px; border-radius:5px; margin-bottom:10px;">
                        <label style="font-weight:bold; display:block; margin-bottom:5px; font-size:0.9rem;">Método de Pago Saldo:</label>
                        <select id="selMetodoSalida" style="width:100%; padding:8px; border:1px solid #ccc; border-radius:4px; margin-bottom:10px;">
                            <option value="Efectivo">Efectivo</option><option value="Yape">QR IziPay</option><option value="Tarjeta">Tarjeta</option><option value="Transferencia">Transferencia</option>
                        </select>
                        <div style="display:grid; grid-template-columns: 1fr 1fr; gap:10px;">
                           
                            <button onclick="realizarCheckout(${id})" style="padding:10px; background:#e74c3c; color:white; border:none; border-radius:5px; cursor:pointer; font-weight:bold;"><i class="fas fa-sign-out-alt"></i> Cobrar y Salir</button>
                  </div>
                        
                        <!-- BOTÓN EXTRA PARA ERRORES -->
                        <div style="text-align:center; margin-top:15px;">
                             <a href="#" onclick="forzarLimpieza(${id})" style="color:#7f8c8d; font-size:0.8rem; text-decoration:underline;">Cancelar Ingreso / Enviar a Limpieza</a>
                        </div>
                    </div>
                    <div id="areaVenta" style="margin-top:20px; border-top:1px solid #eee; padding-top:10px;"></div>
                `;
                
                // Si la caja está cerrada, no mostramos botones de cobro/adelanto
                let botonesFinales = (!CAJA_ABIERTA) ? alertaCaja : (botonesAccion + formAdelantoHTML + seccionCobro);

                // HTML INPUT DESCUENTO
                let inputDescuentoHTML = "";
                if(ES_ADMIN) {
                    inputDescuentoHTML = `
                        <div style="display:flex; justify-content:space-between; font-size:0.9rem; margin-bottom:10px; color:#e67e22; align-items:center;">
                            <span>(-) Descuento (Admin):</span>
                            <input type="number" id="inputDescuento" value="${fDescuento}" 
                                   onchange="actualizarTotalConDescuento(${fHab}, ${fConsumos}, ${fAdelanto})" 
                                   style="width:80px; text-align:right; font-weight:bold; color:#e67e22; border:1px solid #e67e22;">
                        </div>
                    `;
                } else if (descuento > 0) {
                     inputDescuentoHTML = `
                        <div style="display:flex; justify-content:space-between; font-size:0.9rem; margin-bottom:10px; color:#e67e22;">
                            <span>(-) Descuento:</span><strong>- S/ ${fDescuento}</strong>
                            <input type="hidden" id="inputDescuento" value="${fDescuento}">
                        </div>
                    `;
                } else {
                     inputDescuentoHTML = `<input type="hidden" id="inputDescuento" value="0">`;
                }

                let htmlPreCuenta = `
                    <div style="background: #fdfdfd; border: 1px solid #ddd; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        <h4 style="margin-top:0; color:#555; text-align:center; border-bottom:1px solid #eee; padding-bottom:10px;">
                            <i class="fas fa-file-invoice-dollar"></i> Pre-Cuenta Actual
                        </h4>
                        
                        <div style="text-align:center; margin-bottom:10px; color:#2980b9; font-size:0.9rem;">
                            <i class="fas fa-clock"></i> Ingresó: <strong>${fechaIngreso}</strong>
                        </div>

                        <div style="display:flex; justify-content:space-between; font-size:0.9rem; margin-bottom:5px;">
                            <span>Alojamiento (${dias} n):</span>
                            <strong>S/ ${fHab}</strong>
                        </div>
                        
                        <div style="display:flex; justify-content:space-between; font-size:0.9rem; margin-bottom:5px;">
                            <span>Consumos / Extras: 
                                <a href="javascript:void(0)" onclick="verDetalleConsumos(${id})" style="color:#3498db; text-decoration:underline; font-weight:bold; margin-left:5px;">(Ver Detalle)</a>
                            </span>
                            <strong>S/ ${fConsumos}</strong>
                        </div>
                        
                        <div id="listaDetalle_${id}" class="lista-detallada" style="display:none; border:1px solid #eee; background:#fff; padding:5px; margin-bottom:5px; font-size:0.85rem;">
                            Cargando...
                        </div>

                        <div style="display:flex; justify-content:space-between; font-size:0.9rem; margin-bottom:10px; color:#27ae60;">
                            <span>(-) Adelantos:</span>
                            <strong>- S/ ${fAdelanto}</strong>
                        </div>
                        
                        ${inputDescuentoHTML}

                        <div style="display:flex; justify-content:space-between; font-size:1.2rem; font-weight:bold; border-top:2px solid #333; padding-top:10px;">
                            <span>TOTAL A PAGAR:</span>
                            <span style="color:#c0392b;" id="txtTotalPagar">S/ ${fTotal}</span>
                        </div>
                    </div>
                `;
                contenido.innerHTML = htmlPreCuenta + botonesFinales;
            }
        }       

        function actualizarTotalConDescuento(hab, cons, adel) {
            let desc = parseFloat(document.getElementById('inputDescuento').value) || 0;
            let total = (parseFloat(hab) + parseFloat(cons) - parseFloat(adel) - desc);
            document.getElementById('txtTotalPagar').innerText = "S/ " + total.toFixed(2);
        }

        // Resto de funciones auxiliares...
        function verDetalleConsumos(idHab) {
            let caja = document.getElementById('listaDetalle_' + idHab);
            if(caja.style.display === 'block') { caja.style.display = 'none'; return; }
            caja.style.display = 'block';
            caja.innerHTML = '<div style="padding:10px; color:#999;">Buscando...</div>';
            fetch('api_consumos_habitacion.php?id_habitacion=' + idHab)
                .then(r => r.json())
                .then(data => {
                    let html = '';
                    if (data.length > 0) { data.forEach(item => { html += `<div><span>${item.detalle}</span><strong>S/ ${item.monto}</strong></div>`; }); } 
                    else { html = '<div style="padding:10px; text-align:center;">Sin consumos.</div>'; }
                    caja.innerHTML = html;
                });
        }
        function cargarDatosReserva() {
            let index = document.getElementById('selectReserva').value;
            let divInfo = document.getElementById('infoPago');
            let inputNombre = document.getElementById('inputHuesped');
            let inputAdelanto = document.getElementById('inputAdelanto');
            let inputIdRes = document.getElementById('inputIdReservaWeb'); 
            let inputTel = document.getElementById('inputTelefono');
            let inputEmail = document.getElementById('inputEmail');

            if (index === "") { 
                divInfo.style.display = 'none'; inputNombre.value = ""; inputAdelanto.value = 0; inputIdRes.value = ""; 
                if(inputTel) inputTel.value = ""; if(inputEmail) inputEmail.value = ""; return; 
            }
            let reserva = reservasHoy[index];
            inputNombre.value = reserva.nombre_cliente;
            inputAdelanto.value = reserva.pago_monto;
            inputIdRes.value = reserva.id; 
            if(inputTel) inputTel.value = reserva.telefono || "";
            if(inputEmail) inputEmail.value = reserva.email || "";

            divInfo.innerHTML = `<strong style="color:#155724;">✅ RESERVA CONFIRMADA (ID: ${reserva.id})</strong><br>Adelanto: S/ ${reserva.pago_monto}`;
            divInfo.style.display = 'block';
        }
        function cargarVenta(idHabitacion) {
            let opciones = '';
            productosDb.forEach(prod => { opciones += `<option value="${prod.nombre}" data-precio="${prod.precio}">${prod.nombre} - S/ ${prod.precio}</option>`; });
            document.getElementById('areaVenta').innerHTML = `<h4>Agregar Consumo</h4><form action="procesar_hotel.php" method="POST"><input type="hidden" name="accion" value="venta"><input type="hidden" name="id_habitacion" value="${idHabitacion}"><label>Producto:</label><select id="selectProducto" name="producto" onchange="actualizarPrecio()" style="width:100%; padding:8px;">${opciones}</select><label>Precio:</label><input type="number" id="inputPrecioVenta" name="monto" step="0.50" readonly style="width:100%; padding:8px; margin-bottom:10px;"><button type="submit" style="width:100%; padding:10px; background:#3498db; color:white; border:none;">CARGAR</button></form>`;
            actualizarPrecio();
        }
        function actualizarPrecio() {
            let select = document.getElementById('selectProducto');
            let precio = select.options[select.selectedIndex].getAttribute('data-precio');
            document.getElementById('inputPrecioVenta').value = precio;
        }
        function actualizarPrecioDirecto() {
            let select = document.getElementById('selectProductoDirecto');
            let precio = select.options[select.selectedIndex].getAttribute('data-precio');
            document.getElementById('inputPrecioDirecto').value = precio;
        }
        function realizarCheckout(id) {
            let metodo = document.getElementById('selMetodoSalida').value;
            let inputDesc = document.getElementById('inputDescuento');
            let descuento = inputDesc ? inputDesc.value : 0;
            if(confirm("¿Confirmar pago con " + metodo + " y liberar habitación?")) {
                window.location.href = `procesar_hotel.php?accion=checkout&id=${id}&metodo=${metodo}&descuento=${descuento}`;
            }
        }
        function abrirModalVentaDirecta() {
            let select = document.getElementById('selectProductoDirecto');
            let opciones = '<option value="">-- Seleccionar --</option>';
            productosDb.forEach(prod => { opciones += `<option value="${prod.nombre}" data-precio="${prod.precio}">${prod.nombre} - S/ ${prod.precio}</option>`; });
            select.innerHTML = opciones;
            document.getElementById('modalVentaDirecta').style.display = 'block';
        }
        function mostrarFormMantenimiento(id) {
            let contenido = document.getElementById('contenidoModal');
            contenido.innerHTML = `<h3 style="color:#e74c3c;">Reportar Avería</h3><form action="procesar_hotel.php" method="POST"><input type="hidden" name="accion" value="reportar_averia"><input type="hidden" name="id_habitacion" value="${id}"><label>Descripción:</label><textarea name="descripcion" rows="3" required style="width:100%; padding:10px; border:1px solid #ccc;"></textarea><label>Prioridad:</label><select name="prioridad" style="width:100%; padding:10px; margin-bottom:15px;"><option value="Alta">Alta</option><option value="Media" selected>Media</option><option value="Baja">Baja</option></select><button type="submit" style="width:100%; padding:12px; background:#e74c3c; color:white; border:none; font-weight:bold;">BLOQUEAR</button></form><button onclick="location.reload()" style="margin-top:10px; background:none; border:none; color:#555; cursor:pointer;">Cancelar</button>`;
        }
        function toggleEmpresa() {
            var check = document.getElementById("checkEmpresa");
            var box = document.getElementById("boxEmpresa");
            if (check.checked) { box.style.display = "block"; } else { box.style.display = "none"; }
        }
        window.onclick = function(e) { if (e.target == document.getElementById('modalHotel')) document.getElementById('modalHotel').style.display='none'; }
        
        // --- FUNCIÓN MOSTRAR FORM ADELANTO (NECESARIA) ---
        function mostrarFormAdelanto() {
            let box = document.getElementById('formAdelantoBox');
            if (box.style.display === 'none') {
                box.style.display = 'block';
            } else {
                box.style.display = 'none';
            }
        }
        function forzarLimpieza(id) {
            if(confirm("¿Seguro? Esto cancelará la estadía actual SIN COBRAR y mandará la habitación a limpieza.")) {
                // Usamos la misma acción de checkout pero podríamos crear una de cancelación si quisiéramos ser más estrictos
                // Por ahora, usaremos checkout para cerrar la estancia y marcar sucia.
                window.location.href = `procesar_hotel.php?accion=checkout&id=${id}&metodo=Cancelado&descuento=0`;
            }
        }
    </script>
</body>
</html>