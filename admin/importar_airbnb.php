<?php
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

include '../db.php';
date_default_timezone_set('America/Lima'); // Asegurar zona horaria

// ENLACE DE AIRBNB
$url_airbnb = "https://www.airbnb.com.pe/calendar/ical/1582674609744819642.ics?t=a1a75efe23ad4d75939a2369f97e1c4c";

// 1. Descargar archivo
$ics_content = @file_get_contents($url_airbnb);
$error_msg = "";
$contador = 0;
$hoy = date('Y-m-d'); // Fecha de hoy

if ($ics_content) {
    // 2. Limpiar reservas FUTURAS de Airbnb para re-importarlas actualizadas
    // (No borramos las pasadas para mantener historial si ya existían)
    mysqli_query($conexion, "DELETE FROM reservaciones WHERE origen = 'Airbnb' AND fecha_salida >= '$hoy'");

    // 3. Analizar el archivo
    preg_match_all('/BEGIN:VEVENT.*?END:VEVENT/s', $ics_content, $eventos);

    foreach ($eventos[0] as $evento) {
        preg_match('/DTSTART;VALUE=DATE:(\d{8})/', $evento, $inicio);
        preg_match('/DTEND;VALUE=DATE:(\d{8})/', $evento, $fin);
        preg_match('/SUMMARY:(.*?)\r\n/', $evento, $resumen);

        if (isset($inicio[1]) && isset($fin[1])) {
            $llegada = date('Y-m-d', strtotime($inicio[1]));
            $salida = date('Y-m-d', strtotime($fin[1]));
            
            // --- FILTRO NUEVO: IGNORAR PASADO ---
            // Si la fecha de salida es menor a hoy, es historia antigua. No la importamos.
            if ($salida < $hoy) {
                continue; 
            }

            // Limpieza del nombre (Airbnb suele mandar "Reserved" o códigos)
            $raw_summary = isset($resumen[1]) ? $resumen[1] : "Reserva Airbnb";
            $nombre_cliente = ($raw_summary == "Reserved") ? "Bloqueo Airbnb" : $raw_summary;

            // 4. Guardar en Base de Datos
            $sql = "INSERT INTO reservaciones (nombre_cliente, fecha_llegada, fecha_salida, tipo_habitacion, estado, origen, metodo_pago) 
                    VALUES ('$nombre_cliente', '$llegada', '$salida', 'Importada', 'confirmada', 'Airbnb', 'Airbnb Web')";
            
            if(mysqli_query($conexion, $sql)) {
                $contador++;
            }
        }
    }
} else {
    $error_msg = "No se pudo conectar con Airbnb. Verifique su conexión.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sincronización Airbnb</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { margin: 0; padding: 0; font-family: 'Montserrat', sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; }
        .sync-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; max-width: 450px; width: 90%; animation: slideUp 0.5s ease-out; }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
        .icon-circle { width: 80px; height: 80px; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 20px; }
        .icon-success { background-color: #2ecc71; box-shadow: 0 5px 15px rgba(46, 204, 113, 0.4); }
        .icon-error { background-color: #e74c3c; box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4); }
        h1 { color: #333; margin: 0 0 10px; font-size: 1.5rem; }
        p { color: #666; margin-bottom: 30px; font-size: 0.95rem; line-height: 1.6; }
        .btn-back { display: inline-block; background-color: #2E5C38; color: white; text-decoration: none; padding: 12px 30px; border-radius: 50px; font-weight: bold; transition: all 0.3s ease; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .btn-back:hover { background-color: #1e3d24; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.3); }
        .stat-box { background: #f9f9f9; border: 1px solid #eee; border-radius: 10px; padding: 15px; margin-bottom: 25px; }
        .stat-number { font-size: 1.8rem; font-weight: bold; color: #2E5C38; display: block; }
        .stat-label { font-size: 0.8rem; text-transform: uppercase; color: #999; letter-spacing: 1px; }
    </style>
</head>
<body>
    <div class="sync-card">
        <?php if ($error_msg == ""): ?>
            <div class="icon-circle icon-success"><i class="fas fa-check"></i></div>
            <h1>¡Sincronización Exitosa!</h1>
            <div class="stat-box">
                <span class="stat-number"><?php echo $contador; ?></span>
                <span class="stat-label">Reservas Nuevas / Futuras</span>
            </div>
            <p>Se han importado las fechas ocupadas desde Airbnb. Las reservas pasadas han sido ignoradas.</p>
        <?php else: ?>
            <div class="icon-circle icon-error"><i class="fas fa-exclamation-triangle"></i></div>
            <h1>Ocurrió un Problema</h1>
            <p><?php echo $error_msg; ?></p>
        <?php endif; ?>
        <a href="panel.php" class="btn-back"><i class="fas fa-arrow-left"></i> Volver al Calendario</a>
    </div>
</body>
</html>