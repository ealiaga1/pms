<?php
session_start();
include '../db.php';
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// 1. Obtener Habitaciones
$resHab = mysqli_query($conexion, "SELECT * FROM habitaciones ORDER BY nombre ASC");

// 2. Obtener Habitación Seleccionada (Por defecto la primera o la que venga por GET)
$id_seleccionada = isset($_GET['id_hab']) ? $_GET['id_hab'] : '';
$nombre_seleccionada = "Seleccione una habitación";

if ($id_seleccionada) {
    $qSel = mysqli_query($conexion, "SELECT nombre FROM habitaciones WHERE id = '$id_seleccionada'");
    $dSel = mysqli_fetch_assoc($qSel);
    $nombre_seleccionada = $dSel['nombre'];
    
    // Consultar Inventario de esta habitación
    $sqlInv = "SELECT inv.*, i.nombre, i.categoria 
               FROM inventario_cuartos inv 
               JOIN activos_items i ON inv.id_item = i.id 
               WHERE inv.id_habitacion = '$id_seleccionada' 
               ORDER BY i.categoria, i.nombre ASC";
    $resInv = mysqli_query($conexion, $sqlInv);
}

// 3. Obtener Catálogo de Ítems (Para el select de agregar)
$resItems = mysqli_query($conexion, "SELECT * FROM activos_items ORDER BY nombre ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario Habitaciones | Tulumayo</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #ecf0f1; margin: 0; display: flex; height: 100vh; }
        .sidebar { width: 250px; background-color: #2E5C38; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { padding: 20px; background: #244a2d; margin: 0; text-align: center; }
        .sidebar a { padding: 15px 20px; color: white; text-decoration: none; display: flex; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .sidebar a:hover { background-color: #3A5A40; }
        
        .main { flex: 1; display: flex; overflow: hidden; }
        
        /* Panel Izquierdo (Lista Habitaciones) */
        .panel-left { width: 300px; background: white; border-right: 1px solid #ddd; overflow-y: auto; padding: 20px; }
        .hab-item { display: block; padding: 15px; border-bottom: 1px solid #eee; text-decoration: none; color: #333; transition: 0.3s; border-radius: 5px; margin-bottom: 5px; }
        .hab-item:hover { background: #f0f2f5; }
        .hab-item.active { background: #2ecc71; color: white; font-weight: bold; }
        
        /* Panel Derecho (Inventario) */
        .panel-right { flex: 1; padding: 30px; overflow-y: auto; background: #f9f9f9; }
        
        .header-inv { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        
        table { width: 100%; border-collapse: collapse; background: white; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-radius: 8px; overflow: hidden; }
        th { background: #34495e; color: white; padding: 12px; text-align: left; }
        td { padding: 10px 12px; border-bottom: 1px solid #eee; }
        
        .input-mini { width: 50px; text-align: center; padding: 5px; border: 1px solid #ccc; border-radius: 4px; }
        .select-mini { padding: 5px; border: 1px solid #ccc; border-radius: 4px; }
        
        .btn-update { background: none; border: none; color: #2980b9; cursor: pointer; font-size: 1.2rem; }
        .btn-del { background: none; border: none; color: #c0392b; cursor: pointer; font-size: 1.2rem; }
        
        .cat-tag { font-size: 0.75rem; padding: 2px 6px; border-radius: 4px; background: #95a5a6; color: white; }
    </style>
</head>
<body>

       <?php include 'sidebar.php'; ?>

    <div class="main">
        
        <!-- COLUMNA IZQUIERDA: HABITACIONES -->
        <div class="panel-left">
            <h3>Habitaciones</h3>
            <p style="font-size:0.8rem; color:#777; margin-bottom:15px;">Selecciona para ver inventario:</p>
            
            <?php while($h = mysqli_fetch_assoc($resHab)): 
                $claseActiva = ($h['id'] == $id_seleccionada) ? 'active' : '';
            ?>
                <a href="inventario_habitaciones.php?id_hab=<?php echo $h['id']; ?>" class="hab-item <?php echo $claseActiva; ?>">
                    <i class="fas fa-bed"></i> <?php echo $h['nombre']; ?>
                </a>
            <?php endwhile; ?>
            
            <hr style="margin: 20px 0;">
            <button onclick="document.getElementById('modalActivo').style.display='block'" style="width:100%; padding:10px; background:#34495e; color:white; border:none; border-radius:5px; cursor:pointer;">
                <i class="fas fa-plus"></i> Crear Nuevo Activo
            </button>
        </div>

        <!-- COLUMNA DERECHA: LISTA DE OBJETOS -->
        <div class="panel-right">
            
            <?php if($id_seleccionada): ?>
                <div class="header-inv">
                    <h2 style="margin:0; color:#2c3e50;">Inventario: <?php echo $nombre_seleccionada; ?></h2>
                    <button onclick="document.getElementById('modalAgregar').style.display='block'" style="background:#27ae60; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; font-weight:bold;">
                        <i class="fas fa-plus-circle"></i> Agregar Objeto
                    </button>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th>Categoría</th>
                            <th>Objeto / Activo</th>
                            <th>Cantidad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if(mysqli_num_rows($resInv) > 0) {
                            while($item = mysqli_fetch_assoc($resInv)): 
                        ?>
                        <tr>
                            <form action="procesar_inventario_hab.php" method="POST">
                                <input type="hidden" name="accion" value="actualizar">
                                <input type="hidden" name="id_habitacion" value="<?php echo $id_seleccionada; ?>">
                                <input type="hidden" name="id_registro" value="<?php echo $item['id']; ?>">
                                
                                <td><span class="cat-tag"><?php echo $item['categoria']; ?></span></td>
                                <td><strong><?php echo $item['nombre']; ?></strong></td>
                                
                                <td>
                                    <input type="number" name="cantidad" value="<?php echo $item['cantidad']; ?>" class="input-mini">
                                </td>
                                
                                <td>
                                    <select name="estado" class="select-mini" style="color: <?php echo ($item['estado']=='Faltante')?'red':'black'; ?>">
                                        <option value="Bueno" <?php if($item['estado']=='Bueno') echo 'selected'; ?>>Bueno</option>
                                        <option value="Regular" <?php if($item['estado']=='Regular') echo 'selected'; ?>>Regular</option>
                                        <option value="Malo" <?php if($item['estado']=='Malo') echo 'selected'; ?>>Malo</option>
                                        <option value="Faltante" <?php if($item['estado']=='Faltante') echo 'selected'; ?>>Faltante (Robo/Pérdida)</option>
                                    </select>
                                </td>
                                
                                <td>
                                    <button type="submit" class="btn-update" title="Guardar Cambios"><i class="fas fa-save"></i></button>
                                    <a href="procesar_inventario_hab.php?accion=eliminar&id_reg=<?php echo $item['id']; ?>&id_habitacion=<?php echo $id_seleccionada; ?>" class="btn-del" onclick="return confirm('¿Quitar este objeto de la lista?');" title="Eliminar"><i class="fas fa-trash-alt"></i></a>
                                </td>
                            </form>
                        </tr>
                        <?php endwhile; 
                        } else {
                            echo "<tr><td colspan='5' style='text-align:center; padding:20px;'>Esta habitación no tiene inventario asignado aún.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

            <?php else: ?>
                <div style="text-align:center; margin-top:100px; color:#aaa;">
                    <i class="fas fa-arrow-left" style="font-size:3rem;"></i>
                    <h2>Selecciona una habitación<br>para ver su inventario</h2>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- MODAL AGREGAR ITEM A HABITACIÓN -->
    <div id="modalAgregar" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:100;">
        <div style="background:white; width:400px; margin:10% auto; padding:25px; border-radius:8px;">
            <h3 style="margin-top:0;">Agregar a <?php echo $nombre_seleccionada; ?></h3>
            <form action="procesar_inventario_hab.php" method="POST">
                <input type="hidden" name="accion" value="agregar">
                <input type="hidden" name="id_habitacion" value="<?php echo $id_seleccionada; ?>">
                
                <label>Objeto:</label>
                <select name="id_item" style="width:100%; padding:10px; margin-bottom:15px;">
                    <?php 
                    // Reiniciar puntero de consulta
                    mysqli_data_seek($resItems, 0);
                    while($it = mysqli_fetch_assoc($resItems)): ?>
                        <option value="<?php echo $it['id']; ?>"><?php echo $it['nombre']; ?> (<?php echo $it['categoria']; ?>)</option>
                    <?php endwhile; ?>
                </select>
                
                <label>Cantidad:</label>
                <input type="number" name="cantidad" value="1" min="1" style="width:100%; padding:10px; margin-bottom:15px;">
                
                <button type="submit" style="width:100%; padding:10px; background:#27ae60; color:white; border:none; cursor:pointer;">AGREGAR</button>
                <button type="button" onclick="document.getElementById('modalAgregar').style.display='none'" style="width:100%; padding:10px; background:#ccc; border:none; margin-top:5px; cursor:pointer;">CANCELAR</button>
            </form>
        </div>
    </div>

    <!-- MODAL CREAR NUEVO TIPO DE ACTIVO -->
    <div id="modalActivo" class="modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:100;">
        <div style="background:white; width:400px; margin:10% auto; padding:25px; border-radius:8px;">
            <h3 style="margin-top:0;">Crear Nuevo Activo</h3>
            <form action="procesar_inventario_hab.php" method="POST">
                <input type="hidden" name="accion" value="crear_activo">
                
                <!-- Truco para volver a la misma habitacion -->
                <input type="hidden" name="id_habitacion" value="<?php echo $id_seleccionada; ?>">

                <label>Nombre:</label>
                <input type="text" name="nombre" placeholder="Ej. Secadora de Pelo" required style="width:100%; padding:10px; margin-bottom:15px;">
                
                <label>Categoría:</label>
                <select name="categoria" style="width:100%; padding:10px; margin-bottom:15px;">
                    <option>Electrónica</option>
                    <option>Lencería</option>
                    <option>Mobiliario</option>
                    <option>Menaje</option>
                    <option>Decoración</option>
                </select>
                
                <button type="submit" style="width:100%; padding:10px; background:#34495e; color:white; border:none; cursor:pointer;">CREAR CATALOGO</button>
                <button type="button" onclick="document.getElementById('modalActivo').style.display='none'" style="width:100%; padding:10px; background:#ccc; border:none; margin-top:5px; cursor:pointer;">CANCELAR</button>
            </form>
        </div>
    </div>

</body>
</html>