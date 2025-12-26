<?php
session_start();
include '../db.php';
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// Consultar todas las habitaciones
$sql = "SELECT * FROM habitaciones ORDER BY nombre ASC";
$resultado = mysqli_query($conexion, $sql);

// Consultar TIPOS únicos para el filtro de actualización masiva
$sqlTipos = "SELECT DISTINCT tipo FROM habitaciones ORDER BY tipo ASC";
$resTipos = mysqli_query($conexion, $sqlTipos);
$tipos_disponibles = [];
while($row = mysqli_fetch_assoc($resTipos)) {
    $tipos_disponibles[] = $row['tipo'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Config. Habitaciones | Tulumayo</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #ecf0f1; margin: 0; display: flex; height: 100vh; }
        .sidebar { width: 250px; background-color: #2E5C38; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background: #244a2d; margin: 0; border-bottom: 1px solid #3A5A40; }
        .sidebar a { padding: 15px 20px; color: white; text-decoration: none; display: flex; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.3s; }
        .sidebar a:hover { background-color: #3A5A40; padding-left: 25px; }
        
        .main { flex: 1; padding: 30px; overflow-y: auto; }
        .header-main { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        
        .btn-add { background: #2ecc71; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }
        .btn-update-all { background: #3498db; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; margin-right: 10px; }
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #34495e; color: white; text-transform: uppercase; font-size: 0.85rem; }
        tr:hover { background: #f9f9f9; }

        .btn-action { border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; color: white; margin-right: 5px; }
        
        .precio-baja { color: #2980b9; font-weight: bold; }
        .precio-alta { color: #c0392b; font-weight: bold; }

        /* Modal */
        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 100; }
        .modal-content { background: white; width: 450px; margin: 5% auto; padding: 30px; border-radius: 8px; position: relative; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input, .form-group select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <div class="main">
        <div class="header-main">
            <h1>Configuración de Habitaciones</h1>
            <div>
                <a href="historial_mantenimiento.php" class="btn-update-all"><i class="fas fa-tools"></i> Mantenimiento</a>
                
                <a href="temporadas.php" class="btn-update-all">
                    <i class="fas fa-calendar-check"></i> Config. Temporadas
                </a>

                <!-- BOTÓN NUEVO: ACTUALIZAR MASIVO -->
                <button class="btn-update-all" onclick="document.getElementById('modalMasivo').style.display='block'">
                    <i class="fas fa-tags"></i> Actualizar Precios en Bloque
                </button>
                
                <button class="btn-add" onclick="abrirModalCrear()">
                    <i class="fas fa-plus"></i> Nueva Habitación
                </button>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Capac.</th>
                    <th>Precio Baja</th>
                    <th>Precio Alta</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($hab = mysqli_fetch_assoc($resultado)) { 
                    $p_alta = ($hab['precio_alta'] > 0) ? $hab['precio_alta'] : $hab['precio_noche'];
                ?>
                <tr>
                    <td><strong><?php echo $hab['nombre']; ?></strong></td>
                    <td><?php echo $hab['tipo']; ?></td>
                    <td><?php echo $hab['capacidad']; ?>p</td>
                    <td class="precio-baja">S/ <?php echo number_format($hab['precio_noche'], 2); ?></td>
                    <td class="precio-alta">S/ <?php echo number_format($p_alta, 2); ?></td>
                    <td>
                        <?php 
                            if($hab['estado'] == 'disponible') echo '<span style="color:#2ecc71;">Disponible</span>';
                            elseif($hab['estado'] == 'ocupada') echo '<span style="color:#e74c3c;">Ocupada</span>';
                            else echo '<span style="color:#f1c40f;">Limpieza</span>';
                        ?>
                    </td>
                    <td>
                        <button class="btn-action" style="background:#f39c12;" onclick="abrirModalEditar(
                            '<?php echo $hab['id']; ?>', 
                            '<?php echo $hab['nombre']; ?>', 
                            '<?php echo $hab['tipo']; ?>', 
                            '<?php echo $hab['capacidad']; ?>', 
                            '<?php echo $hab['precio_noche']; ?>',
                            '<?php echo $p_alta; ?>'
                        )"><i class="fas fa-edit"></i></button>
                        
                        <form action="guardar_habitacion.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar habitación?');">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?php echo $hab['id']; ?>">
                            <button class="btn-action" style="background:#e74c3c;"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- MODAL INDIVIDUAL (CREAR/EDITAR) -->
    <div id="modalHabitacion" class="modal">
        <div class="modal-content">
            <span onclick="document.getElementById('modalHabitacion').style.display='none'" style="float:right; cursor:pointer; font-size:1.5rem;">&times;</span>
            <h2 id="tituloModal">Habitación</h2>
            
            <form action="guardar_habitacion.php" method="POST">
                <input type="hidden" name="accion" id="inputAccion" value="crear">
                <input type="hidden" name="id" id="inputId">

                <div class="form-group">
                    <label>Nombre o Número:</label>
                    <input type="text" name="nombre" id="inputNombre" required placeholder="Ej. Bungalow 10">
                </div>

                <div class="form-group">
                    <label>Tipo:</label>
                    <select name="tipo" id="inputTipo">
                        <!-- Se llena manual o podrías hacerlo dinámico, por ahora manual es seguro -->
                        <option value="Matrimonial">Matrimonial</option>
                        <option value="Doble">Doble / Twin</option>
                        <option value="Triple">Triple</option>
                        <option value="Familiar">Familiar</option>
                        <option value="Bungalow">Bungalow</option>
                        <option value="Suite">Suite con Vista</option>
                        <option value="Camping">Zona Camping</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Capacidad:</label>
                    <input type="number" name="capacidad" id="inputCapacidad" required value="2">
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label style="color:#2980b9;">Precio Baja:</label>
                        <input type="number" step="0.50" name="precio" id="inputPrecio" required>
                    </div>
                    <div class="form-group">
                        <label style="color:#c0392b;">Precio Alta:</label>
                        <input type="number" step="0.50" name="precio_alta" id="inputPrecioAlta" required>
                    </div>
                </div>

                <button type="submit" style="width:100%; padding:10px; background:#2ecc71; color:white; border:none; border-radius:5px; font-weight:bold;">GUARDAR</button>
            </form>
        </div>
    </div>

    <!-- NUEVO: MODAL ACTUALIZACIÓN MASIVA -->
    <div id="modalMasivo" class="modal">
        <div class="modal-content" style="border-top: 5px solid #3498db;">
            <span onclick="document.getElementById('modalMasivo').style.display='none'" style="float:right; cursor:pointer; font-size:1.5rem;">&times;</span>
            <h2 style="color:#3498db;">Actualizar Precios en Bloque</h2>
            <p style="color:#666; font-size:0.9rem;">Cambia el precio a TODAS las habitaciones del mismo tipo a la vez.</p>
            
            <form action="guardar_habitacion.php" method="POST" onsubmit="return confirm('¿Seguro? Esto cambiará el precio de TODOS los bungalows de este tipo.');">
                <input type="hidden" name="accion" value="actualizar_precios_lote">

                <div class="form-group">
                    <label>Seleccionar Tipo de Habitación:</label>
                    <select name="tipo_lote" required style="font-weight:bold;">
                        <option value="">-- Seleccionar --</option>
                        <?php foreach($tipos_disponibles as $tipo): ?>
                            <option value="<?php echo $tipo; ?>"><?php echo $tipo; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label style="color:#2980b9;">Nuevo Precio Baja:</label>
                        <input type="number" step="0.50" name="precio_baja_lote" required placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label style="color:#c0392b;">Nuevo Precio Alta:</label>
                        <input type="number" step="0.50" name="precio_alta_lote" required placeholder="0.00">
                    </div>
                </div>

                <button type="submit" style="width:100%; padding:12px; background:#3498db; color:white; border:none; border-radius:5px; font-weight:bold;">APLICAR CAMBIOS</button>
            </form>
        </div>
    </div>

    <script>
        var modal = document.getElementById("modalHabitacion");

        function abrirModalCrear() {
            document.getElementById("tituloModal").innerText = "Nueva Habitación";
            document.getElementById("inputAccion").value = "crear";
            document.getElementById("inputId").value = "";
            document.getElementById("inputNombre").value = "";
            document.getElementById("inputPrecio").value = "";
            document.getElementById("inputPrecioAlta").value = "";
            document.getElementById("inputCapacidad").value = "2";
            modal.style.display = "block";
        }

        function abrirModalEditar(id, nombre, tipo, capacidad, precio, precioAlta) {
            document.getElementById("tituloModal").innerText = "Editar Habitación";
            document.getElementById("inputAccion").value = "editar";
            document.getElementById("inputId").value = id;
            document.getElementById("inputNombre").value = nombre;
            document.getElementById("inputTipo").value = tipo;
            document.getElementById("inputCapacidad").value = capacidad;
            document.getElementById("inputPrecio").value = precio;
            document.getElementById("inputPrecioAlta").value = precioAlta;
            modal.style.display = "block";
        }

        window.onclick = function(e) { 
            if (e.target == modal) modal.style.display = "none"; 
            if (e.target == document.getElementById('modalMasivo')) document.getElementById('modalMasivo').style.display='none'; 
        }
    </script>
</body>
</html>