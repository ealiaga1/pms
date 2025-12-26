<?php
session_start();
include '../db.php';
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

$sql = "SELECT * FROM blancos_items ORDER BY tipo DESC, nombre ASC";
$res = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario Blancos | Tulumayo</title>
    <style>
        body { font-family: sans-serif; padding: 20px; }
        h1 { text-align: center; color: #2980b9; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background: #eee; }
        .text-center { text-align: center; }
        .total { font-weight: bold; background: #eafaf1; }
        @media print { .no-print { display: none; } }
    </style>
</head>
<body>
    <button class="no-print" onclick="window.print()" style="padding:10px 20px; cursor:pointer;">IMPRIMIR</button>
    
    <h1>Inventario Físico - Housekeeping</h1>
    <p>Fecha de corte: <?php echo date("d/m/Y H:i"); ?></p>

    <table>
        <thead>
            <tr>
                <th>Ítem</th>
                <th>Categoría</th>
                <th class="text-center">Stock Limpio</th>
                <th class="text-center">Stock Sucio</th>
                <th class="text-center">TOTAL REAL</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = mysqli_fetch_assoc($res)): ?>
            <tr>
                <td><?php echo $row['nombre']; ?></td>
                <td><?php echo $row['tipo']; ?></td>
                <td class="text-center"><?php echo $row['stock_actual']; ?></td>
                <td class="text-center"><?php echo $row['stock_sucio']; ?></td>
                <td class="text-center total"><?php echo $row['stock_actual'] + $row['stock_sucio']; ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>