<?php
session_start();
include '../db.php';
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// CONSULTA INTELIGENTE (AGRUPADA)
// 1. Unimos todas las tablas en una subconsulta llamada "todos"
// 2. Agrupamos por NOMBRE para eliminar duplicados
// 3. Usamos MAX() para tratar de rescatar el dato que no esté vacío (ej: si en una fila tiene email y en otra no)

$sql = "
    SELECT 
        MIN(id) as id,
        nombre,
        MAX(email) as email,
        MAX(telefono) as telefono,
        MAX(documento) as documento,
        MAX(numero) as numero,
        MAX(origen) as origen,
        MAX(es_manual) as es_manual
    FROM (
        -- Tabla 1: Reservas Web
        SELECT id, TRIM(UPPER(nombre_cliente)) as nombre, email, telefono, 'Web' as documento, '' as numero, 'Reserva Online' as origen, 0 as es_manual 
        FROM reservaciones WHERE nombre_cliente != ''
        
        UNION ALL
        
        -- Tabla 2: Estancias (Hotel)
        SELECT id, TRIM(UPPER(nombre_huesped)) as nombre, '' as email, '' as telefono, tipo_doc as documento, num_doc as numero, 'Mostrador' as origen, 0 as es_manual 
        FROM estancias WHERE nombre_huesped != ''

        UNION ALL

        -- Tabla 3: Clientes Manuales
        SELECT id, TRIM(UPPER(nombre)) as nombre, email, telefono, tipo_doc as documento, num_doc as numero, 'Directorio' as origen, 1 as es_manual
        FROM clientes_manuales
    ) as todos
    GROUP BY nombre
    ORDER BY nombre ASC
";

$resultado = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Directorio Clientes | Tulumayo</title>
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
        
        .btn-excel { background: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; text-decoration: none; margin-right: 10px; }
        .btn-add { background: #2c3e50; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; }

        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #34495e; color: white; text-transform: uppercase; font-size: 0.85rem; }
        tr:hover { background: #f9f9f9; }
        
        .tag { padding: 3px 8px; border-radius: 10px; font-size: 0.75rem; font-weight: bold; color: white; }
        .tag-web { background: #3498db; }
        .tag-hotel { background: #e67e22; }
        .tag-manual { background: #8e44ad; }

        /* Modal */
        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 100; }
        .modal-content { background: white; width: 500px; margin: 5% auto; padding: 25px; border-radius: 8px; position: relative; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; font-size: 0.9rem; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
    </style>
</head>
<body>

        <?php include 'sidebar.php'; ?>

    <div class="main">
        <div class="header-main">
            <h1>Directorio de Clientes</h1>
            <div>
                <a href="reporte_mincetur.php" class="btn-excel"><i class="fas fa-file-contract"></i> Reporte Mincetur</a>
                <a href="exportar_clientes.php" class="btn-excel"><i class="fas fa-file-excel"></i> Excel</a>
                <button class="btn-add" onclick="document.getElementById('modalCliente').style.display='block'"><i class="fas fa-plus"></i> Nuevo Cliente</button>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Nombre / Razón Social</th>
                    <th>Documento</th>
                    <th>Contacto</th>
                    <th>Email</th>
                    <th>Origen</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while($c = mysqli_fetch_assoc($resultado)) { 
                    $claseTag = 'tag-hotel';
                    if($c['origen'] == 'Reserva Online') $claseTag = 'tag-web';
                    if($c['origen'] == 'Directorio') $claseTag = 'tag-manual';
                    
                    $docInfo = $c['documento'];
                    if(!empty($c['numero'])) { $docInfo .= ": " . $c['numero']; }
                ?>
                <tr>
                    <td><strong><?php echo $c['nombre']; ?></strong></td>
                    <td><?php echo $docInfo; ?></td>
                    <td>
                        <?php if(!empty($c['telefono'])): ?>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $c['telefono']); ?>" target="_blank" style="color:#2ecc71; font-weight:bold; text-decoration:none;">
                                <i class="fab fa-whatsapp"></i> <?php echo $c['telefono']; ?>
                            </a>
                        <?php else: echo "-"; endif; ?>
                    </td>
                    <td><?php echo $c['email']; ?></td>
                    <td><span class="tag <?php echo $claseTag; ?>"><?php echo $c['origen']; ?></span></td>
                    <td>
                        <?php if($c['es_manual'] == 1): ?>
                            <form action="guardar_cliente.php" method="POST" onsubmit="return confirm('¿Borrar este contacto?');">
                                <input type="hidden" name="accion" value="eliminar">
                                <input type="hidden" name="id" value="<?php echo $c['id']; ?>">
                                <button style="border:none; background:none; color:#e74c3c; cursor:pointer;" title="Eliminar"><i class="fas fa-trash"></i></button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- MODAL NUEVO CLIENTE -->
    <div id="modalCliente" class="modal">
        <div class="modal-content">
            <span onclick="document.getElementById('modalCliente').style.display='none'" style="float:right; cursor:pointer; font-size:1.5rem;">&times;</span>
            <h2 style="color:#2c3e50; margin-top:0;">Registrar Cliente Manual</h2>
            
            <form action="guardar_cliente.php" method="POST">
                <input type="hidden" name="accion" value="crear">

                <div class="form-group">
                    <label>Nombre Completo / Empresa:</label>
                    <input type="text" name="nombre" required placeholder="Ej. Agencia de Viajes Selva">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Tipo Doc:</label>
                        <select name="tipo_doc">
                            <option value="DNI">DNI</option>
                            <option value="RUC">RUC</option>
                            <option value="PASAPORTE">Pasaporte</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Número:</label>
                        <input type="text" name="num_doc">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Teléfono / Celular:</label>
                        <input type="text" name="telefono">
                    </div>
                    <div class="form-group">
                        <label>Email:</label>
                        <input type="email" name="email">
                    </div>
                </div>

                <div class="form-group">
                    <label>Dirección:</label>
                    <input type="text" name="direccion">
                </div>

                <div class="form-group">
                    <label>Observaciones (Cumpleaños, gustos, etc):</label>
                    <textarea name="observaciones" rows="2"></textarea>
                </div>

                <button type="submit" style="width:100%; padding:12px; background:#2ecc71; color:white; border:none; border-radius:5px; font-weight:bold; cursor:pointer;">
                    GUARDAR EN DIRECTORIO
                </button>
            </form>
        </div>
    </div>

    <script>
        // Cerrar modal al clic fuera
        var modal = document.getElementById("modalCliente");
        window.onclick = function(e) { if (e.target == modal) modal.style.display = "none"; }
    </script>

</body>
</html>