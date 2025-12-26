<?php
include '../db.php';

if (isset($_GET['id_mesa'])) {
    $id_mesa = $_GET['id_mesa'];

    // 1. Buscamos el pedido abierto de esta mesa
    $sqlPedido = "SELECT id FROM pedidos_restaurante WHERE id_mesa = '$id_mesa' AND estado = 'abierto'";
    $resPedido = mysqli_query($conexion, $sqlPedido);
    $ped = mysqli_fetch_assoc($resPedido);

    if ($ped) {
        $id_pedido = $ped['id'];

        // 2. Buscamos los detalles (Platos)
        $sqlDet = "SELECT d.cantidad, d.precio_unitario, p.nombre, d.notas 
                   FROM detalle_pedido d 
                   JOIN productos p ON d.id_producto = p.id 
                   WHERE d.id_pedido = '$id_pedido'";
        
        $resDet = mysqli_query($conexion, $sqlDet);
        
        $items = array();
        while($row = mysqli_fetch_assoc($resDet)) {
            $items[] = $row;
        }

        // Devolvemos la lista en formato JSON (Texto para JS)
        echo json_encode($items);
    } else {
        echo json_encode([]);
    }
}
?>