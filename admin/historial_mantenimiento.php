<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima');

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// CONSULTA DE MANTENIMIENTO
$sql = "SELECT m.*, h.nombre as habitacion, u.usuario 
        FROM mantenimiento m 
        JOIN habitaciones h ON m.id_habitacion = h.id 
        LEFT JOIN usuarios_admin u ON m.usuario_reporto = u.id 
        ORDER BY m.fecha_reporte DESC";
$res = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Historial Mantenimiento | Tulumayo</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #ecf0f1; margin: 0; display: flex; height: 100vh; }
        
        /* SIDEBAR (Estándar) */
        .sidebar { width: 250px; background-color: #2E5C38; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background: #244a2d; margin: 0; border-bottom: 1px solid #3A5A40; }
        .sidebar a { padding: 15px 20px; color: white; text-decoration: none; display: flex; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.3s; }
        .sidebar a:hover { background-color: #3A5A40; padding-left: 25px; } 
        
        /* MAIN CONTENT */
        .main { flex: 1; padding: 30px; overflow-y: auto; }
        
        /* CABECERA */
        .header-main { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .header-main h1 { color: #2E5C38; margin: 0; }
        
        /* BOTÓN IMPRIMIR */
        .btn-print { 
            background: #2c3e50; color: white; border: none; padding: 10px 20px; 
            border-radius: 5px; cursor: pointer; font-weight: bold; display: flex; align-items: center; gap: 8px;
        }
        .btn-print:hover { background: #34495e; }

        /* TABLA ESTILIZADA */
        .card-table { background: white; border-radius: 10px; padding: 20px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        
        table { width: 100%; border-collapse: collapse; font-size: 0.9rem; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; color: #555; text-transform: uppercase; font-size: 0.8rem; letter-spacing: 1px; }
        tr:hover { background: #fdfdfd; }

        /* BADGES (Etiquetas de color) */
        .badge { padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold; color: white; }
        .p-alta { background: #e74c3c; }
        .p-media { background: #f39c12; }
        .p-baja { background: #3498db; }

        .estado-pendiente { color: #e74c3c; font-weight: bold; background: #fdedec; padding: 5px 10px; border-radius: 5px; }
        .estado-resuelto { color: #2ecc71; font-weight: bold; background: #eafaf1; padding: 5px 10px; border-radius: 5px; }

        /* ESTILOS DE IMPRESIÓN */
        @media print {
            .sidebar { display: none; } /* Ocultar menú */
            .btn-print { display: none; } /* Ocultar botón */
            .main { padding: 0; overflow: visible; }
            body { background: white; height: auto; display: block; }
            .card-table { box-shadow: none; padding: 0; }
            th { background: #ddd !important; -webkit-print-color-adjust: exact; }
            
            /* Agregar encabezado solo al imprimir */
            .header-print { display: block !important; text-align: center; margin-bottom: 20px; }
            .header-print h2 { margin: 0; }
        }
        
        .header-print { display: none; }
    </style>
</head>
<body>

    <!-- MENÚ LATERAL -->
    <?php include 'sidebar.php'; ?>

    <div class="main">
        
        <div class="header-main">
            <h1><i class="fas fa-tools"></i> Historial de Incidencias</h1>
            <button class="btn-print" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir Reporte
            </button>
        </div>

        <!-- Encabezado solo para impresión -->
        <div class="header-print">
            <h2>REPORTE DE MANTENIMIENTO</h2>
            <p>Tulumayo Lodge - Generado el: <?php echo date("d/m/Y H:i"); ?></p>
            <hr>
        </div>

        <div class="card-table">
            <table>
                <thead>
                    <tr>
                        <th>Fecha Reporte</th>
                        <th>Habitación</th>
                        <th style="width: 30%;">Detalle del Problema</th>
                        <th>Prioridad</th>
                        <th>Reportado Por</th>
                        <th>Estado</th>
                        <th>Fecha Solución</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($res)): 
                        // Colores de prioridad
                        $classPrio = 'p-baja';
                        if($row['prioridad'] == 'Alta') $classPrio = 'p-alta';
                        if($row['prioridad'] == 'Media') $classPrio = 'p-media';
                    ?>
                    <tr>
                        <td>
                            <?php echo date("d/m/Y", strtotime($row['fecha_reporte'])); ?><br>
                            <small style="color:#888;"><?php echo date("H:i", strtotime($row['fecha_reporte'])); ?></small>
                        </td>
                        <td><strong><?php echo $row['habitacion']; ?></strong></td>
                        <td><?php echo $row['descripcion']; ?></td>
                        <td><span class="badge <?php echo $classPrio; ?>"><?php echo $row['prioridad']; ?></span></td>
                        <td style="text-transform: capitalize;"><?php echo $row['usuario']; ?></td>
                        <td>
                            <?php if($row['estado']=='pendiente'): ?>
                                <span class="estado-pendiente"><i class="fas fa-clock"></i> Pendiente</span>
                            <?php else: ?>
                                <span class="estado-resuelto"><i class="fas fa-check-circle"></i> Resuelto</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                                if($row['fecha_solucion']) {
                                    echo date("d/m/Y H:i", strtotime($row['fecha_solucion']));
                                } else {
                                    echo "-";
                                }
                            ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

    </div>

</body>
</html>