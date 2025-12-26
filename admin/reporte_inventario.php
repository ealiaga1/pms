<?php
session_start();
include '../db.php';
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// FECHAS POR DEFECTO (Del mes actual)
$fecha_inicio = isset($_GET['inicio']) ? $_GET['inicio'] : date('Y-m-01');
$fecha_fin = isset($_GET['fin']) ? $_GET['fin'] : date('Y-m-d');

// CONSULTA DE MOVIMIENTOS
$sql = "SELECT * FROM kardex 
        WHERE date(fecha) BETWEEN '$fecha_inicio' AND '$fecha_fin' 
        ORDER BY fecha DESC";
$res = mysqli_query($conexion, $sql);

// CONSULTA DE STOCK ACTUAL
$sqlStock = "SELECT * FROM productos ORDER BY nombre ASC";
$resStock = mysqli_query($conexion, $sqlStock);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Inventario | Tulumayo</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #eee; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #ddd; padding-bottom: 20px; margin-bottom: 20px; }
        h1 { color: #2E5C38; margin: 0; }
        
        .filter-box { background: #f9f9f9; padding: 15px; border-radius: 5px; display: flex; gap: 10px; align-items: flex-end; margin-bottom: 30px; }
        .filter-box label { font-size: 0.8rem; font-weight: bold; display: block; margin-bottom: 5px; }
        .filter-box input { padding: 8px; border: 1px solid #ccc; border-radius: 4px; }
        .btn-filter { background: #2c3e50; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
        .btn-print { background: #e67e22; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; text-decoration: none; }

        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; margin-bottom: 30px; }
        th { background: #2E5C38; color: white; padding: 10px; text-align: left; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        
        .badge { padding: 3px 8px; border-radius: 10px; font-size: 0.75rem; color: white; }
        .b-in { background: #2ecc71; } /* Entrada */
        .b-out { background: #e74c3c; } /* Salida */

        .section-title { margin-top: 40px; margin-bottom: 15px; color: #333; border-left: 5px solid #2E5C38; padding-left: 10px; }

        @media print {
            body { background: white; }
            .container { box-shadow: none; }
            .filter-box, .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="header">
            <h1>Reporte de Inventario</h1>
            <a href="productos.php" class="btn-print">Volver</a>
        </div>

        <!-- FILTRO DE FECHAS -->
        <form class="filter-box" method="GET">
            <div>
                <label>Desde:</label>
                <input type="date" name="inicio" value="<?php echo $fecha_inicio; ?>">
            </div>
            <div>
                <label>Hasta:</label>
                <input type="date" name="fin" value="<?php echo $fecha_fin; ?>">
            </div>
            <button type="submit" class="btn-filter">Filtrar Movimientos</button>
            <button type="button" class="btn-print" onclick="window.print()" style="margin-left:auto;">Imprimir</button>
        </form>

        <!-- TABLA DE MOVIMIENTOS (KARDEX) -->
        <h2 class="section-title">Movimientos en el periodo</h2>
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Producto</th>
                    <th>Movimiento</th>
                    <th>Cantidad</th>
                    <th>Detalle</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                if (mysqli_num_rows($res) > 0) {
                    while($row = mysqli_fetch_assoc($res)) { 
                        $tipoClass = ($row['tipo_movimiento'] == 'entrada') ? 'b-in' : 'b-out';
                ?>
                <tr>
                    <td><?php echo date("d/m/Y H:i", strtotime($row['fecha'])); ?></td>
                    <td><strong><?php echo $row['nombre_producto']; ?></strong></td>
                    <td><span class="badge <?php echo $tipoClass; ?>"><?php echo strtoupper($row['tipo_movimiento']); ?></span></td>
                    <td style="font-weight:bold;"><?php echo $row['cantidad']; ?></td>
                    <td><?php echo $row['observacion']; ?></td>
                </tr>
                <?php 
                    } 
                } else {
                    echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>No hay movimientos en estas fechas.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <!-- STOCK ACTUAL (CORTE DE INVENTARIO) -->
        <h2 class="section-title">Corte de Inventario Actual (Stock Hoy)</h2>
        <table>
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio Venta</th>
                    <th>Stock Actual</th>
                    <th>Valorización (Aprox)</th>
                </tr>
            </thead>
            <tbody>
                <?php while($prod = mysqli_fetch_assoc($resStock)) { ?>
                <tr>
                    <td><?php echo $prod['nombre']; ?></td>
                    <td>S/ <?php echo number_format($prod['precio'], 2); ?></td>
                    <td style="font-weight:bold; color: <?php echo ($prod['stock']<10)?'red':'green'; ?>">
                        <?php echo $prod['stock']; ?> un.
                    </td>
                    <td>S/ <?php echo number_format($prod['precio'] * $prod['stock'], 2); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>

</body>
</html>