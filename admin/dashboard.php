<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima');

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// --- CÁLCULOS MATEMÁTICOS ---

// 1. OCUPACIÓN ACTUAL
$sqlTotalHab = "SELECT COUNT(*) as total FROM habitaciones";
$sqlOcupadas = "SELECT COUNT(*) as total FROM habitaciones WHERE estado = 'ocupada'";
$total_hab = mysqli_fetch_assoc(mysqli_query($conexion, $sqlTotalHab))['total'];
$ocupadas = mysqli_fetch_assoc(mysqli_query($conexion, $sqlOcupadas))['total'];
$porcentaje_ocupacion = ($total_hab > 0) ? round(($ocupadas / $total_hab) * 100) : 0;

// 2. INGRESOS DEL MES ACTUAL (Reservas Web + Checkouts + Ventas Directas)
$mes_actual = date('m');
$anio_actual = date('Y');

// A. Reservas Web
$sqlWeb = "SELECT SUM(pago_monto) as total FROM reservaciones WHERE (estado='confirmada' OR estado='checkin') AND MONTH(fecha_pago) = '$mes_actual' AND YEAR(fecha_pago) = '$anio_actual'";
$ingreso_web = mysqli_fetch_assoc(mysqli_query($conexion, $sqlWeb))['total'];

// B. Hotel (Estancias Finalizadas)
$sqlHotel = "SELECT SUM(total_habitacion + total_consumos) as total FROM estancias WHERE estado='finalizada' AND MONTH(fecha_salida) = '$mes_actual' AND YEAR(fecha_salida) = '$anio_actual'";
$ingreso_hotel = mysqli_fetch_assoc(mysqli_query($conexion, $sqlHotel))['total'];

// C. Ventas Directas
$sqlDirecta = "SELECT SUM(monto) as total FROM ventas_directas WHERE MONTH(fecha) = '$mes_actual' AND YEAR(fecha) = '$anio_actual'";
$ingreso_directa = mysqli_fetch_assoc(mysqli_query($conexion, $sqlDirecta))['total'];

$ingreso_total_mes = $ingreso_web + $ingreso_hotel + $ingreso_directa;

// 3. DATOS PARA GRÁFICO: ÚLTIMOS 7 DÍAS
$dias_grafico = [];
$montos_grafico = [];

for ($i = 6; $i >= 0; $i--) {
    $fecha_loop = date('Y-m-d', strtotime("-$i days"));
    $dias_grafico[] = date('d/m', strtotime($fecha_loop)); // Ej: 28/12

    // Sumar todo lo de ese día específico
    $s1 = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT SUM(pago_monto) as t FROM reservaciones WHERE (estado='confirmada' OR estado='checkin') AND DATE(fecha_pago) = '$fecha_loop'"))['t'];
    $s2 = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT SUM(total_habitacion + total_consumos) as t FROM estancias WHERE estado='finalizada' AND DATE(fecha_salida) = '$fecha_loop'"))['t'];
    $s3 = mysqli_fetch_assoc(mysqli_query($conexion, "SELECT SUM(monto) as t FROM ventas_directas WHERE DATE(fecha) = '$fecha_loop'"))['t'];
    
    $montos_grafico[] = ($s1 + $s2 + $s3) ? ($s1 + $s2 + $s3) : 0;
}

// 4. TOP 5 PRODUCTOS MÁS VENDIDOS
$sqlTop = "SELECT nombre_producto, SUM(cantidad) as total 
           FROM kardex WHERE tipo_movimiento = 'salida' 
           GROUP BY nombre_producto ORDER BY total DESC LIMIT 5";
