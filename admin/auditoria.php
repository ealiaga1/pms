<?php
session_start();
include '../db.php';

// SEGURIDAD MÁXIMA: Solo Admin puede ver esto
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] != 'admin') { 
    header("Location: panel.php"); 
    exit(); 
}

// Filtros básicos (Últimos 100 eventos por defecto)
$sql = "SELECT a.*, u.usuario, u.nombre_completo 
        FROM auditoria a 
        LEFT JOIN usuarios_admin u ON a.id_usuario = u.id 
        ORDER BY a.fecha DESC LIMIT 100";
$resultado = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Auditoría del Sistema | Tulumayo</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #ecf0f1; margin: 0; display: flex; height: 100vh; }
        
        /* SIDEBAR */
        .sidebar { width: 250px; background-color: #2E5C38; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background: #244a2d; margin: 0; border-bottom: 1px solid #3A5A40; }
        .sidebar a { padding: 15px 20px; color: white; text-decoration: none; display: flex; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.3s; }
        .sidebar a:hover { background-color: #3A5A40; padding-left: 25px; }
        
        /* MAIN */
        .main { flex: 1; padding: 30px; overflow-y: auto; }
        
        .header-main { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        
        /* TABLA ESTILO LOG */
        .log-table { width: 100%; border-collapse: collapse; background: white; font-family: 'Courier New', monospace; font-size: 0.9rem; box-shadow: 0 4px 10px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; }
        .log-table th, .log-table td { padding: 10px 12px; text-align: left; border-bottom: 1px solid #eee; }
        .log-table th { background: #333; color: white; text-transform: uppercase; font-size: 0.8rem; }
        .log-table tr:hover { background: #f9f9f9; }

        /* Colores por acción */
        .tag-accion { padding: 2px 6px; border-radius: 4px; font-weight: bold; font-size: 0.75rem; color: white; }
        .bg-login { background: #3498db; }       
        .bg-delete { background: #e74c3c; }      
        .bg-create { background: #2ecc71; }      
        .bg-update { background: #f39c12; }      
        .bg-money { background: #8e44ad; }       
        .bg-default { background: #95a5a6; }

        /* --- ESTILOS DE IMPRESIÓN (LA MAGIA) --- */
        @media print {
            /* 1. Ocultar todo lo que no sirve en papel */
            .sidebar { display: none !important; }
            .header-main button { display: none !important; } /* Botón imprimir */
            
            /* 2. Ajustar el contenedor principal */
            body { background: white; height: auto; display: block; margin: 0; padding: 0; }
            .main { padding: 0; overflow: visible; width: 100%; }
            
            /* 3. Estilo de tabla para impresión */
            .log-table { 
                box-shadow: none; 
                border: 1px solid #000; 
                font-size: 10px; /* Letra más pequeña para que quepa */
            }
            .log-table th { 
                background-color: #ddd !important; 
                color: #000 !important; 
                border-bottom: 1px solid #000;
                -webkit-print-color-adjust: exact; /* Forzar color de fondo */
            }
            .log-table td { border-bottom: 1px solid #ccc; }
            
            /* 4. Encabezado solo para impresión */
            .header-main h1 { font-size: 1.5rem; text-align: center; width: 100%; margin-bottom: 10px; }
            .header-main::after {
                content: "Reporte generado: <?php echo date('d/m/Y H:i'); ?>";
                display: block; width: 100%; text-align: center; font-size: 0.8rem; color: #555;
            }
        }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
   <?php include 'sidebar.php'; ?>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="main">
        <div class="header-main">
            <h1><i class="fas fa-user-secret"></i> Registro de Actividad (Auditoría)</h1>
            <button onclick="window.print()" style="background:#333; color:white; border:none; padding:10px 20px; cursor:pointer; font-weight:bold; border-radius:5px;">
                <i class="fas fa-print"></i> Imprimir Log
            </button>
        </div>

        <table class="log-table">
            <thead>
                <tr>
                    <th style="width:150px;">Fecha / Hora</th>
                    <th style="width:150px;">Usuario</th>
                    <th style="width:100px;">Acción</th>
                    <th>Detalle del Evento</th>
                    <th style="width:100px;">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($resultado)): 
                    // Asignación de colores
                    $bg = "bg-default";
                    $acc = strtoupper($row['accion']);
                    if(strpos($acc, 'LOGIN') !== false) $bg = "bg-login";
                    if(strpos($acc, 'ELIMINAR') !== false || strpos($acc, 'CANCELAR') !== false) $bg = "bg-delete";
                    if(strpos($acc, 'CREAR') !== false || strpos($acc, 'NUEVO') !== false) $bg = "bg-create";
                    if(strpos($acc, 'COBRO') !== false || strpos($acc, 'CAJA') !== false) $bg = "bg-money";
                    if(strpos($acc, 'UPDATE') !== false || strpos($acc, 'EDITAR') !== false) $bg = "bg-update";
                ?>
                <tr>
                    <td><?php echo date("d/m/Y H:i:s", strtotime($row['fecha'])); ?></td>
                    <td>
                        <strong><?php echo $row['usuario'] ? $row['usuario'] : 'Sistema'; ?></strong><br>
                        <small style="color:#777;"><?php echo $row['nombre_completo']; ?></small>
                    </td>
                    <td><span class="tag-accion <?php echo $bg; ?>"><?php echo $row['accion']; ?></span></td>
                    <td><?php echo $row['detalle']; ?></td>
                    <td style="color:#999; font-size:0.8rem;"><?php echo $row['ip']; ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

</body>
</html>