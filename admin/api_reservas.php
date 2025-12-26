<?php
header('Content-Type: application/json');
include '../db.php';

// 1. CONSULTA COMPLETA
// Agregamos 'id_habitacion_asignada' a la lista
$sql = "SELECT id, nombre_cliente, email, telefono, fecha_llegada, fecha_salida, 
               estado, tipo_habitacion, hora_estimada, detalles_pago, 
               metodo_pago, pago_monto, id_habitacion_asignada 
        FROM reservaciones 
        WHERE estado != 'cancelada' AND estado != 'checkin'"; 

$resultado = mysqli_query($conexion, $sql);
$eventos = array();

while ($fila = mysqli_fetch_assoc($resultado)) {
    // Definir color
    $color = '#f39c12'; // Amarillo (Pendiente)
    if ($fila['estado'] == 'confirmada') {
        $color = '#2ecc71'; // Verde (Confirmada)
    }

    $eventos[] = array(
        'id'    => $fila['id'],
        'title' => $fila['nombre_cliente'],
        'start' => $fila['fecha_llegada'],
        'end'   => $fila['fecha_salida'],
        'color' => $color,
        // AQUÍ ENVIAMOS LOS DATOS EXTENDIDOS
        'extendedProps' => array(
            'email'       => $fila['email'],
            'telefono'    => $fila['telefono'],
            'habitacion'  => $fila['tipo_habitacion'],
            'estado'      => $fila['estado'],
            'fecha_salida'      => $fila['fecha_salida'],
            'hora'        => $fila['hora_estimada'],
            'pago_nota'   => $fila['detalles_pago'],
            'metodo'      => $fila['metodo_pago'] ? $fila['metodo_pago'] : 'No especificado',
            'pago_monto'  => $fila['pago_monto'] ? $fila['pago_monto'] : '0.00',
            // ENVIAMOS LA HABITACIÓN ASIGNADA AL FRONTEND
            'id_habitacion_asignada' => $fila['id_habitacion_asignada']
        )
    );
}

echo json_encode($eventos);
?>