$resTop = mysqli_query($conexion, $sqlTop);
$prod_nombres = [];
$prod_cantidades = [];
while($row = mysqli_fetch_assoc($resTop)){
    $prod_nombres[] = $row['nombre_producto'];
    $prod_cantidades[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard | Tulumayo Lodge</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- LIBRERÍA DE GRÁFICOS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        body { font-family: 'Montserrat', sans-serif; background: #ecf0f1; margin: 0; display: flex; height: 100vh; }
        .sidebar { width: 250px; background-color: #2E5C38; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { padding: 20px; background: #244a2d; margin: 0; text-align: center; }
        .sidebar a { padding: 15px; color: white; text-decoration: none; display: flex; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); transition:0.3s; }
        .sidebar a:hover { background-color: #3A5A40; padding-left: 25px; }
        
        .main { flex: 1; padding: 30px; overflow-y: auto; }

        /* TARJETAS KPI */
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 40px; }
        .kpi-card { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); display: flex; align-items: center; justify-content: space-between; border-left: 5px solid #ccc; }
        .kpi-info h3 { margin: 0; font-size: 0.9rem; color: #777; }
        .kpi-info p { margin: 5px 0 0; font-size: 1.8rem; font-weight: bold; color: #333; }
        .kpi-icon { font-size: 2.5rem; opacity: 0.3; }

        /* Colores KPI */
        .kpi-blue { border-color: #3498db; } .kpi-blue i { color: #3498db; }
        .kpi-green { border-color: #2ecc71; } .kpi-green i { color: #2ecc71; }
        .kpi-orange { border-color: #e67e22; } .kpi-orange i { color: #e67e22; }
        .kpi-red { border-color: #e74c3c; } .kpi-red i { color: #e74c3c; }

        /* CONTENEDOR GRÁFICOS */
        .charts-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 20px; }
        .chart-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .chart-box h3 { margin-top: 0; color: #2E5C38; border-bottom: 1px solid #eee; padding-bottom: 10px; }

        @media (max-width: 900px) { .charts-grid { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <div class="main">
        <h1 style="color:#2E5C38;">Resumen General</h1>
        
        <!-- TARJETAS KPI -->
        <div class="kpi-grid">
            <div class="kpi-card kpi-green">
                <div class="kpi-info">
                    <h3>Ingresos del Mes</h3>
                    <p>S/ <?php echo number_format($ingreso_total_mes, 2); ?></p>
                </div>
                <i class="fas fa-money-bill-wave kpi-icon"></i>
            </div>

            <div class="kpi-card kpi-blue">
                <div class="kpi-info">
                    <h3>Ocupación Actual</h3>
                    <p><?php echo $porcentaje_ocupacion; ?>%</p>
                </div>
                <i class="fas fa-bed kpi-icon"></i>
            </div>

            <div class="kpi-card kpi-orange">
                <div class="kpi-info">
                    <h3>Habitaciones Ocupadas</h3>
                    <p><?php echo $ocupadas; ?> / <?php echo $total_hab; ?></p>
                </div>
                <i class="fas fa-door-open kpi-icon"></i>
            </div>
        </div>

        <!-- GRÁFICOS -->
        <div class="charts-grid">
            <!-- Gráfico Lineal: Ventas -->
            <div class="chart-box">
                <h3><i class="fas fa-chart-area"></i> Evolución de Ventas (7 días)</h3>
                <canvas id="chartVentas"></canvas>
            </div>

            <!-- Gráfico Dona: Top Productos -->
            <div class="chart-box">
                <h3><i class="fas fa-star"></i> Top Productos</h3>
                <canvas id="chartProductos"></canvas>
            </div>
        </div>
    </div>

    <script>
        // --- 1. GRÁFICO DE VENTAS (LINEAL) ---
        const ctxVentas = document.getElementById('chartVentas').getContext('2d');
        new Chart(ctxVentas, {
            type: 'line',
            data: {
                labels: <?php echo json_encode($dias_grafico); ?>,
                datasets: [{
                    label: 'Ventas Diarias (S/)',
                    data: <?php echo json_encode($montos_grafico); ?>,
                    borderColor: '#2ecc71',
                    backgroundColor: 'rgba(46, 204, 113, 0.2)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: { responsive: true }
        });

        // --- 2. GRÁFICO DE PRODUCTOS (DONA) ---
        const ctxProd = document.getElementById('chartProductos').getContext('2d');
        new Chart(ctxProd, {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($prod_nombres); ?>,
                datasets: [{
                    data: <?php echo json_encode($prod_cantidades); ?>,
                    backgroundColor: ['#e74c3c', '#3498db', '#f1c40f', '#9b59b6', '#2c3e50']
                }]
            },
            options: { responsive: true }
        });
    </script>

</body>
</html>