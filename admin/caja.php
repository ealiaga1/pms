<?php
session_start();
include '../db.php';
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// 1. Datos Usuario
$usuario_log = $_SESSION['usuario'];
$qUser = mysqli_query($conexion, "SELECT id, nombre_completo FROM usuarios_admin WHERE usuario = '$usuario_log'");
$dUser = mysqli_fetch_assoc($qUser);
$id_usuario = $dUser['id'];
$nombre_usuario = $dUser['nombre_completo'];

// 2. Verificar Caja Abierta
$qCaja = mysqli_query($conexion, "SELECT * FROM caja_sesiones WHERE id_usuario = '$id_usuario' AND estado = 'abierta'");
$caja = mysqli_fetch_assoc($qCaja);
$caja_abierta = (mysqli_num_rows($qCaja) > 0);

// VARIABLES DE SUMA
$ingresos_reservas_web = 0;
$ingresos_hotel_checkout = 0;
$ingresos_mostrador = 0;
$total_gastos = 0; // NUEVO
$total_calculado = 0;
$total_final_en_caja = 0;

if ($caja_abierta) {
    $fecha_apertura = $caja['fecha_apertura'];
    
    // A. SUMAR RESERVAS WEB
    $sqlWeb = "SELECT SUM(pago_monto) as total FROM reservaciones 
           WHERE (estado='confirmada' OR estado='checkin') 
           AND fecha_pago >= '$fecha_apertura'";
    $dWeb = mysqli_fetch_assoc(mysqli_query($conexion, $sqlWeb));
    $ingresos_reservas_web = $dWeb['total'] ? $dWeb['total'] : 0.00;

// B. SUMAR CHECK-OUTS DEL HOTEL (Habitación + Consumos - Adelantos)
    // CORRECCIÓN MATEMÁTICA: Restamos el adelanto para no duplicar ingresos
    $sqlHotel = "SELECT SUM((total_habitacion + total_consumos) - adelanto) as total 
                 FROM estancias 
                 WHERE estado='finalizada' AND fecha_salida >= '$fecha_apertura'";
                 
    $rHotel = mysqli_query($conexion, $sqlHotel);
    $dHotel = mysqli_fetch_assoc($rHotel);
    $ingresos_hotel_checkout = $dHotel['total'] ? $dHotel['total'] : 0.00;

    // C. SUMAR VENTAS MOSTRADOR
    $sqlMostrador = "SELECT SUM(monto) as total FROM ventas_directas WHERE fecha >= '$fecha_apertura'";
    $dMostrador = mysqli_fetch_assoc(mysqli_query($conexion, $sqlMostrador));
    $ingresos_mostrador = $dMostrador['total'] ? $dMostrador['total'] : 0.00;

    // D. SUMAR GASTOS (EGRESOS) -- NUEVO BLOQUE
    $sqlGastos = "SELECT SUM(monto) as total FROM gastos WHERE fecha >= '$fecha_apertura'";
    $dGastos = mysqli_fetch_assoc(mysqli_query($conexion, $sqlGastos));
    $total_gastos = $dGastos['total'] ? $dGastos['total'] : 0.00;

    // E. CÁLCULO FINAL
    // Ingresos Totales
    $total_ingresos = $ingresos_reservas_web + $ingresos_hotel_checkout + $ingresos_mostrador;
    
    // Lo que debe haber físicamente = (Inicial + Ingresos) - Gastos
    $total_final_en_caja = ($caja['monto_inicial'] + $total_ingresos) - $total_gastos;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Caja | Tulumayo Lodge</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #ecf0f1; margin: 0; display: flex; height: 100vh; }
        .sidebar { width: 250px; background-color: #2E5C38; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background: #244a2d; margin: 0; border-bottom: 1px solid #3A5A40; }
        .sidebar a { padding: 15px 20px; color: white; text-decoration: none; display: flex; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.3s; }
        .sidebar a:hover { background-color: #3A5A40; padding-left: 25px; }
        .sidebar a.active { background-color: #2ecc71; font-weight: bold; }
        
        .main { flex: 1; padding: 40px; overflow-y: auto; display: flex; flex-direction: column; align-items: center; }

        .box-card { background: white; width: 700px; padding: 30px; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; margin-bottom: 40px; }
        .box-icon { font-size: 3rem; color: #2ecc71; margin-bottom: 10px; }
        .box-closed .box-icon { color: #e74c3c; }
        
        .report-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 10px; margin-bottom: 20px; text-align: left; }
        .report-item { background: #f8f9fa; padding: 10px; border-radius: 5px; border-left: 4px solid #ddd; }
        .report-item span { display: block; font-size: 0.75rem; color: #888; margin-bottom: 5px; }
        .report-item strong { display: block; font-size: 1rem; color: #333; }
        
        /* Estilo especial para Gastos */
        .item-gasto { border-color: #e74c3c; background: #fdedec; }
        .item-gasto strong { color: #e74c3c; }

        .total-box { background: #2c3e50; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
        
        .input-money { font-size: 1.5rem; padding: 10px; width: 150px; text-align: center; border: 2px solid #ddd; border-radius: 5px; }
        .btn-big { width: 100%; padding: 15px; border: none; border-radius: 5px; font-size: 1.1rem; font-weight: bold; cursor: pointer; color: white; margin-top: 15px; }
        .btn-open { background: #2ecc71; }
        .btn-close { background: #e74c3c; }

        /* Modal */
        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 100; }
        .modal-content { background: white; width: 400px; margin: 10% auto; padding: 25px; border-radius: 8px; position: relative; }
    </style>
</head>
<body>

        <?php include 'sidebar.php'; ?>

    <div class="main">
        <?php if (!$caja_abierta): ?>
            <!-- CAJA CERRADA -->
            <div class="box-card box-closed">
                <div class="box-icon"><i class="fas fa-lock"></i></div>
                <h1>Caja Cerrada</h1>
                <p>Bienvenido, <b><?php echo $nombre_usuario; ?></b>. Inicia tu turno.</p>
                <form action="procesar_caja.php" method="POST">
                    <input type="hidden" name="accion" value="abrir">
                    <label>Monto Inicial (Sencillo):</label><br>
                    <input type="number" name="monto_inicial" class="input-money" value="0.00" step="0.10" required>
                    <button type="submit" class="btn-big btn-open">ABRIR TURNO</button>
                </form>
            </div>
        <?php else: ?>
            <!-- CAJA ABIERTA -->
            <div class="box-card">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <div style="text-align:left;">
                        <h1 style="margin:0;">Turno Activo</h1>
                        <small>Abierto: <?php echo date("d/m H:i", strtotime($caja['fecha_apertura'])); ?></small>
                    </div>
                    <!-- BOTÓN REGISTRAR GASTO -->
                    <button onclick="document.getElementById('modalGasto').style.display='block'" style="background:#e74c3c; color:white; border:none; padding:10px 15px; border-radius:5px; cursor:pointer; font-weight:bold;">
                        <i class="fas fa-minus-circle"></i> Registrar Egreso
                    </button>
                </div>
                
                <div class="report-grid">
                    <div class="report-item" style="border-color: #3498db;">
                        <span>Fondo Inicial</span>
                        <strong>S/ <?php echo $caja['monto_inicial']; ?></strong>
                    </div>
                    <div class="report-item" style="border-color: #f39c12;">
                        <span>Reservas Web</span>
                        <strong>S/ <?php echo number_format($ingresos_reservas_web, 2); ?></strong>
                    </div>
                    <div class="report-item" style="border-color: #9b59b6;">
                        <span>Check-outs + Mostrador</span>
                        <strong>S/ <?php echo number_format($ingresos_hotel_checkout + $ingresos_mostrador, 2); ?></strong>
                    </div>
                    <!-- CUADRO DE GASTOS -->
                    <div class="report-item item-gasto">
                        <span>(-) Gastos / Salidas</span>
                        <strong>S/ <?php echo number_format($total_gastos, 2); ?></strong>
                    </div>
                </div>

                <div class="total-box">
                    <div style="text-align: left;">
                        <span style="display:block; font-size:0.8rem; opacity:0.8;">Cálculo (Inicial + Ingresos - Gastos)</span>
                        <span style="font-size:1rem; font-weight:normal;">Debe haber en efectivo</span>
                    </div>
                    <div style="text-align: right;">
                        <span style="font-size:2rem; font-weight:bold;">S/ <?php echo number_format($total_final_en_caja, 2); ?></span>
                    </div>
                </div>

                <form action="procesar_caja.php" method="POST" onsubmit="return confirm('¿Cerrar turno?');">
                    <input type="hidden" name="accion" value="cerrar">
                    <input type="hidden" name="id_caja" value="<?php echo $caja['id']; ?>">
                    <!-- Guardamos las ventas brutas para estadística, aunque el monto final es lo que importa -->
                    <input type="hidden" name="total_sistema" value="<?php echo $total_ingresos; ?>">
                    
                    <label style="font-weight:bold; color:#e74c3c;">¿Cuánto dinero hay FÍSICAMENTE?</label><br>
                    <input type="number" name="monto_final" class="input-money" placeholder="0.00" step="0.10" required style="border-color:#e74c3c; margin-top:5px;">
                    
                    <button type="submit" class="btn-big btn-close">CERRAR TURNO</button>
                </form>
            </div>

            <!-- MODAL REGISTRAR GASTO -->
            <div id="modalGasto" class="modal">
                <div class="modal-content" style="border-top: 5px solid #e74c3c;">
                    <span onclick="document.getElementById('modalGasto').style.display='none'" style="float:right; cursor:pointer; font-size:1.5rem;">&times;</span>
                    <h2 style="color:#e74c3c; margin-top:0;">Registrar Salida de Dinero</h2>
                    
                    <form action="procesar_gasto.php" method="POST">
                        <label style="display:block; margin-bottom:5px; font-weight:bold;">Descripción del Gasto:</label>
                        <input type="text" name="descripcion" required placeholder="Ej. Pago Taxi, Compra Pan" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px; margin-bottom:15px;">
                        
                        <label style="display:block; margin-bottom:5px; font-weight:bold;">Monto a Retirar (S/):</label>
                        <input type="number" name="monto" step="0.50" required placeholder="0.00" style="width:100%; padding:10px; border:1px solid #ccc; border-radius:5px; margin-bottom:15px;">
                        
                        <button type="submit" style="width:100%; padding:12px; background:#e74c3c; color:white; border:none; border-radius:5px; font-weight:bold; cursor:pointer;">
                            REGISTRAR EGRESO
                        </button>
                    </form>
                </div>
            </div>

        <?php endif; ?>

        <!-- HISTORIAL DE CAJAS -->
        <div style="width: 100%; max-width: 800px; margin-top: 20px;">
            <h2 style="color: #2c3e50; border-bottom: 2px solid #ddd; padding-bottom: 10px;">Historial de Cierres</h2>
            <table style="width: 100%; background: white; border-collapse: collapse; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden;">
                <thead style="background: #34495e; color: white;">
                    <tr>
                        <th style="padding:12px;">ID</th>
                        <th style="padding:12px;">Apertura</th>
                        <th style="padding:12px;">Cierre</th>
                        <th style="padding:12px;">Ventas</th>
                        <th style="padding:12px;">Estado</th>
                        <th style="padding:12px;">Reporte</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sqlHist = "SELECT * FROM caja_sesiones WHERE id_usuario = '$id_usuario' ORDER BY id DESC LIMIT 10";
                    $resHist = mysqli_query($conexion, $sqlHist);
                    while($h = mysqli_fetch_assoc($resHist)): 
                    ?>
                    <tr style="border-bottom: 1px solid #eee;">
                        <td style="padding:12px;">#<?php echo $h['id']; ?></td>
                        <td style="padding:12px;"><?php echo date("d/m H:i", strtotime($h['fecha_apertura'])); ?></td>
                        <td style="padding:12px;"><?php echo $h['fecha_cierre'] ? date("d/m H:i", strtotime($h['fecha_cierre'])) : '-'; ?></td>
                        <td style="padding:12px;">S/ <?php echo number_format($h['total_ventas'], 2); ?></td>
                        <td style="padding:12px;">
                            <?php if($h['estado']=='abierta'): ?>
                                <span style="background:#2ecc71; color:white; padding:3px 8px; border-radius:10px; font-size:0.8rem;">Activa</span>
                            <?php else: ?>
                                <span style="background:#95a5a6; color:white; padding:3px 8px; border-radius:10px; font-size:0.8rem;">Cerrada</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding:12px;">
                            <a href="reporte_caja.php?id=<?php echo $h['id']; ?>" target="_blank" style="color: #3498db; font-weight: bold; text-decoration: none;">Ver</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>
</body>
</html>