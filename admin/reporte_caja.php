<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima');

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }
if (!isset($_GET['id'])) { die("Error: Falta el ID de la caja."); }

$id_caja = $_GET['id'];

// 1. OBTENER DATOS DE LA SESIÓN DE CAJA
$sql = "SELECT c.*, u.nombre_completo 
        FROM caja_sesiones c 
        JOIN usuarios_admin u ON c.id_usuario = u.id 
        WHERE c.id = '$id_caja'";
$res = mysqli_query($conexion, $sql);
$caja = mysqli_fetch_assoc($res);

if (!$caja) { die("Caja no encontrada."); }

$inicio = $caja['fecha_apertura'];
$fin = $caja['fecha_cierre'] ? $caja['fecha_cierre'] : date('Y-m-d H:i:s'); 

// 2. CALCULAR INGRESOS POR RESERVAS WEB
$sqlWeb = "SELECT * FROM reservaciones 
           WHERE (estado='confirmada' OR estado='checkin') 
           AND fecha_pago BETWEEN '$inicio' AND '$fin'";
$resWeb = mysqli_query($conexion, $sqlWeb);

$total_web = 0;
$lista_web = [];
while($row = mysqli_fetch_assoc($resWeb)){
    $total_web += $row['pago_monto'];
    $lista_web[] = $row;
}

// 3. CALCULAR INGRESOS POR HOTEL (CHECK-OUTS)
$sqlHotel = "SELECT e.*, h.nombre as habitacion 
             FROM estancias e 
             JOIN habitaciones h ON e.id_habitacion = h.id 
             WHERE e.estado='finalizada' AND e.fecha_salida BETWEEN '$inicio' AND '$fin'";
$resHotel = mysqli_query($conexion, $sqlHotel);

$total_hotel = 0;
$lista_hotel = [];
while($row = mysqli_fetch_assoc($resHotel)){
    // Cálculo del cobro real (Total - Adelanto)
    $total_bruto = $row['total_habitacion'] + $row['total_consumos'];
    $cobrado_hoy = $total_bruto - $row['adelanto']; 
    
    $total_hotel += $cobrado_hoy;
    
    $row['monto_final_ticket'] = $cobrado_hoy; 
    $lista_hotel[] = $row;
}

// 4. CALCULAR VENTAS DE MOSTRADOR
$sqlDirectas = "SELECT * FROM ventas_directas 
                WHERE fecha BETWEEN '$inicio' AND '$fin'";
$resDirectas = mysqli_query($conexion, $sqlDirectas);

$total_directas = 0;
$lista_directas = [];
while($row = mysqli_fetch_assoc($resDirectas)){
    $total_directas += $row['monto'];
    $lista_directas[] = $row;
}

// 5. CALCULAR GASTOS
$sqlGastos = "SELECT * FROM gastos WHERE fecha BETWEEN '$inicio' AND '$fin'";
$resGastos = mysqli_query($conexion, $sqlGastos);

$total_gastos = 0;
$lista_gastos = [];
while($row = mysqli_fetch_assoc($resGastos)){
    $total_gastos += $row['monto'];
    $lista_gastos[] = $row;
}

