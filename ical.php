<?php
include 'db.php'; 

// Recibir el tipo de habitación por URL (Ej: ical.php?tipo=Matrimonial)
// Si no envían nada, muestra todo (para pruebas)
$filtro_tipo = isset($_GET['tipo']) ? $_GET['tipo'] : '';

header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="reservas.ics"');

echo "BEGIN:VCALENDAR\r\n";
echo "VERSION:2.0\r\n";
echo "PRODID:-//Tulumayo Lodge//PMS//PE\r\n";
echo "CALSCALE:GREGORIAN\r\n";

// CLÁUSULA WHERE DINÁMICA
$whereWeb = "WHERE estado = 'confirmada'";
$whereHotel = "WHERE estado = 'activa'";

if (!empty($filtro_tipo)) {
    // Si piden un tipo específico, filtramos
    $whereWeb .= " AND tipo_habitacion = '$filtro_tipo'";
    
    // Para las estancias (Rack), tenemos que hacer un JOIN para saber el tipo
    // (Ajustaremos la query abajo)
}

// 1. RESERVAS WEB
$sqlWeb = "SELECT id, nombre_cliente, fecha_llegada, fecha_salida FROM reservaciones $whereWeb";
$resWeb = mysqli_query($conexion, $sqlWeb);

while ($row = mysqli_fetch_assoc($resWeb)) {
    imprimirEvento($row['id'], $row['nombre_cliente'], $row['fecha_llegada'], $row['fecha_salida'], 'Web');
}

// 2. ESTANCIAS (RACK)
// Hacemos JOIN con habitaciones para filtrar por tipo
$sqlHotel = "SELECT e.id, e.nombre_huesped, e.fecha_ingreso, h.tipo 
             FROM estancias e 
             JOIN habitaciones h ON e.id_habitacion = h.id 
             WHERE e.estado = 'activa'";

if (!empty($filtro_tipo)) {
    $sqlHotel .= " AND h.tipo = '$filtro_tipo'";
}

$resHotel = mysqli_query($conexion, $sqlHotel);

while ($row = mysqli_fetch_assoc($resHotel)) {
    $inicio = date('Ymd', strtotime($row['fecha_ingreso']));
    $fin = date('Ymd', strtotime("+1 day")); // Bloqueo preventivo de 1 día
    
    echo "BEGIN:VEVENT\r\n";
    echo "DTSTART;VALUE=DATE:" . $inicio . "\r\n";
    echo "DTEND;VALUE=DATE:" . $fin . "\r\n";
    echo "SUMMARY:Ocupado\r\n";
    echo "UID:hotel-" . $row['id'] . "@tulumayo.com\r\n";
    echo "END:VEVENT\r\n";
}

echo "END:VCALENDAR";

function imprimirEvento($id, $nombre, $llegada, $salida, $origen) {
    $dtStart = date('Ymd', strtotime($llegada));
    $dtEnd   = date('Ymd', strtotime($salida));
    echo "BEGIN:VEVENT\r\n";
    echo "DTSTART;VALUE=DATE:" . $dtStart . "\r\n";
    echo "DTEND;VALUE=DATE:" . $dtEnd . "\r\n";
    echo "SUMMARY:Ocupado\r\n";
    echo "UID:reserva-" . $id . "@tulumayo.com\r\n";
    echo "END:VEVENT\r\n";
}
?>