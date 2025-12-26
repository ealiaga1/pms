<?php
session_start();
include '../db.php';
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

$sql = "SELECT * FROM blancos_items ORDER BY tipo DESC, nombre ASC";
$res = mysqli_query($conexion, $sql);

$resHab = mysqli_query($conexion, "SELECT * FROM habitaciones");
$habitaciones = [];
while($h = mysqli_fetch_assoc($resHab)){ $habitaciones[] = $h; }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Housekeeping | Tulumayo</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #ecf0f1; margin: 0; display: flex; height: 100vh; }
        .sidebar { width: 250px; background-color: #2E5C38; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background: #244a2d; margin: 0; border-bottom: 1px solid #3A5A40; }
        .sidebar a { padding: 15px 20px; color: white; text-decoration: none; display: flex; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar a:hover { background-color: #3A5A40; }
        .sidebar .active { background-color: #2ecc71; font-weight: bold; }
        .main { flex: 1; padding: 30px; overflow-y: auto; }
        .header-main { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #3498db; color: white; text-transform: uppercase; font-size: 0.85rem; }
        tr:hover { background: #f9f9f9; }

        .btn-ingreso { background: #2980b9; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; }
        .btn-entrega { background: #8e44ad; color: white; border: none; padding: 8px 12px; border-radius: 5px; cursor: pointer; }
        .btn-new { background: #2c3e50; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; }

        .tag-tipo { padding: 3px 8px; border-radius: 10px; font-size: 0.75rem; color: white; font-weight: bold; }
        .t-lenceria { background: #95a5a6; }
        .t-amenitie { background: #e67e22; }
        .t-limpieza { background: #16a085; }

        /* Estilos para el Stock de Lenceria */
        .stock-box { display: flex; gap: 15px; font-size: 0.9rem; }
        .s-limpio { color: #27ae60; font-weight: bold; }
        .s-sucio { color: #c0392b; font-weight: bold; }
        .s-total { color: #333; font-weight: bold; border-left: 2px solid #ddd; padding-left: 10px; }

        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 100; }
        .modal-content { background: white; width: 400px; margin: 8% auto; padding: 25px; border-radius: 8px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn-delete { 
    background: #e74c3c; 
    color: white; 
    border: none; 
    padding: 8px 12px; 
    border-radius: 5px; 
    cursor: pointer; 
    margin-left: 5px; 
}
.btn-delete:hover { background: #c0392b; }
    </style>
</head>
<body>
        <?php include 'sidebar.php'; ?>
    <div class="main">
        <div class="header-main">
            <h1 style="color:#2980b9;">Almacén de Blancos</h1>
            <div>
        <!-- Botón 1: Ver Stock Actual -->
        <a href="reporte_blancos_stock.php" target="_blank" style="background:#27ae60; color:white; padding:10px 15px; text-decoration:none; border-radius:5px; font-weight:bold; margin-right:5px; font-size:0.9rem;">
            <i class="fas fa-clipboard-list"></i> Inventario
        </a>

        <!-- Botón 2: Ver Historial -->
        <a href="reporte_blancos_historial.php" target="_blank" style="background:#7f8c8d; color:white; padding:10px 15px; text-decoration:none; border-radius:5px; font-weight:bold; margin-right:10px; font-size:0.9rem;">
            <i class="fas fa-history"></i> Historial
        </a>
        
        <!-- Botón Nuevo -->
        <button class="btn-new" onclick="document.getElementById('modalNuevo').style.display='block'">
            <i class="fas fa-plus"></i> Nuevo Ítem
        </button>
    </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Ítem</th>
                    <th>Tipo</th>
                    <th>Estado de Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($i = mysqli_fetch_assoc($res)) { 
                    $clase = 't-lenceria';
                    if($i['tipo']=='Amenitie') $clase = 't-amenitie';
                    if($i['tipo']=='Limpieza') $clase = 't-limpieza';
                ?>
                <tr>
                    <td><strong><?php echo $i['nombre']; ?></strong></td>
                    <td><span class="tag-tipo <?php echo $clase; ?>"><?php echo $i['tipo']; ?></span></td>
                    <td>
                        <?php if($i['tipo'] == 'Lenceria'): ?>
                            <!-- VISTA ESPECIAL PARA LENCERÍA -->
                            <div class="stock-box">
                                <span class="s-limpio" title="En estante"><i class="fas fa-check"></i> <?php echo $i['stock_actual']; ?> Limpio</span>
                                <span class="s-sucio" title="En uso/lavandería"><i class="fas fa-clock"></i> <?php echo $i['stock_sucio']; ?> Sucio</span>
                                <span class="s-total" title="Total Propiedad"><i class="fas fa-equals"></i> <?php echo $i['stock_actual'] + $i['stock_sucio']; ?> Total</span>
                            </div>
                        <?php else: ?>
                            <!-- VISTA NORMAL AMENITIES -->
                            <span style="font-size:1.1rem; font-weight:bold;"><?php echo $i['stock_actual']; ?> Unid.</span>
                        <?php endif; ?>
                    </td>
                    <td>
    <!-- Botones existentes -->
    <button class="btn-ingreso" onclick="abrirMovimiento('<?php echo $i['id']; ?>', '<?php echo $i['nombre']; ?>', 'compra', '<?php echo $i['tipo']; ?>')" title="Ingreso">
        <i class="fas fa-arrow-up"></i>
    </button>
    
    <button class="btn-entrega" onclick="abrirMovimiento('<?php echo $i['id']; ?>', '<?php echo $i['nombre']; ?>', 'entrega', '<?php echo $i['tipo']; ?>')" title="Entregar">
        <i class="fas fa-arrow-right"></i>
    </button>

    <!-- NUEVO: BOTÓN ELIMINAR -->
    <form action="procesar_blancos.php" method="POST" style="display:inline-block;" onsubmit="return confirm('¿Estás seguro de ELIMINAR este ítem? Se perderá el conteo de stock.');">
        <input type="hidden" name="accion" value="eliminar">
        <input type="hidden" name="id" value="<?php echo $i['id']; ?>">
        <button type="submit" class="btn-delete" title="Borrar Ítem">
            <i class="fas fa-trash"></i>
        </button>
    </form>
</td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- MODAL NUEVO -->
    <div id="modalNuevo" class="modal">
        <div class="modal-content">
            <span onclick="document.getElementById('modalNuevo').style.display='none'" style="float:right; cursor:pointer;">&times;</span>
            <h2>Nuevo Material</h2>
            <form action="procesar_blancos.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                <div class="form-group"><label>Nombre:</label><input type="text" name="nombre" required></div>
                <div class="form-group">
                    <label>Categoría:</label>
                    <select name="tipo">
                        <option value="Lenceria">Lenceria (Rotativo)</option>
                        <option value="Amenitie">Amenitie (Consumible)</option>
                        <option value="Limpieza">Insumos Limpieza</option>
                    </select>
                </div>
                <button type="submit" style="width:100%; padding:10px; background:#2c3e50; color:white; border:none;">GUARDAR</button>
            </form>
        </div>
    </div>

    <!-- MODAL MOVIMIENTOS -->
    <div id="modalMov" class="modal">
        <div class="modal-content">
            <span onclick="document.getElementById('modalMov').style.display='none'" style="float:right; cursor:pointer;">&times;</span>
            <h2 id="tituloMov">Registrar</h2>
            <form action="procesar_blancos.php" method="POST">
                <input type="hidden" name="accion" value="movimiento">
                <input type="hidden" name="id_item" id="inputIdItem">
                <input type="hidden" name="tipo_mov" id="inputTipoMov">

                <!-- Se llena con JS -->
                <div id="divExtraOpcion" class="form-group" style="display:none;"></div> 

                <div class="form-group"><label>Cantidad:</label><input type="number" name="cantidad" required style="font-size:1.5rem; text-align:center;"></div>
                <div class="form-group"><label>Observación:</label><input type="text" name="observacion"></div>
                
                <button type="submit" id="btnMovSubmit" style="width:100%; padding:10px; color:white; border:none; cursor:pointer;">GUARDAR</button>
            </form>
        </div>
    </div>

    <script>
        function abrirMovimiento(id, nombre, movimiento, categoria) {
            document.getElementById('inputIdItem').value = id;
            document.getElementById('inputTipoMov').value = movimiento;
            let modal = document.getElementById('modalMov');
            let titulo = document.getElementById('tituloMov');
            let btn = document.getElementById('btnMovSubmit');
            let divExtra = document.getElementById('divExtraOpcion');
            
            divExtra.style.display = 'none';
            divExtra.innerHTML = '';

            if (movimiento === 'compra') {
                titulo.innerText = "Ingreso: " + nombre;
                btn.style.background = "#2980b9";
                btn.innerText = "REGISTRAR INGRESO";

                // Si es lencería, preguntamos si es compra o retorno
                if(categoria === 'Lenceria'){
                    divExtra.style.display = 'block';
                    divExtra.innerHTML = `
                        <label>Tipo de Ingreso:</label>
                        <select name="subtipo_mov" style="width:100%; padding:10px; border:1px solid #2980b9;">
                            <option value="Retorno Lavandería">Retorno de Lavandería (Limpio)</option>
                            <option value="Compra Nueva">Compra Nueva (Nuevo Stock)</option>
                        </select>
                    `;
                }
            } else {
                titulo.innerText = "Salida: " + nombre;
                btn.style.background = "#8e44ad";
                btn.innerText = "REGISTRAR ENTREGA";
            }
            modal.style.display = 'block';
        }
        window.onclick = function(e) { if (e.target.className === 'modal') e.target.style.display = "none"; }
    </script>

</body>
</html>