// TOTALES FINALES
$total_sistema = $total_web + $total_hotel + $total_directas;
$total_esperado = ($caja['monto_inicial'] + $total_sistema) - $total_gastos;
$diferencia = $caja['monto_final'] - $total_esperado;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Caja #<?php echo $caja['id']; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #555; display: flex; justify-content: center; padding: 40px; }
        .hoja { background: white; width: 700px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        header { text-align: center; border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 30px; }
        header h1 { margin: 0; color: #2E5C38; text-transform: uppercase; }
        header p { margin: 5px 0; color: #777; font-size: 0.9rem; }
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-box label { display: block; font-size: 0.8rem; color: #999; font-weight: bold; text-transform: uppercase; }
        .info-box span { display: block; font-size: 1.1rem; font-weight: 600; color: #333; }
        h3 { border-bottom: 1px solid #ddd; padding-bottom: 10px; color: #2E5C38; margin-top: 30px; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 0.9rem; }
        th { text-align: left; background: #f4f4f4; padding: 10px; color: #555; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .resumen-final { background: #f9f9f9; padding: 20px; border-radius: 5px; margin-top: 40px; }
        .fila-resumen { display: flex; justify-content: space-between; margin-bottom: 10px; font-size: 1rem; }
        .total-grande { font-size: 1.5rem; font-weight: bold; color: #333; border-top: 2px solid #ccc; padding-top: 10px; margin-top: 10px; }
        .cuadre-box { text-align: center; padding: 15px; color: white; font-weight: bold; margin-top: 20px; border-radius: 5px; }
        .bg-ok { background: #2ecc71; }
        .bg-error { background: #e74c3c; }
        .btn-print { position: fixed; bottom: 20px; right: 20px; background: #2c3e50; color: white; padding: 15px 30px; border: none; border-radius: 50px; cursor: pointer; box-shadow: 0 5px 15px rgba(0,0,0,0.3); font-weight: bold; }
        @media print { body { background: white; padding: 0; } .hoja { box-shadow: none; width: 100%; padding: 20px; } .btn-print { display: none; } }
        
        .tag-metodo { font-size: 0.75rem; background: #eee; padding: 2px 6px; border-radius: 4px; color: #555; border: 1px solid #ccc; }
    </style>
</head>
<body>

    <button class="btn-print" onclick="window.print()">IMPRIMIR REPORTE</button>

    <div class="hoja">
        <header>
            <h1>Tulumayo Lodge</h1>
            <p>Reporte de Cierre de Caja</p>
            <p>RUC: 20123456789 | Chanchamayo, Perú</p>
        </header>

        <div class="info-grid">
            <div class="info-box"><label>Responsable</label><span><?php echo $caja['nombre_completo']; ?></span></div>
            <div class="info-box"><label>N° Reporte</label><span>#<?php echo str_pad($caja['id'], 6, '0', STR_PAD_LEFT); ?></span></div>
            <div class="info-box"><label>Apertura</label><span><?php echo date("d/m/Y H:i", strtotime($caja['fecha_apertura'])); ?></span></div>
            <div class="info-box"><label>Cierre</label><span><?php echo $caja['fecha_cierre'] ? date("d/m/Y H:i", strtotime($caja['fecha_cierre'])) : "En curso"; ?></span></div>
        </div>

        <!-- 1. RESERVAS WEB -->
        <h3>Ingresos Reservas Web / Adelantos</h3>
        <table>
            <thead><tr><th>Cliente</th><th>Método</th><th>Nota</th><th class="text-right">Monto</th></tr></thead>
            <tbody>
                <?php foreach($lista_web as $w): ?>
                <tr>
                    <td><?php echo $w['nombre_cliente']; ?></td>
                    <td><span class="tag-metodo"><?php echo $w['metodo_pago']; ?></span></td>
                    <td><?php echo $w['detalles_pago']; ?></td>
                    <td class="text-right">S/ <?php echo number_format($w['pago_monto'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($lista_web)) echo "<tr><td colspan='4'>No hubo movimientos.</td></tr>"; ?>
            </tbody>
        </table>

        <!-- 2. HOTEL CHECK-OUTS (ACTUALIZADO CON MÉTODO) -->
        <h3>Ingresos Hotel (Check-outs)</h3>
        <table>
            <thead>
                <tr>
                    <th>Habitación</th>
                    <th>Huésped</th>
                    <th>Método</th> <!-- Nueva Columna -->
                    <th class="text-right">Monto Cobrado</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($lista_hotel as $h): ?>
                <tr>
                    <td><?php echo $h['habitacion']; ?></td>
                    <td><?php echo $h['nombre_huesped']; ?></td>
                    <!-- Aquí mostramos el método guardado -->
                    <td><span class="tag-metodo"><?php echo $h['metodo_pago_checkout']; ?></span></td>
                    <td class="text-right">S/ <?php echo number_format($h['monto_final_ticket'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($lista_hotel)) echo "<tr><td colspan='4'>No hubo salidas.</td></tr>"; ?>
            </tbody>
        </table>

        <!-- 3. VENTAS MOSTRADOR -->
        <h3>Ventas de Mostrador / Bar / Restaurante</h3>
        <table>
            <thead><tr><th>Producto</th><th>Método</th><th>Hora</th><th class="text-right">Monto</th></tr></thead>
            <tbody>
                <?php foreach($lista_directas as $d): ?>
                <tr>
                    <td><?php echo $d['detalle']; ?></td>
                    <td><span class="tag-metodo"><?php echo $d['metodo_pago']; ?></span></td>
                    <td><?php echo date("H:i", strtotime($d['fecha'])); ?></td>
                    <td class="text-right">S/ <?php echo number_format($d['monto'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($lista_directas)) echo "<tr><td colspan='4'>No hubo ventas directas.</td></tr>"; ?>
            </tbody>
        </table>

        <!-- 4. GASTOS -->
        <h3 style="color: #e74c3c;">Salidas de Dinero (Gastos)</h3>
        <table>
            <thead><tr><th>Descripción</th><th>Hora</th><th class="text-right">Monto</th></tr></thead>
            <tbody>
                <?php foreach($lista_gastos as $g): ?>
                <tr>
                    <td><?php echo $g['descripcion']; ?></td>
                    <td><?php echo date("H:i", strtotime($g['fecha'])); ?></td>
                    <td class="text-right" style="color: #e74c3c;">- S/ <?php echo number_format($g['monto'], 2); ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if(empty($lista_gastos)) echo "<tr><td colspan='3'>No hubo gastos.</td></tr>"; ?>
            </tbody>
        </table>

        <!-- RESUMEN FINAL -->
        <div class="resumen-final">
            <div class="fila-resumen"><span>(+) Fondo Inicial Caja</span><strong>S/ <?php echo number_format($caja['monto_inicial'], 2); ?></strong></div>
            <div class="fila-resumen"><span>(+) Total Ventas</span><strong>S/ <?php echo number_format($total_sistema, 2); ?></strong></div>
            <div class="fila-resumen"><span>(-) Gastos Operativos</span><strong style="color: #e74c3c;">- S/ <?php echo number_format($total_gastos, 2); ?></strong></div>
            <hr>
            <div class="fila-resumen total-grande"><span>(=) Total Esperado en Caja</span><span>S/ <?php echo number_format($total_esperado, 2); ?></span></div>
            
            <?php if($caja['estado'] == 'cerrada'): ?>
                <div class="fila-resumen" style="color: #555; margin-top: 15px;"><span>(-) Efectivo Declarado</span><strong>S/ <?php echo number_format($caja['monto_final'], 2); ?></strong></div>
                <?php 
                    $color_diff = ($diferencia >= -0.5) ? 'bg-ok' : 'bg-error';
                    $texto_diff = ($diferencia >= -0.5) ? 'CUADRE CORRECTO' : 'FALTANTE: S/ ' . number_format($diferencia, 2);
                ?>
                <div class="cuadre-box <?php echo $color_diff; ?>"><?php echo $texto_diff; ?></div>
            <?php endif; ?>
        </div>
        
        <br><center>__________________________<br>Firma Responsable</center>
    </div>
</body>
</html>