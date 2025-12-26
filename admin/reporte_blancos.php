<?php
session_start();
include '../db.php';
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// Consultar movimientos
$sql = "SELECT m.*, i.nombre as item, h.nombre as habitacion 
        FROM blancos_movimientos m 
        JOIN blancos_items i ON m.id_item = i.id 
        LEFT JOIN habitaciones h ON m.id_habitacion = h.id 
        ORDER BY m.fecha DESC LIMIT 100";
$res = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Housekeeping</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; padding: 40px; background: #f4f4f4; }
        .container { background: white; max-width: 900px; margin: 0 auto; padding: 30px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        h1 { color: #2c3e50; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background: #34495e; color: white; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #ddd; }
        .in { color: #27ae60; font-weight: bold; }
        .out { color: #e67e22; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Historial de Movimientos - Blancos</h1>
        <button onclick="window.print()" style="padding:10px; cursor:pointer;">Imprimir</button>
        <a href="blancos.php" style="margin-left:10px; text-decoration:none;">Volver</a>
        
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Ítem</th>
                    <th>Movimiento</th>
                    <th>Habitación / Destino</th>
                    <th>Cant.</th>
                    <th>Nota</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($res)) { 
                    $tipo = ($row['tipo_mov'] == 'compra') ? 'Entrada' : 'Salida/Entrega';
                    $clase = ($row['tipo_mov'] == 'compra') ? 'in' : 'out';
                    $destino = $row['habitacion'] ? $row['habitacion'] : 'Almacén General';
                ?>
                <tr>
                    <td><?php echo date("d/m/Y H:i", strtotime($row['fecha'])); ?></td>
                    <td><?php echo $row['item']; ?></td>
                    <td class="<?php echo $clase; ?>"><?php echo $tipo; ?></td>
                    <td><?php echo $destino; ?></td>
                    <td style="font-weight:bold;"><?php echo $row['cantidad']; ?></td>
                    <td><?php echo $row['observacion']; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>