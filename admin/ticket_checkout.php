<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima');

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }
if (!isset($_GET['id'])) { die("Error: Falta ID de estancia."); }

$id_estancia = $_GET['id'];

// 1. DATOS DE LA ESTANCIA
$sql = "SELECT e.*, h.nombre as habitacion, h.precio_noche 
        FROM estancias e 
        JOIN habitaciones h ON e.id_habitacion = h.id 
        WHERE e.id = '$id_estancia'";
$res = mysqli_query($conexion, $sql);
$estancia = mysqli_fetch_assoc($res);

// 2. DATOS DE CONSUMOS
$sqlConsumos = "SELECT * FROM consumos WHERE id_estancia = '$id_estancia'";
$resConsumos = mysqli_query($conexion, $sqlConsumos);

// --- CÁLCULO DE DÍAS (Lógica Corregida por Fechas) ---
$inicio = new DateTime($estancia['fecha_ingreso']);
$inicio->setTime(0, 0, 0); // Reset a medianoche

$fin = new DateTime($estancia['fecha_salida']);
$fin->setTime(0, 0, 0); // Reset a medianoche

$diff = $inicio->diff($fin);
$dias = $diff->days;
if($dias == 0) $dias = 1; // Mínimo 1 noche

// --- MATEMÁTICA FINANCIERA ---
$subtotal_hab = $estancia['total_habitacion']; // La base de datos ya tiene el cálculo precio * dias
$subtotal_con = $estancia['total_consumos'];
$total_general = $subtotal_hab + $subtotal_con;
$adelanto = $estancia['adelanto'];
$saldo_a_pagar = ($total_general - $adelanto) - $estancia['descuento'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?php echo $id_estancia; ?></title>
    <style>
        /* CONFIGURACIÓN PARA IMPRESORA TÉRMICA 80MM */
        @page {
            margin: 0;
            size: 80mm auto; /* Ancho fijo, alto automático */
        }

        body {
            background: #eee;
            font-family: 'Helvetica', 'Arial', sans-serif; /* Fuente legible en térmicas */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
        }
        
        .ticket {
            background: white;
            width: 80mm; /* Ancho exacto */
            max-width: 80mm;
            padding: 5mm; /* Márgenes internos pequeños */
            box-sizing: border-box;
            font-size: 12px; /* Letra estándar para tickets */
            color: #000;
        }

        .center { text-align: center; }
        .right { text-align: right; }
        
        /* LOGO */
        .logo-ticket {
            width: 60%; /* Ajustar según tu logo */
            max-width: 120px;
            height: auto;
            margin-bottom: 5px;
            filter: grayscale(100%) contrast(120%); /* Truco: Logos B/N salen mejor en térmicas */
        }

        h2 { margin: 5px 0 0 0; font-size: 14px; font-weight: bold; text-transform: uppercase; }
        p { margin: 2px 0; }
        
        .line { border-bottom: 1px dashed #000; margin: 8px 0; }
        
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        
        .product-item { font-size: 11px; }
        
        .total-row td { 
            font-size: 14px; 
            font-weight: bold; 
            border-top: 1px solid #000; 
            padding-top: 5px; 
            margin-top: 5px;
        }

        .final-pay td {
            font-size: 16px;
            font-weight: 900;
            padding-top: 10px;
        }
        
        /* BOTONES (Se ocultan al imprimir) */
        .no-print {
            padding: 10px;
            text-align: center;
            background: #eee;
            width: 100%;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            text-decoration: none;
            color: white;
            border-radius: 5px;
            font-weight: bold;
            font-size: 14px;
            margin: 5px;
        }
        .btn-print { background: #2c3e50; }
        .btn-back { background: #95a5a6; }

        @media print {
            body { background: white; }
            .ticket { width: 100%; padding: 0; box-shadow: none; margin: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <!-- Contenedor Principal -->
    <div class="ticket">
        
        <!-- CABECERA -->
        <div class="center">
            <!-- Logo en blanco y negro recomendado para térmicas -->
            <img src="../img/logo.png" alt="Logo" class="logo-ticket">
            
            <h2>T&H IMPERIAL SAC</h2>
            <p>RUC: 20615071995</p>
            <p>Tulumayo Lodge - Chanchamayo</p>
            <p>Tel: +51 999 600 091</p>
        </div>

        <div class="line"></div>

<!-- LÓGICA INTELIGENTE DE CLIENTE -->
        <?php if($estancia['es_empresa'] == 1): ?>
            <!-- ES FACTURA -->
            <p><strong>FACTURA ELECTRÓNICA</strong></p>
            <p><strong>Razón Social:</strong> <?php echo strtoupper($estancia['razon_social']); ?></p>
            <p><strong>RUC:</strong> <?php echo $estancia['ruc_empresa']; ?></p>
            <p><strong>Dir:</strong> <?php echo strtoupper($estancia['direccion']); ?></p>
            <p><small>Huésped: <?php echo $estancia['nombre_huesped']; ?></small></p>
        <?php else: ?>
            <!-- ES BOLETA -->
            <p><strong>BOLETA DE VENTA</strong></p>
            <p><strong>Cliente:</strong> <?php echo strtoupper($estancia['nombre_huesped']); ?></p>
            <p><strong><?php echo $estancia['tipo_doc']; ?>:</strong> <?php echo $estancia['num_doc']; ?></p>
        <?php endif; ?>

        <p><strong>Habitación:</strong> <?php echo $estancia['habitacion']; ?></p>

        <div class="line"></div>

        <!-- DETALLE DE COBROS -->
        <table>
            <!-- 1. ALOJAMIENTO -->
            <tr>
                <td colspan="2" style="font-weight:bold;">ALOJAMIENTO</td>
            </tr>
            <tr>
                <td class="product-item">
                    <?php echo $dias; ?> Noche(s) x S/ <?php echo number_format($estancia['precio_noche'], 2); ?>
                    <br><small><?php echo date("d/m", strtotime($estancia['fecha_ingreso'])); ?> al <?php echo date("d/m", strtotime($estancia['fecha_salida'])); ?></small>
                </td>
                <td class="right">S/ <?php echo number_format($subtotal_hab, 2); ?></td>
            </tr>

            <!-- 2. CONSUMOS -->
            <?php if (mysqli_num_rows($resConsumos) > 0): ?>
                <tr><td colspan="2" style="padding-top:5px; font-weight:bold;">CONSUMOS / EXTRAS</td></tr>
                <?php while($c = mysqli_fetch_assoc($resConsumos)): ?>
                <tr>
                    <td class="product-item"><?php echo $c['detalle']; ?></td>
                    <td class="right">S/ <?php echo number_format($c['monto'], 2); ?></td>
                </tr>
                <?php endwhile; ?>
            <?php endif; ?>
        </table>

        <div class="line"></div>

        <!-- TOTALES -->
        <table>
            <tr class="total-row">
                <td>IMPORTE TOTAL:</td>
                <td class="right">S/ <?php echo number_format($total_general, 2); ?></td>
            </tr>
            
            <?php if($adelanto > 0): ?>
            <tr>
                <td>(-) ADELANTO:</td>
                <td class="right">- S/ <?php echo number_format($adelanto, 2); ?></td>
            </tr>
            <?php endif; ?>
            <?php if($estancia['descuento'] > 0): ?>
            <tr>
                <td>(-) DESCUENTO:</td>
                <td class="right">- S/ <?php echo number_format($estancia['descuento'], 2); ?></td>
            </tr>
            <?php endif; ?>
            
            <tr class="final-pay">
                <td>SALDO A PAGAR:</td>
                <td class="right">S/ <?php echo number_format($saldo_a_pagar, 2); ?></td>
            </tr>
        </table>

        <br>
        <div class="center" style="font-size: 10px;">
            <p>¡Gracias por su preferencia!</p>
            <p>www.tulumayolodge.com</p>
        </div>

    </div>

    <!-- BOTONES DE NAVEGACIÓN -->
    <div class="no-print" style="position: fixed; bottom: 0; left: 0;">
        <a href="#" onclick="window.print()" class="btn btn-print">🖨️ IMPRIMIR</a>
        <a href="rack.php" class="btn btn-back">↩ VOLVER</a>
    </div>

</body>
</html>