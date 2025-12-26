<?php
session_start();
include '../db.php';
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

$sql = "SELECT * FROM productos ORDER BY nombre ASC";
$resultado = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario | Tulumayo Lodge</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #ecf0f1; margin: 0; display: flex; height: 100vh; }
        .sidebar { width: 250px; background-color: #2E5C38; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background: #244a2d; margin: 0; border-bottom: 1px solid #3A5A40; }
        .sidebar a { padding: 15px 20px; color: white; text-decoration: none; display: flex; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.3s; }
        .sidebar a:hover { background-color: #3A5A40; padding-left: 25px; }
        .sidebar a.active { background-color: #2ecc71; font-weight: bold; }
        .main { flex: 1; padding: 30px; overflow-y: auto; }
        
        .header-main { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .btn-add { background: #2ecc71; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #34495e; color: white; text-transform: uppercase; font-size: 0.85rem; }
        tr:hover { background: #f9f9f9; }

        .badge-serv { background: #3498db; color: white; padding: 3px 8px; border-radius: 10px; font-size: 0.8rem; }
        .badge-prod { background: #95a5a6; color: white; padding: 3px 8px; border-radius: 10px; font-size: 0.8rem; }

        /* Modal */
        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 100; }
        .modal-content { background: white; width: 400px; margin: 5% auto; padding: 25px; border-radius: 8px; position: relative; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing:border-box; }
        
        .btn-action { border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; color: white; margin-right: 5px; }
    </style>
</head>
<body>

        <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="header-main">
    <h1>Gestión de Productos</h1>
    <div>
        <!-- Botón Reporte (Existente) -->
        <a href="reporte_inventario.php" target="_blank" style="background:#34495e; color:white; padding:10px 15px; text-decoration:none; border-radius:5px; margin-right:5px; font-weight:bold;">
            <i class="fas fa-file-alt"></i> Reporte
        </a>
        
        <!-- NUEVO BOTÓN: CIERRE DE INVENTARIO -->
        <a href="cierre_inventario.php" style="background:#e67e22; color:white; padding:10px 15px; text-decoration:none; border-radius:5px; margin-right:5px; font-weight:bold;">
            <i class="fas fa-clipboard-check"></i> Cierre Físico
        </a>

        <!-- Botón Nuevo (Existente) -->
        <button class="btn-add" onclick="abrirModalCrear()"><i class="fas fa-plus"></i> Nuevo Item</button>
    </div>
</div>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($prod = mysqli_fetch_assoc($resultado)) { 
                    // Buscamos el último "Ajuste de Inventario" en el Kardex para este producto
                    $id_prod = $prod['id'];
                    $qKardex = "SELECT cantidad, fecha FROM kardex 
                                WHERE nombre_producto = '".$prod['nombre']."' 
                                AND observacion LIKE '%Ajuste Inventario%' 
                                ORDER BY fecha DESC LIMIT 1";
                    $resKardex = mysqli_query($conexion, $qKardex);
                    $ultimo_ajuste = mysqli_fetch_assoc($resKardex);
                    
                    $stock_inicial = ($ultimo_ajuste) ? $prod['stock'] : '-'; // Si hubo ajuste, mostramos referencia
                    $fecha_corte = ($ultimo_ajuste) ? date("d/m", strtotime($ultimo_ajuste['fecha'])) : '-';
                ?>
                <tr>
                    <td><strong><?php echo $prod['nombre']; ?></strong></td>
                    <td>
                        <?php if($prod['tipo']=='servicio'): ?>
                            <span class="badge-serv">Servicio</span>
                        <?php else: ?>
                            <span class="badge-prod">Producto</span>
                        <?php endif; ?>
                    </td>
                    <td>S/ <?php echo number_format($prod['precio'], 2); ?></td>
                    
                    <!-- COLUMNA STOCK MEJORADA -->
                    <td>
                        <?php if($prod['tipo']=='servicio'): ?>
                            <span style="color:#aaa;">∞</span>
                        <?php else: ?>
                            <div style="display:flex; align-items:center; gap:10px;">
                                <span style="font-size:1.2rem; font-weight:bold; color:#2c3e50;"><?php echo $prod['stock']; ?></span>
                                
                                <!-- Información del último corte (Tooltip visual) -->
                                <?php if($ultimo_ajuste): ?>
                                    <small style="color:#7f8c8d; font-size:0.75rem;">
                                        (Corte el <?php echo $fecha_corte; ?>)
                                    </small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </td>

                    <td>
                        <button class="btn-action" style="background:#f39c12;" onclick="abrirModalEditar(
                            '<?php echo $prod['id']; ?>', 
                            '<?php echo $prod['nombre']; ?>', 
                            '<?php echo $prod['precio']; ?>', 
                            '<?php echo $prod['stock']; ?>',
                            '<?php echo $prod['tipo']; ?>'
                        )"><i class="fas fa-edit"></i></button>
                        
                        <form action="guardar_producto.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar?');">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?php echo $prod['id']; ?>">
                            <button class="btn-action" style="background:#e74c3c;"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- MODAL -->
    <div id="modalProducto" class="modal">
        <div class="modal-content">
            <span onclick="document.getElementById('modalProducto').style.display='none'" style="float:right; cursor:pointer; font-size:1.5rem;">&times;</span>
            <h2 id="tituloModal">Item</h2>
            <form action="guardar_producto.php" method="POST">
                <input type="hidden" name="accion" id="inputAccion" value="crear">
                <input type="hidden" name="id" id="inputId">

                <div class="form-group">
                    <label>Tipo:</label>
                    <select name="tipo" id="inputTipo" onchange="toggleStock()">
                        <option value="producto">Producto (Controlar Stock)</option>
                        <option value="servicio">Servicio (Sin Stock)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Nombre:</label>
                    <input type="text" name="nombre" id="inputNombre" required>
                </div>

                <div class="form-group">
                    <label>Precio (S/):</label>
                    <input type="number" step="0.50" name="precio" id="inputPrecio" required>
                </div>

                <div class="form-group" id="groupStock">
                    <label>Stock Actual:</label>
                    <input type="number" name="stock" id="inputStock" value="0">
                </div>

                <button type="submit" style="width:100%; padding:10px; background:#2ecc71; color:white; border:none; border-radius:5px; font-weight:bold;">GUARDAR</button>
            </form>
        </div>
    </div>

    <script>
        var modal = document.getElementById("modalProducto");

        function toggleStock() {
            let tipo = document.getElementById("inputTipo").value;
            let divStock = document.getElementById("groupStock");
            if(tipo === 'servicio') {
                divStock.style.display = 'none';
            } else {
                divStock.style.display = 'block';
            }
        }

        function abrirModalCrear() {
            document.getElementById("tituloModal").innerText = "Nuevo Item";
            document.getElementById("inputAccion").value = "crear";
            document.getElementById("inputId").value = "";
            document.getElementById("inputNombre").value = "";
            document.getElementById("inputPrecio").value = "";
            document.getElementById("inputStock").value = "0";
            document.getElementById("inputTipo").value = "producto";
            toggleStock();
            modal.style.display = "block";
        }

        function abrirModalEditar(id, nombre, precio, stock, tipo) {
            document.getElementById("tituloModal").innerText = "Editar Item";
            document.getElementById("inputAccion").value = "editar";
            document.getElementById("inputId").value = id;
            document.getElementById("inputNombre").value = nombre;
            document.getElementById("inputPrecio").value = precio;
            document.getElementById("inputStock").value = stock;
            document.getElementById("inputTipo").value = tipo || 'producto';
            toggleStock();
            modal.style.display = "block";
        }

        window.onclick = function(e) { if (e.target == modal) modal.style.display = "none"; }
    </script>
</body>
</html>