<?php
// Iniciamos sesión para seguridad
session_start();
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

include '../db.php';

// --- PEGA AQUÍ TU ENLACE DE BOOKING.COM ---
// (Lo consiguies en Extranet Booking -> Calendario -> Sincronizar -> Exportar/Importar)
$url_booking = "AQUI_PEGA_EL_LINK_ICS_DE_BOOKING"; 

// 1. Descargar el archivo .ics
$ics_content = @file_get_contents($url_booking);
$error_msg = "";
$contador = 0;

if ($ics_content) {
    $hoy = date('Y-m-d');
    
    // 2. Limpiar reservas antiguas de Booking para no duplicar
    mysqli_query($conexion, "DELETE FROM reservaciones WHERE origen = 'Booking' AND fecha_llegada >= '$hoy'");

    // 3. Analizar el archivo
    preg_match_all('/BEGIN:VEVENT.*?END:VEVENT/s', $ics_content, $eventos);

    foreach ($eventos[0] as $evento) {
        preg_match('/DTSTART;VALUE=DATE:(\d{8})/', $evento, $inicio);
        preg_match('/DTEND;VALUE=DATE:(\d{8})/', $evento, $fin);
        preg_match('/SUMMARY:(.*?)\r\n/', $evento, $resumen);

        if (isset($inicio[1]) && isset($fin[1])) {
            $llegada = date('Y-m-d', strtotime($inicio[1]));
            $salida = date('Y-m-d', strtotime($fin[1]));
            
            // Booking a veces no da el nombre, solo dice "CLOSED" o "RESERVED"
            $nombre_cliente = isset($resumen[1]) ? $resumen[1] : "Reserva Booking";

            // 4. Guardar en Base de Datos (Origen: Booking)
            $sql = "INSERT INTO reservaciones (nombre_cliente, fecha_llegada, fecha_salida, tipo_habitacion, estado, origen, metodo_pago) 
                    VALUES ('$nombre_cliente', '$llegada', '$salida', 'Importada', 'confirmada', 'Booking', 'Booking Web')";
            
            if(mysqli_query($conexion, $sql)) {
                $contador++;
            }
        }
    }
} else {
    $error_msg = "No se pudo conectar con Booking.com. Verifica el enlace .ics.";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sincronización Booking | Tulumayo</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Mismos estilos que Airbnb pero en Azul */
        body { font-family: 'Montserrat', sans-serif; background-color: #f4f7f6; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .sync-card { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); text-align: center; max-width: 450px; width: 90%; }
        .icon-circle { width: 80px; height: 80px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 40px; margin: 0 auto 20px; color: white; }
        .icon-success { background-color: #003580; box-shadow: 0 5px 15px rgba(0, 53, 128, 0.4); } /* Azul Booking */
        .icon-error { background-color: #e74c3c; }
        .stat-number { font-size: 1.8rem; font-weight: bold; color: #003580; display: block; }
        .btn-back { display: inline-block; background-color: #2E5C38; color: white; text-decoration: none; padding: 12px 30px; border-radius: 50px; font-weight: bold; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="sync-card">
        <?php if ($error_msg == ""): ?>
            <div class="icon-circle icon-success"><i class="fas fa-check"></i></div>
            <h1 style="color:#003580">¡Booking Sincronizado!</h1>
            <div style="background:#f9f9f9; padding:15px; border-radius:10px; margin:20px 0;">
                <span class="stat-number"><?php echo $contador; ?></span>
                <span style="font-size:0.8rem; text-transform:uppercase; color:#999;">Reservas Importadas</span>
            </div>
        <?php else: ?>
            <div class="icon-circle icon-error"><i class="fas fa-exclamation-triangle"></i></div>
            <h1>Error</h1>
            <p><?php echo $error_msg; ?></p>
        <?php endif; ?>
        <a href="panel.php" class="btn-back">Volver al Calendario</a>
    </div>
</body>
</html>