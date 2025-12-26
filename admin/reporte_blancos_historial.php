<?php
session_start();
include '../db.php';
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// Consultamos historial uniendo con el nombre del ítem
$sql = "SELECT h.*, i.nombre as item 
        FROM blancos_historial h 
        JOIN blancos_items i ON h.id_item = i.id 
        ORDER BY h.fecha DESC LIMIT 100"; // Últimos 100 movimientos
$res = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial Movimientos | Tulumayo</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        h1 { text-align: center; color: #8e44ad; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #f4f4f4; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()" style="padding:10px 20px; cursor:pointer;">IMPRIMIR</button>
    
    <h1>Bitácora de Movimientos - Blancos</h1>
    <p>Últimos movimientos registrados</p>

    <table>
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Ítem</th>
                <th>Movimiento</th>
                <th>Cant.</th>
                <th>Detalle / Obs</th>
                <th>Usuario</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td><?php echo date("d/m/Y H:i", strtotime($row['fecha'])); ?></td>
                <td><strong><?php echo $row['item']; ?></strong></td>
                <td><?php echo $row['tipo_movimiento']; ?></td>
                <td style="font-weight:bold;"><?php echo $row['cantidad']; ?></td>
                <td><?php echo $row['observacion']; ?></td>
                <td><?php echo $row['usuario']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>