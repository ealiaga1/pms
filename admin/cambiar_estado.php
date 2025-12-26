<?php
include '../db.php';

// 1. OBLIGAR ZONA HORARIA LIMA
date_default_timezone_set('America/Lima');
$ahora = date('Y-m-d H:i:s'); 

if (isset($_POST['id']) && isset($_POST['accion'])) {
    $id = $_POST['id'];
    $accion = $_POST['accion'];
    
    // Recibimos datos (con validación básica)
    $pago_nota  = isset($_POST['pago_nota']) ? $_POST['pago_nota'] : '';
    $metodo     = isset($_POST['metodo']) ? $_POST['metodo'] : '';
    $hora       = isset($_POST['hora']) ? $_POST['hora'] : '';
    
    // Validamos montos
    $monto_real = isset($_POST['monto_real']) && $_POST['monto_real'] != '' ? $_POST['monto_real'] : 0.00;
    
    // RECIBIMOS LA HABITACIÓN ASIGNADA (Si no llega, es 0)
    $id_hab_asignada = isset($_POST['id_habitacion_asignada']) ? $_POST['id_habitacion_asignada'] : 0;

    if ($accion == 'confirmar') {
        // ACTUALIZAMOS TODO EN UNA SOLA CONSULTA
        $sql = "UPDATE reservaciones 
                SET estado = 'confirmada', 
                    detalles_pago = '$pago_nota', 
                    metodo_pago = '$metodo', 
                    hora_estimada = '$hora', 
                    pago_monto = '$monto_real',
                    fecha_pago = '$ahora',
                    id_habitacion_asignada = '$id_hab_asignada'
                WHERE id = $id";
                
    } elseif ($accion == 'cancelar') {
        $sql = "UPDATE reservaciones SET estado = 'cancelada' WHERE id = $id";
        
        // Función de auditoría (asegúrate de tener la función en db.php o comenta esta línea si da error)
        if(function_exists('registrar_auditoria')) {
            registrar_auditoria($conexion, 'ELIMINAR RESERVA', "Canceló reserva ID: $id");
        }
    }

    // Ejecutar y responder
    if (mysqli_query($conexion, $sql)) {
        echo "ok";
    } else {
        echo "Error MySQL: " . mysqli_error($conexion);
    }

} else {
    echo "Error: Datos incompletos.";
}
?>