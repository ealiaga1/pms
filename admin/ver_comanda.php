<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima');

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }
if (!isset($_GET['id'])) { die("Error: Falta ID de pedido."); }

$id_pedido = $_GET['id'];

// 1. DATOS DEL PEDIDO Y MESA
$sql = "SELECT p.*, m.nombre as nombre_mesa 
        FROM pedidos_restaurante p 
        JOIN mesas m ON p.id_mesa = m.id 
        WHERE p.id = '$id_pedido'";
$res = mysqli_query($conexion, $sql);
$pedido = mysqli_fetch_assoc($res);

// 2. DETALLES (PLATOS)
$sqlDet = "SELECT d.*, pr.nombre 
           FROM detalle_pedido d 
           JOIN productos pr ON d.id_producto = pr.id 
           WHERE d.id_pedido = '$id_pedido'";
$resDet = mysqli_query($conexion, $sqlDet);

// Calcular total al vuelo (o usar el de la BD)
$total_calculado = 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comanda #<?php echo $id_pedido; ?></title>
    <style>
        /* CONFIGURACIÓN IMPRESORA TÉRMICA */
        @page { margin: 0; size: 80mm auto; }

        body {
            background: #eee;
            font-family: 'Helvetica', 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
        }
        
        .ticket {
            background: white;
            width: 80mm;
            max-width: 80mm;
            padding: 5mm;
            box-sizing: border-box;
            font-size: 12px;
            color: #000;
        }

        .center { text-align: center; }
        .right { text-align: right; }
        
        /* LOGO */
        .logo-ticket {
            width: 60%;
            max-width: 120px;
            height: auto;
            margin-bottom: 5px;
            filter: grayscale(100%) contrast(120%);
        }

        h2 { margin: 5px 0 0 0; font-size: 14px; font-weight: bold; text-transform: uppercase; }
        p { margin: 2px 0; }
        
        .line { border-bottom: 1px dashed #000; margin: 8px 0; }
        
        table { width: 100%; border-collapse: collapse; }
        td { padding: 3px 0; vertical-align: top; }
        
        .item-name { font-weight: bold; font-size: 11px; }
        .item-note { font-size: 10px; font-style: italic; display: block; margin-left: 10px; }
        
        .total-row td { 
            font-size: 16px; 
            font-weight: 900; 
            border-top: 2px solid #000; 
            padding-top: 8px; 
            margin-top: 5px;
        }

        /* BOTONES DE PANTALLA */
        .no-print {
            padding: 10px; text-align: center; background: #eee; width: 100%;
            position: fixed; bottom: 0; left: 0;
        }
        .btn {
            display: inline-block; padding: 10px 20px; text-decoration: none; color: white;
            border-radius: 5px; font-weight: bold; font-size: 14px; margin: 5px; cursor: pointer;
        }
        .btn-print { background: #2c3e50; }
        .btn-back { background: #95a5a6; }

        @media print {
            body { background: white; }
            .ticket { width: 100%; padding: 0; margin: 0; box-shadow: none; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="ticket">
        
        <div class="center">
            <img src="../img/logo.png" alt="Logo" class="logo-ticket">
            <h2>T&H IMPERIAL SAC</h2>
            <p>RESTAURANTE / BAR</p>
            <p>Tulumayo Lodge</p>
        </div>

        <div class="line"></div>

        <table style="font-size: 11px;">
            <tr>
                <td><strong>Orden:</strong> #<?php echo str_pad($pedido['id'], 6, "0", STR_PAD_LEFT); ?></td>
                <td class="right"><?php echo date("d/m H:i", strtotime($pedido['fecha_apertura'])); ?></td>
            </tr>
            <tr>
                <td colspan="2" style="font-size: 14px; font-weight: bold; padding-top: 5px;">
                    <?php echo strtoupper($pedido['nombre_mesa']); ?>
                </td>
            </tr>
            <tr>
                <td colspan="2">Atendido por: <?php echo $pedido['mozo']; ?></td>
            </tr>
        </table>

        <div class="line"></div>

        <!-- DETALLE DEL PEDIDO -->
        <table>
            <tr>
                <td style="border-bottom:1px solid #000;">Cant.</td>
                <td style="border-bottom:1px solid #000;">Descripción</td>
                <td class="right" style="border-bottom:1px solid #000;">Importe</td>
            </tr>

            <?php while($d = mysqli_fetch_assoc($resDet)): 
                $subtotal = $d['cantidad'] * $d['precio_unitario'];
                $total_calculado += $subtotal;
            ?>
            <tr>
                <td style="width: 10%; font-weight: bold;"><?php echo $d['cantidad']; ?></td>
                <td>
                    <span class="item-name"><?php echo $d['nombre']; ?></span>
                    <?php if(!empty($d['notas'])): ?>
                        <span class="item-note">(<?php echo $d['notas']; ?>)</span>
                    <?php endif; ?>
                </td>
                <td class="right">S/ <?php echo number_format($subtotal, 2); ?></td>
            </tr>
            <?php endwhile; ?>
        </table>

        <div class="line"></div>

        <!-- TOTAL -->
        <table>
            <tr class="total-row">
                <td>TOTAL A PAGAR:</td>
                <td class="right">S/ <?php echo number_format($total_calculado, 2); ?></td>
            </tr>
        </table>

        <br>
        <div class="center" style="font-size: 10px;">
            <p>Propina no incluida</p>
            <p>¡Buen Provecho!</p>
        </div>

    </div>

    <!-- BOTONES -->
    <div class="no-print">
        <a href="#" onclick="window.print()" class="btn btn-print">🖨️ IMPRIMIR</a>
        <a href="restaurante.php" class="btn btn-back">↩ VOLVER</a>
    </div>

</body>
</html>