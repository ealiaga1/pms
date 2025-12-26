<?php
session_start();
include '../db.php';
date_default_timezone_set('America/Lima');

if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// 1. Obtener Mesas (Ahora traemos también la zona)
$sqlMesas = "SELECT * FROM mesas ORDER BY zona DESC, id ASC";
$resMesas = mysqli_query($conexion, $sqlMesas);

// 2. Productos
$resProd = mysqli_query($conexion, "SELECT * FROM productos ORDER BY nombre ASC");
$lista_productos = [];
while($p = mysqli_fetch_assoc($resProd)){ $lista_productos[] = $p; }

// 3. Habitaciones
$resHab = mysqli_query($conexion, "SELECT h.id, h.nombre, e.nombre_huesped FROM habitaciones h JOIN estancias e ON h.id = e.id_habitacion WHERE e.estado = 'activa'");
$habitaciones_ocupadas = [];
while($h = mysqli_fetch_assoc($resHab)){ $habitaciones_ocupadas[] = $h; }
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Puntos de Venta | Tulumayo</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #ecf0f1; margin: 0; display: flex; height: 100vh; }
        
        /* Sidebar (Mismo estilo) */
        .sidebar { width: 250px; background-color: #2E5C38; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { text-align: center; padding: 20px 0; background: #244a2d; margin: 0; border-bottom: 1px solid #3A5A40; }
        .sidebar a { padding: 15px 20px; color: white; text-decoration: none; display: flex; gap: 10px; border-bottom: 1px solid rgba(255,255,255,0.05); transition: 0.3s; }
        .sidebar a:hover { background-color: #3A5A40; padding-left: 25px; } 
        
        .main { flex: 1; padding: 30px; overflow-y: auto; }
        
        /* PESTAÑAS DE ZONA */
        .tabs-container { display: flex; gap: 15px; margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .tab-btn {
            padding: 10px 25px;
            border: none;
            background: #ddd;
            color: #555;
            font-weight: bold;
            font-size: 1rem;
            border-radius: 20px;
            cursor: pointer;
            transition: 0.3s;
            display: flex; align-items: center; gap: 8px;
        }
        .tab-btn.active { background: #2E5C38; color: white; box-shadow: 0 4px 10px rgba(0,0,0,0.2); }
        .tab-btn:hover { background: #bbb; color: #333; }
        .tab-btn.active:hover { background: #244a2d; color: white; }

        /* GRID */
        .grid-mesas { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 20px; }
        
        .card-mesa { background: white; padding: 30px 20px; border-radius: 10px; text-align: center; cursor: pointer; box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: 0.3s; border-top: 5px solid #ccc; position: relative; }
        .card-mesa:hover { transform: translateY(-5px); }
        
        .mesa-libre { border-color: #2ecc71; }
        .mesa-libre i { color: #2ecc71; }
        
        .mesa-ocupada { border-color: #e74c3c; background: #fff5f5; }
        .mesa-ocupada i { color: #e74c3c; }

        .btn-delete-mesa { position: absolute; top: 10px; right: 10px; background: none; border: none; color: #e74c3c; cursor: pointer; font-size: 1.2rem; z-index: 10; }
        
        /* Modal */
        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 100; }
        .modal-content { background: white; width: 600px; margin: 2% auto; padding: 25px; border-radius: 8px; max-height: 90vh; overflow-y: auto; }
        .form-select, .form-input { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px; margin-bottom: 10px; }
    </style>
</head>
<body>

        <!-- SIDEBAR -->
               <?php include 'sidebar.php'; ?>

    <div class="main">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
            <h1 style="color: #2E5C38; margin:0;">Puntos de Venta</h1>
            <button onclick="document.getElementById('modalNuevaMesa').style.display='block'" style="background:#2c3e50; color:white; border:none; padding:10px 20px; border-radius:5px; cursor:pointer; font-weight:bold;">
                <i class="fas fa-plus"></i> Crear Espacio
            </button>
        </div>

        <!-- PESTAÑAS DE ZONAS -->
        <div class="tabs-container">
            <button class="tab-btn active" onclick="filtrarZona('restaurante', this)">
                <i class="fas fa-utensils"></i> Restaurante
            </button>
            <button class="tab-btn" onclick="filtrarZona('piscina', this)">
                <i class="fas fa-swimmer"></i> Barra Piscina
            </button>
        </div>
        
        <div class="grid-mesas">
            <?php while($m = mysqli_fetch_assoc($resMesas)): 
                // Cálculos
                $total_mesa = 0; $id_pedido_mesa = 0;
                if($m['estado'] == 'ocupada') {
                    $qP = mysqli_query($conexion, "SELECT id, total FROM pedidos_restaurante WHERE id_mesa = '".$m['id']."' AND estado = 'abierto'");
                    $dataP = mysqli_fetch_assoc($qP);
                    $total_mesa = $dataP['total'] ? $dataP['total'] : 0.00;
                    $id_pedido_mesa = $dataP['id'];
                }
                
                $clase = ($m['estado'] == 'libre') ? 'mesa-libre' : 'mesa-ocupada';
                $icono = ($m['zona'] == 'piscina') ? 'fa-glass-martini-alt' : 'fa-chair'; // Icono diferente para bar
                if($m['estado']=='ocupada') $icono = 'fa-utensils'; // Icono comiendo
            ?>
                <!-- AGREGAMOS EL ATRIBUTO DATA-ZONA -->
                <div class="card-mesa item-mesa <?php echo $clase; ?>" data-zona="<?php echo $m['zona']; ?>" onclick="abrirMesa(<?php echo $m['id']; ?>, '<?php echo $m['estado']; ?>', '<?php echo $m['nombre']; ?>', <?php echo $id_pedido_mesa; ?>, <?php echo $total_mesa; ?>)">
                    
                    <?php if($m['estado'] == 'libre'): ?>
                        <form action="guardar_mesa.php" method="POST" onsubmit="return confirm('¿Eliminar este espacio?');" style="display:inline;">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?php echo $m['id']; ?>">
                            <button type="submit" class="btn-delete-mesa" onclick="event.stopPropagation();"><i class="fas fa-trash-alt"></i></button>
                        </form>
                    <?php endif; ?>

                    <i class="fas <?php echo $icono; ?>" style="font-size: 3rem; margin-bottom: 15px;"></i>
                    <h3 style="margin:0; color:#333;"><?php echo $m['nombre']; ?></h3>
                    
                    <?php if($m['estado'] == 'ocupada'): ?>
                        <div style="margin-top:10px;"><span style="color: #e74c3c; font-weight: bold; font-size:1.2rem;">S/ <?php echo number_format($total_mesa, 2); ?></span></div>
                    <?php else: ?>
                        <div style="margin-top:10px; color:#2ecc71;">Disponible</div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <!-- MODAL NUEVA MESA / ESPACIO -->
    <div id="modalNuevaMesa" class="modal">
        <div class="modal-content" style="width: 400px; padding: 30px;">
            <h2 style="margin:0; color:#2E5C38;">Nuevo Espacio</h2><br>
            <form action="guardar_mesa.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                
                <label style="font-weight:bold;">Zona / Ubicación:</label>
                <select name="zona" class="form-select">
                    <option value="restaurante">Salón Restaurante</option>
                    <option value="piscina">Barra / Piscina</option>
                </select>

                <label style="font-weight:bold;">Nombre (Ej: Mesa 1, Tumbona 2):</label>
                <input type="text" name="nombre" class="form-input" required>
                
                <button type="submit" style="width:100%; padding:12px; background:#2ecc71; color:white; border:none; border-radius:5px; font-weight:bold;">CREAR</button>
            </form>
            <br>
            <button onclick="document.getElementById('modalNuevaMesa').style.display='none'" style="width:100%; padding:10px; background:#ccc; border:none; border-radius:5px;">CANCELAR</button>
        </div>
    </div>

    <!-- MODAL PEDIDOS (Reutilizable) -->
    <div id="modalPedido" class="modal">
        <div class="modal-content">
            <div style="display:flex; justify-content:space-between; align-items:center; border-bottom:1px solid #eee; padding-bottom:10px; margin-bottom:20px;">
                <h2 id="tituloMesa" style="margin:0; color:#2E5C38;">Mesa</h2>
                <span onclick="document.getElementById('modalPedido').style.display='none'" style="cursor:pointer; font-size:1.5rem;">&times;</span>
            </div>
            <div id="contenidoModal"></div>
        </div>
    </div>

    <script>
        const productos = <?php echo json_encode($lista_productos); ?>;
        const habitaciones = <?php echo json_encode($habitaciones_ocupadas); ?>;

        // --- FILTRAR POR ZONA (JS) ---
        function filtrarZona(zona, btn) {
            // Cambiar estilo de botones
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            // Mostrar/Ocultar tarjetas
            let tarjetas = document.querySelectorAll('.item-mesa');
            tarjetas.forEach(card => {
                if (card.getAttribute('data-zona') === zona) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        // Ejecutar filtro al inicio (Mostrar Restaurante por defecto)
        document.addEventListener("DOMContentLoaded", function() {
            filtrarZona('restaurante', document.querySelector('.tab-btn'));
        });

        // --- FUNCIONES DE PEDIDO (Igual que antes) ---
        function abrirMesa(id, estado, nombre, idPedido, total) {
            let modal = document.getElementById('modalPedido');
            let content = document.getElementById('contenidoModal');
            document.getElementById('tituloMesa').innerText = nombre;
            modal.style.display = 'block';

            if(estado === 'libre') {
                content.innerHTML = `
                    <div style="text-align:center; padding:20px;">
                        <i class="fas fa-utensils" style="font-size:4rem; color:#2ecc71; margin-bottom:20px;"></i>
                        <p>El espacio está libre.</p>
                        <form action="procesar_restaurante.php" method="POST">
                            <input type="hidden" name="accion" value="abrir">
                            <input type="hidden" name="id_mesa" value="${id}">
                            <button type="submit" style="width:100%; padding:15px; background:#2ecc71; color:white; border:none; border-radius:5px; font-weight:bold; cursor:pointer; font-size:1rem;">ABRIR CUENTA</button>
                        </form>
                    </div>
                `;
            } else {
                let optsProd = '<option value="">-- Buscar Producto --</option>';
                productos.forEach(p => { optsProd += `<option value="${p.id}">${p.nombre} - S/ ${p.precio}</option>`; });
                let optsHab = '<option value="">-- Seleccionar Habitación --</option>';
                habitaciones.forEach(h => { optsHab += `<option value="${h.id}">${h.nombre} - ${h.nombre_huesped}</option>`; });

                content.innerHTML = `
                    <div style="display:flex; gap:10px; margin-bottom:15px;">
                        <a href="ver_comanda.php?id=${idPedido}" target="_blank" style="flex:1; padding:10px; background:#f39c12; color:white; text-align:center; text-decoration:none; border-radius:5px; font-weight:bold;"><i class="fas fa-print"></i> TICKET COMANDA</a>
                    </div>
                    
                    <label style="font-weight:bold; color:#555;">Consumo Actual:</label>
                    <div id="listaPedidosMesa" class="lista-pedidos-container" style="border:1px solid #ddd; height:150px; overflow-y:auto; margin-bottom:15px; padding:10px;">Cargando...</div>

                    <div style="background:#f4f4f4; padding:15px; border-radius:8px; margin-bottom:20px;">
                        <form action="procesar_restaurante.php" method="POST">
                            <input type="hidden" name="accion" value="agregar_item">
                            <input type="hidden" name="id_mesa" value="${id}">
                            <div style="display:grid; grid-template-columns: 3fr 1fr; gap:10px;">
                                <select name="id_producto" class="form-select" required>${optsProd}</select>
                                <input type="number" name="cantidad" value="1" min="1" class="form-input" required>
                            </div>
                            <input type="text" name="notas" class="form-input" placeholder="Notas (Ej: Sin hielo)">
                            <button type="submit" style="width:100%; background:#3498db; color:white; border:none; padding:10px; cursor:pointer; font-weight:bold; border-radius:5px;">+ AGREGAR</button>
                        </form>
                    </div>

                    <div style="text-align:right; font-size:1.5rem; font-weight:bold; color:#333; margin-bottom:20px; border-top:2px solid #ddd; padding-top:10px;">
                        Total: S/ ${total.toFixed(2)}
                    </div>

                    <h4 style="margin-bottom:10px; border-bottom:1px solid #ccc;">Cerrar y Cobrar</h4>
                    <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                        <form action="procesar_restaurante.php" method="POST">
                            <input type="hidden" name="accion" value="cobrar_caja">
                            <input type="hidden" name="id_mesa" value="${id}">
                            <input type="hidden" name="id_pedido" value="${idPedido}">
                            <input type="hidden" name="total" value="${total}">
                            <select name="metodo_pago" required class="form-select" style="margin-bottom:5px;">
                                <option value="Efectivo">Efectivo</option><option value="Yape">Qr IziPay</option><option value="Tarjeta">Tarjeta</option>
                            </select>
                            <button type="submit" style="width:100%; background:#27ae60; color:white; border:none; padding:12px; cursor:pointer; border-radius:5px; font-weight:bold;">PAGAR CAJA</button>
                        </form>

                        <form action="procesar_restaurante.php" method="POST">
                            <input type="hidden" name="accion" value="cargar_habitacion">
                            <input type="hidden" name="id_mesa" value="${id}">
                            <input type="hidden" name="id_pedido" value="${idPedido}">
                            <input type="hidden" name="total" value="${total}">
                            <select name="id_habitacion" required class="form-select" style="margin-bottom:5px;">${optsHab}</select>
                            <button type="submit" style="width:100%; background:#8e44ad; color:white; border:none; padding:12px; cursor:pointer; border-radius:5px; font-weight:bold;">A LA HABITACIÓN</button>
                        </form>
                    </div>
                `;
                cargarDetallePedido(id);
            }
        }

        function cargarDetallePedido(idMesa) {
            fetch('api_pedidos.php?id_mesa=' + idMesa)
                .then(response => response.json())
                .then(data => {
                    let html = '';
                    if (data.length > 0) {
                        data.forEach(item => {
                            let sub = (item.precio_unitario * item.cantidad).toFixed(2);
                            html += `<div style="display:flex; justify-content:space-between; padding:5px 0; border-bottom:1px solid #eee;"><span>${item.cantidad} x ${item.nombre}</span><strong>S/ ${sub}</strong></div>`;
                        });
                    } else { html = '<div style="text-align:center; color:#999;">Sin pedidos.</div>'; }
                    document.getElementById('listaPedidosMesa').innerHTML = html;
                });
        }

        window.onclick = function(e) { if (e.target == document.getElementById('modalPedido')) document.getElementById('modalPedido').style.display='none'; }
    </script>
    <?php if(isset($_GET['status']) && $_GET['status'] == 'cobrado'): ?>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Mostrar mensaje bonito
            alert("✅ ¡CUENTA CERRADA CON ÉXITO!\n\nEl pago ha sido registrado correctamente.");
            
            // Limpiar la URL para que no vuelva a salir al recargar
            if (window.history.replaceState) {
                window.history.replaceState(null, null, window.location.pathname);
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>