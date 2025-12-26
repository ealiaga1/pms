<?php
include '../db.php';

if (isset($_GET['id_habitacion'])) {
    $id_hab = $_GET['id_habitacion'];

    // 1. Buscamos la estancia activa
    $sqlEst = "SELECT id FROM estancias WHERE id_habitacion = '$id_hab' AND estado = 'activa'";
    $resEst = mysqli_query($conexion, $sqlEst);
    $estancia = mysqli_fetch_assoc($resEst);

    if ($estancia) {
        $id_estancia = $estancia['id'];

        // 2. Buscamos los consumos
        $sqlCon = "SELECT detalle, monto, fecha FROM consumos WHERE id_estancia = '$id_estancia' ORDER BY fecha DESC";
        $resCon = mysqli_query($conexion, $sqlCon);
        
        $lista = array();
        while($row = mysqli_fetch_assoc($resCon)) {
            $lista[] = $row;
        }
        
        echo json_encode($lista);
    } else {
        echo json_encode([]);
    }
}
?>