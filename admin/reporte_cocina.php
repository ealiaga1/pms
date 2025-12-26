<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima');

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// Consultar todos los insumos ordenados alfabéticamente
$sql = "SELECT * FROM cocina_insumos ORDER BY nombre ASC";
$res = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Stock Cocina | Tulumayo</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #555; padding: 40px; display: flex; justify-content: center; }
        
        .hoja { background: white; width: 800px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); min-height: 100vh; }
        
        header { border-bottom: 2px solid #ddd; padding-bottom: 20px; margin-bottom: 30px; display: flex; justify-content: space-between; align-items: center; }
        h1 { margin: 0; color: #d35400; font-size: 1.8rem; text-transform: uppercase; }
        .fecha { font-size: 0.9rem; color: #777; text-align: right; }

        table { width: 100%; border-collapse: collapse; font-size: 0.95rem; }
        th { background: #f4f4f4; color: #333; font-weight: bold; padding: 12px; text-align: left; border-bottom: 2px solid #ccc; }
        td { padding: 10px 12px; border-bottom: 1px solid #eee; }
        
        /* Filas alternas para leer mejor */
        tr:nth-child(even) { background-color: #fafafa; }

        /* Estados de Stock */
        .bajo { color: #c0392b; font-weight: bold; }
        .ok { color: #27ae60; }

        .btn-print { 
            position: fixed; bottom: 30px; right: 30px; 
            background: #d35400; color: white; border: none; 
            padding: 15px 30px; border-radius: 50px; 
            font-weight: bold; cursor: pointer; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.3); 
        }

        /* ESTILOS DE IMPRESIÓN */
        @media print {
            body { background: white; padding: 0; }
            .hoja { width: 100%; box-shadow: none; padding: 0; }
            .btn-print { display: none; }
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="btn-print">IMPRIMIR LISTADO</button>

    <div class="hoja">
        <header>
            <div>
                <h1>Inventario de Cocina</h1>
                <p style="margin:5px 0 0 0; color:#555;">Tulumayo Lodge - Área de Alimentos y Bebidas</p>
            </div>
            <div class="fecha">
                Fecha de Corte:<br>
                <strong><?php echo date("d/m/Y"); ?></strong><br>
                Hora: <?php echo date("H:i"); ?>
            </div>
        </header>

        <table>
            <thead>
                <tr>
                    <th style="width: 50%;">Insumo / Producto</th>
                    <th style="width: 15%;">Unidad</th>
                    <th style="width: 20%; text-align:center;">Stock Actual</th>
                    <th style="width: 15%;">Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_items = 0;
                while($i = mysqli_fetch_assoc($res)) { 
                    $total_items++;
                    $alerta = ($i['stock_actual'] <= $i['stock_minimo']);
                    $clase = $alerta ? 'bajo' : 'ok';
                    $texto = $alerta ? 'BAJO' : 'Normal';
                ?>
                <tr>
                    <td><?php echo $i['nombre']; ?></td>
                    <td><?php echo $i['unidad']; ?></td>
                    <td style="text-align:center; font-weight:bold; font-size:1.1rem;">
                        <?php echo $i['stock_actual']; ?>
                    </td>
                    <td class="<?php echo $clase; ?>"><?php echo $texto; ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>

        <div style="margin-top: 30px; padding-top: 10px; border-top: 2px solid #ddd;">
            <strong>Total de Ítems registrados:</strong> <?php echo $total_items; ?>
        </div>

        <br><br><br>
        <div style="display:flex; justify-content:space-between; margin-top:50px;">
            <div style="text-align:center; width:200px; border-top:1px solid #000; padding-top:5px;">
                Firma Responsable Almacén
            </div>
            <div style="text-align:center; width:200px; border-top:1px solid #000; padding-top:5px;">
                Firma Administración
            </div>
        </div>

    </div>

</body>
</html>