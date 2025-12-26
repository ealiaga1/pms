<?php
session_start();
include '../db.php';
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// Consultar Insumos
$sql = "SELECT * FROM cocina_insumos ORDER BY nombre ASC";
$res = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Almacén Cocina | Tulumayo</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #ecf0f1; margin: 0; display: flex; height: 100vh; }
        
        /* Sidebar */
        .sidebar { width: 250px; background-color: #2E5C38; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { padding: 20px; background: #244a2d; margin: 0; text-align: center; }
        .sidebar a { padding: 15px 20px; color: white; text-decoration: none; display: flex; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar a:hover { background-color: #3A5A40; }
        .sidebar .active { background-color: #2ecc71; font-weight: bold; }

        .main { flex: 1; padding: 30px; overflow-y: auto; }
        
        .header-main { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        
        /* Botones de Acción */
        .btn-compra { background: #27ae60; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-salida { background: #e67e22; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-new { background: #2c3e50; color: white; border: none; padding: 10px 15px; border-radius: 5px; cursor: pointer; }

        /* Tabla */
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #d35400; color: white; text-transform: uppercase; font-size: 0.85rem; } /* Color Café/Naranja para cocina */
        tr:hover { background: #f9f9f9; }

        /* Alertas de Stock */
        .stock-ok { color: #27ae60; font-weight: bold; background: #eafaf1; padding: 2px 8px; border-radius: 4px; }
        .stock-low { color: #c0392b; font-weight: bold; background: #fdedec; padding: 2px 8px; border-radius: 4px; animation: pulse 2s infinite; }

        @keyframes pulse { 0% { opacity: 1; } 50% { opacity: 0.7; } 100% { opacity: 1; } }

        /* Modales */
        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 100; }
        .modal-content { background: white; width: 400px; margin: 10% auto; padding: 25px; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    </style>
</head>
<body>
        <?php include 'sidebar.php'; ?>

    <div class="main">
        <div class="header-main">
            <h1 style="color:#d35400;"><i class="fas fa-carrot"></i> Inventario de Cocina</h1>
            <a href="reporte_cocina.php" target="_blank" style="background:#555; color:white; padding:10px 15px; border-radius:5px; text-decoration:none; font-weight:bold; margin-right:10px;">
            <i class="fas fa-print"></i> Imprimir Stock
        </a>
            <button class="btn-new" onclick="document.getElementById('modalNuevo').style.display='block'">
                <i class="fas fa-plus"></i> Crear Insumo
            </button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Insumo</th>
                    <th>Unidad</th>
                    <th>Stock Actual</th>
                    <th>Estado</th>
                    <th>Acciones Rápidas</th>
                </tr>
            </thead>
            <tbody>
                <?php while($i = mysqli_fetch_assoc($res)) { 
                    $estado = ($i['stock_actual'] <= $i['stock_minimo']) 
                        ? '<span class="stock-low"><i class="fas fa-exclamation-triangle"></i> BAJO</span>' 
                        : '<span class="stock-ok">OK</span>';
                ?>
                <tr>
                    <td><strong><?php echo $i['nombre']; ?></strong></td>
                    <td><?php echo $i['unidad']; ?></td>
                    <td style="font-size:1.1rem;"><?php echo $i['stock_actual']; ?></td>
                    <td><?php echo $estado; ?></td>
                    <td>
                        <button class="btn-compra" onclick="abrirMovimiento('<?php echo $i['id']; ?>', '<?php echo $i['nombre']; ?>', 'compra')" title="Registrar Compra">
                            <i class="fas fa-arrow-up"></i>
                        </button>
                        <button class="btn-salida" onclick="abrirMovimiento('<?php echo $i['id']; ?>', '<?php echo $i['nombre']; ?>', 'consumo')" title="Salida a Cocina">
                            <i class="fas fa-arrow-down"></i>
                        </button>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- MODAL NUEVO INSUMO -->
    <div id="modalNuevo" class="modal">
        <div class="modal-content">
            <span onclick="document.getElementById('modalNuevo').style.display='none'" style="float:right; cursor:pointer; font-size:1.5rem;">&times;</span>
            <h2 style="color:#d35400;">Nuevo Insumo</h2>
            <form action="procesar_cocina.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                <div class="form-group">
                    <label>Nombre del Insumo:</label>
                    <input type="text" name="nombre" required placeholder="Ej. Arroz Extra, Aceite">
                </div>
                <div class="form-group">
                    <label>Unidad de Medida:</label>
                    <select name="unidad">
                        <option value="Kg">Kilogramos (Kg)</option>
                        <option value="Lt">Litros (Lt)</option>
                        <option value="Unidad">Unidades (Und)</option>
                        <option value="Lata">Latas</option>
                        <option value="Paquete">Paquetes</option>
                        <option value="Saco">Sacos</option>
                        <option value="Atado">Atados</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Stock Mínimo (Alerta):</label>
                    <input type="number" name="minimo" step="0.5" value="5" required>
                    <small style="color:#888;">El sistema avisará cuando baje de esta cantidad.</small>
                </div>
                <button type="submit" style="width:100%; padding:10px; background:#2c3e50; color:white; border:none; font-weight:bold; cursor:pointer;">GUARDAR</button>
            </form>
        </div>
    </div>

    <!-- MODAL MOVIMIENTOS (Entrada/Salida) -->
    <div id="modalMov" class="modal">
        <div class="modal-content">
            <span onclick="document.getElementById('modalMov').style.display='none'" style="float:right; cursor:pointer; font-size:1.5rem;">&times;</span>
            <h2 id="tituloMov">Registrar</h2>
            <form action="procesar_cocina.php" method="POST">
                <input type="hidden" name="accion" value="movimiento">
                <input type="hidden" name="id_insumo" id="inputIdInsumo">
                <input type="hidden" name="tipo" id="inputTipoMov">

                <div class="form-group">
                    <label>Cantidad:</label>
                    <input type="number" name="cantidad" step="0.01" required style="font-size:1.5rem; text-align:center;">
                </div>
                <div class="form-group">
                    <label>Detalle / Observación:</label>
                    <input type="text" name="observacion" placeholder="Ej. Compra mercado, Menú del día..." required>
                </div>
                
                <button type="submit" id="btnMovSubmit" style="width:100%; padding:10px; color:white; border:none; font-weight:bold; cursor:pointer;">GUARDAR MOVIMIENTO</button>
            </form>
        </div>
    </div>

    <script>
        function abrirMovimiento(id, nombre, tipo) {
            document.getElementById('inputIdInsumo').value = id;
            document.getElementById('inputTipoMov').value = tipo;
            let modal = document.getElementById('modalMov');
            let titulo = document.getElementById('tituloMov');
            let btn = document.getElementById('btnMovSubmit');

            if (tipo === 'compra') {
                titulo.innerText = "Entrada / Compra: " + nombre;
                titulo.style.color = "#27ae60";
                btn.style.backgroundColor = "#27ae60";
                btn.innerText = "REGISTRAR INGRESO (+)";
            } else {
                titulo.innerText = "Salida a Cocina: " + nombre;
                titulo.style.color = "#e67e22";
                btn.style.backgroundColor = "#e67e22";
                btn.innerText = "REGISTRAR SALIDA (-)";
            }
            modal.style.display = 'block';
        }

        window.onclick = function(e) { 
            if (e.target.className === 'modal') e.target.style.display = "none"; 
        }
    </script>

</body>
</html>