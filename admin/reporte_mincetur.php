<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima');
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// FILTRO DE MES Y AÑO
$mes = isset($_GET['mes']) ? $_GET['mes'] : date('m');
$anio = isset($_GET['anio']) ? $_GET['anio'] : date('Y');

// 1. CAPÍTULO II: ARRIBOS Y PERNOCTACIONES POR TIPO DE HABITACIÓN
// (Simplificado: Cuenta llegadas en el mes)
$sqlCap2 = "SELECT h.tipo, COUNT(*) as arribos_hab, SUM(e.nro_personas) as arribos_pers 
            FROM estancias e 
            JOIN habitaciones h ON e.id_habitacion = h.id 
            WHERE MONTH(e.fecha_ingreso) = '$mes' AND YEAR(e.fecha_ingreso) = '$anio'
            GROUP BY h.tipo";
$resCap2 = mysqli_query($conexion, $sqlCap2);

// 2. CAPÍTULO III: ARRIBOS POR DÍA
$sqlCap3 = "SELECT DAY(fecha_ingreso) as dia, SUM(nro_personas) as total 
            FROM estancias 
            WHERE MONTH(fecha_ingreso) = '$mes' AND YEAR(fecha_ingreso) = '$anio' 
            GROUP BY DAY(fecha_ingreso)";
$resCap3 = mysqli_query($conexion, $sqlCap3);
$dias_mes = array_fill(1, 31, 0); // Array vacío del 1 al 31
while($d = mysqli_fetch_assoc($resCap3)) {
    $dias_mes[$d['dia']] = $d['total'];
}

// 3. CAPÍTULO IV: PROCEDENCIA (REGIONES Y PAÍSES)
$sqlCap4 = "SELECT lugar_origen, procedencia, SUM(nro_personas) as total 
            FROM estancias 
            WHERE MONTH(fecha_ingreso) = '$mes' AND YEAR(fecha_ingreso) = '$anio' 
            GROUP BY lugar_origen, procedencia ORDER BY total DESC";
$resCap4 = mysqli_query($conexion, $sqlCap4);

// 4. CAPÍTULO V: MOTIVO DE VIAJE
$sqlCap5 = "SELECT motivo_viaje, SUM(nro_personas) as total 
            FROM estancias 
            WHERE MONTH(fecha_ingreso) = '$mes' AND YEAR(fecha_ingreso) = '$anio' 
            GROUP BY motivo_viaje";
$resCap5 = mysqli_query($conexion, $sqlCap5);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte MINCETUR</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #eee; padding: 20px; }
        .hoja { background: white; max-width: 900px; margin: 0 auto; padding: 40px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        h1, h2, h3 { color: #2c3e50; text-transform: uppercase; }
        .header { text-align: center; border-bottom: 2px solid #2c3e50; padding-bottom: 20px; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; font-size: 0.9rem; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
        th { background: #f4f4f4; }
        .left { text-align: left; }
        
        .filter-box { text-align: center; margin-bottom: 20px; }
        .btn { padding: 10px 20px; background: #2c3e50; color: white; border: none; cursor: pointer; }
        
        @media print { .filter-box { display: none; } body { padding: 0; } .hoja { box-shadow: none; } }
    </style>
</head>
<body>

    <div class="filter-box">
        <form method="GET">
            <label>Mes:</label>
            <select name="mes">
                <?php for($i=1; $i<=12; $i++) echo "<option value='$i' ".($mes==$i?'selected':'').">$i</option>"; ?>
            </select>
            <label>Año:</label>
            <input type="number" name="anio" value="<?php echo $anio; ?>" style="width:60px;">
            <button type="submit" class="btn">Generar Reporte</button>
            <button type="button" onclick="window.print()" class="btn" style="background:#e67e22;">Imprimir</button>
            <a href="panel.php" class="btn" style="background:#7f8c8d; text-decoration:none;">Volver</a>
        </form>
    </div>

    <div class="hoja">
        <div class="header">
            <img src="../img/logo.png" width="80"><br>
            <h2>Estadística Mensual de Turismo</h2>
            <p>Periodo: <?php echo "$mes / $anio"; ?></p>
        </div>

        <!-- CAPITULO II -->
        <h3>Cap. II: Movimiento de Alojamiento (Arribos)</h3>
        <table>
            <thead>
                <tr>
                    <th class="left">Tipo de Habitación</th>
                    <th>N° Habitaciones Ocupadas</th>
                    <th>N° Personas (Arribos)</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $totHab = 0; $totPers = 0;
                while($row = mysqli_fetch_assoc($resCap2)): 
                    $totHab += $row['arribos_hab'];
                    $totPers += $row['arribos_pers'];
                ?>
                <tr>
                    <td class="left"><?php echo $row['tipo']; ?></td>
                    <td><?php echo $row['arribos_hab']; ?></td>
                    <td><?php echo $row['arribos_pers']; ?></td>
                </tr>
                <?php endwhile; ?>
                <tr style="background:#eee; font-weight:bold;">
                    <td class="left">TOTAL</td>
                    <td><?php echo $totHab; ?></td>
                    <td><?php echo $totPers; ?></td>
                </tr>
            </tbody>
        </table>

        <!-- CAPITULO III -->
        <h3>Cap. III: Arribos por Día</h3>
        <table>
            <tr>
                <?php for($i=1; $i<=16; $i++) echo "<th>$i</th>"; ?>
            </tr>
            <tr>
                <?php for($i=1; $i<=16; $i++) echo "<td>".($dias_mes[$i] > 0 ? $dias_mes[$i] : '')."</td>"; ?>
            </tr>
            <tr>
                <?php for($i=17; $i<=31; $i++) echo "<th>$i</th>"; ?>
                <th>TOTAL</th>
            </tr>
            <tr>
                <?php for($i=17; $i<=31; $i++) echo "<td>".($dias_mes[$i] > 0 ? $dias_mes[$i] : '')."</td>"; ?>
                <td><strong><?php echo array_sum($dias_mes); ?></strong></td>
            </tr>
        </table>

        <!-- CAPITULO IV -->
        <h3>Cap. IV: Lugar de Residencia</h3>
        <table>
            <thead>
                <tr>
                    <th class="left">Región / País</th>
                    <th>Procedencia</th>
                    <th>N° Personas</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($resCap4)): ?>
                <tr>
                    <td class="left"><?php echo strtoupper($row['lugar_origen']); ?></td>
                    <td><?php echo strtoupper($row['procedencia']); ?></td>
                    <td><?php echo $row['total']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- CAPITULO V -->
        <h3>Cap. V: Motivo de Viaje</h3>
        <ul>
            <?php while($row = mysqli_fetch_assoc($resCap5)): ?>
                <li><strong><?php echo $row['motivo_viaje']; ?>:</strong> <?php echo $row['total']; ?> personas</li>
            <?php endwhile; ?>
        </ul>

    </div>

</body>
</html>