<?php
session_start();
include '../db.php';
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// Configurar cabeceras para forzar descarga Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Clientes_Tulumayo_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Consulta Unificada
$sql = "
    SELECT nombre_cliente as nombre, email, telefono, 'Web' as tipo_doc, '' as numero, fecha_solicitud as fecha
    FROM reservaciones WHERE nombre_cliente != ''
    
    UNION ALL
    
    SELECT nombre_huesped as nombre, '' as email, '' as telefono, tipo_doc, num_doc as numero, fecha_ingreso as fecha
    FROM estancias WHERE nombre_huesped != ''
    
    ORDER BY fecha DESC
";

$resultado = mysqli_query($conexion, $sql);
?>

<table border="1">
    <thead>
        <tr style="background-color: #2E5C38; color: white;">
            <th>FECHA REGISTRO</th>
            <th>NOMBRE / RAZON SOCIAL</th>
            <th>TIPO DOC</th>
            <th>NUMERO DOC</th>
            <th>TELEFONO</th>
            <th>EMAIL</th>
        </tr>
    </thead>
    <tbody>
        <?php while($row = mysqli_fetch_assoc($resultado)): ?>
        <tr>
            <td><?php echo date("d/m/Y", strtotime($row['fecha'])); ?></td>
            <td><?php echo mb_convert_encoding($row['nombre'], 'UTF-16LE', 'UTF-8'); ?></td> <!-- Fix para tildes en Excel -->
            <td><?php echo $row['tipo_doc']; ?></td>
            <td><?php echo $row['numero']; ?></td>
            <td><?php echo $row['telefono']; ?></td>
            <td><?php echo $row['email']; ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>