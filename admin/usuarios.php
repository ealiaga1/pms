<?php
session_start();
include '../db.php';

// 1. Si no ha iniciado sesión, ir al login
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// 2. VERIFICACIÓN DE PERMISOS
$mis_permisos = is_array($_SESSION['permisos']) ? $_SESSION['permisos'] : explode(',', $_SESSION['permisos']);

if ($_SESSION['rol'] != 'admin' && !in_array('usuarios', $mis_permisos)) {
    header("Location: panel.php"); 
    exit();
}

$sql = "SELECT * FROM usuarios_admin ORDER BY id ASC";
$resultado = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Usuarios y Roles | Tulumayo</title>
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
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #34495e; color: white; text-transform: uppercase; font-size: 0.85rem; }
        tr:hover { background: #f9f9f9; }

        /* --- COLORES DE ROLES --- */
        .rol-badge { padding: 4px 10px; border-radius: 15px; font-size: 0.75rem; font-weight: bold; color: white; text-transform: uppercase; }
        .rol-admin { background: #c0392b; }
        .rol-recep { background: #2980b9; }
        .rol-limpieza { background: #f1c40f; color: #333; }
        .rol-barra { background: #8e44ad; }
        .rol-restaurante { background: #e67e22; }

        .modal { display: none; position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.5); z-index: 100; }
        .modal-content { background: white; width: 600px; margin: 2% auto; padding: 30px; border-radius: 8px; position: relative; max-height: 90vh; overflow-y: auto; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: bold; }
        .form-group input[type="text"], .form-group input[type="password"], select { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing:border-box; }
        
        /* --- ESTILOS DE PERMISOS MEJORADOS (GRID) --- */
        .permisos-grid { 
            display: grid; 
            grid-template-columns: repeat(2, 1fr); /* 2 columnas exactas */
            gap: 10px; 
            background: #fdfdfd; 
            padding: 15px; 
            border-radius: 5px; 
            border: 1px solid #ddd; 
        }

        .permiso-item {
            display: flex;
            align-items: center;
            background: white;
            padding: 8px;
            border-radius: 4px;
            border: 1px solid #eee;
            transition: all 0.2s;
        }

        .permiso-item:hover {
            border-color: #2ecc71;
            background-color: #f0fdf4;
        }

        .permiso-item label { 
            display: flex; 
            align-items: center; 
            gap: 10px; /* Espacio entre el cuadrito y el texto */
            cursor: pointer; 
            width: 100%;
            font-size: 0.85rem;
            margin: 0;
            font-weight: 500;
            color: #333;
        }

        .permiso-item input[type="checkbox"] {
            width: 18px;
            height: 18px;
            accent-color: #2E5C38;
            cursor: pointer;
            margin: 0;
        }
    </style>
</head>
<body>

    <!-- MENÚ LATERAL -->
           <?php include 'sidebar.php'; ?>

    <div class="main">
        <div class="header-main">
            <h1>Gestión de Usuarios y Roles</h1>
            <a href="auditoria.php" class="btn-add"><i class="fas fa-tools"></i> Auditoria</a>
            <button class="btn-add" onclick="abrirModalCrear()"><i class="fas fa-user-plus"></i> Nuevo Usuario</button>
        </div>

        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Usuario</th>
                    <th>Rol / Puesto</th>
                    <th>Acceso a Módulos</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user = mysqli_fetch_assoc($resultado)) { 
                    $badge = "rol-recep";
                    if($user['rol'] == 'admin') $badge = "rol-admin";
                    if($user['rol'] == 'limpieza') $badge = "rol-limpieza";
                    if($user['rol'] == 'barra') $badge = "rol-barra";
                    if($user['rol'] == 'restaurante') $badge = "rol-restaurante";
                ?>
                <tr>
                    <td><?php echo $user['nombre_completo']; ?></td>
                    <td><?php echo $user['usuario']; ?></td>
                    <td><span class="rol-badge <?php echo $badge; ?>"><?php echo strtoupper($user['rol']); ?></span></td>
                    <td>
                        <?php if($user['rol'] == 'admin'): ?>
                            <small style="color:#c0392b; font-weight:bold;">ACCESO TOTAL</small>
                        <?php else: ?>
                            <small style="color:#666; font-size:0.8rem;">
                                <?php echo str_replace(',', ', ', $user['permisos']); ?>
                            </small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <button onclick="abrirModalEditar(
                            '<?php echo $user['id']; ?>', 
                            '<?php echo $user['nombre_completo']; ?>', 
                            '<?php echo $user['usuario']; ?>', 
                            '<?php echo $user['rol']; ?>',
                            '<?php echo $user['permisos']; ?>'
                        )" style="cursor:pointer; border:none; background:none; color:#f39c12; font-size:1.2rem;"><i class="fas fa-edit"></i></button>
                        
                        <?php if($user['id'] != 1): ?>
                        <form action="guardar_usuario.php" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar?');">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                            <button style="cursor:pointer; border:none; background:none; color:#e74c3c; font-size:1.2rem;"><i class="fas fa-trash"></i></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <!-- MODAL USUARIO -->
    <div id="modalUsuario" class="modal">
        <div class="modal-content">
            <span onclick="cerrarModal()" style="float:right; cursor:pointer; font-size:1.5rem;">&times;</span>
            <h2 id="tituloModal">Usuario</h2>
            
            <form action="guardar_usuario.php" method="POST">
                <input type="hidden" name="accion" id="inputAccion" value="crear">
                <input type="hidden" name="id" id="inputId">

                <div class="form-group">
                    <label>Nombre Completo:</label>
                    <input type="text" name="nombre" id="inputNombre" required placeholder="Ej. Juan Pérez">
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label>Usuario (Login):</label>
                        <input type="text" name="usuario" id="inputUsuario" required>
                    </div>
                    <div class="form-group">
                        <label>Contraseña:</label>
                        <input type="password" name="password" id="inputPassword" placeholder="Solo llenar para cambiar">
                    </div>
                </div>

                <div class="form-group">
                    <label>Rol / Puesto de Trabajo:</label>
                    <select name="rol" id="inputRol" style="background:#f4f4f4; font-weight:bold;">
                        <option value="recepcionista">RECEPCIONISTA</option>
                        <option value="admin">ADMINISTRADOR (Acceso Total)</option>
                        <option value="barra">BARRA / KIOSKO</option>
                        <option value="restaurante">RESTAURANTE</option>
                        <option value="limpieza">LIMPIEZA / HOUSEKEEPING</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Marcar Permisos Permitidos:</label>
                    <div class="permisos-grid">
                        <div class="permiso-item"><label><input type="checkbox" name="permisos[]" value="reservas" class="chk-permiso"> Reservas Web</label></div>
                        <div class="permiso-item"><label><input type="checkbox" name="permisos[]" value="rack" class="chk-permiso"> Rack Hotelero</label></div>
                        <div class="permiso-item"><label><input type="checkbox" name="permisos[]" value="caja" class="chk-permiso"> Caja / Turnos</label></div>
                        <div class="permiso-item"><label><input type="checkbox" name="permisos[]" value="clientes" class="chk-permiso"> Clientes</label></div>
                        <div class="permiso-item"><label><input type="checkbox" name="permisos[]" value="facturacion" class="chk-permiso"> Facturación (Ext)</label></div>
                        
                        <div class="permiso-item"><label><input type="checkbox" name="permisos[]" value="inventario" class="chk-permiso"> Inv. Productos</label></div>
                        <div class="permiso-item"><label><input type="checkbox" name="permisos[]" value="cocina" class="chk-permiso"> Almacén Cocina</label></div>
                        <div class="permiso-item"><label><input type="checkbox" name="permisos[]" value="inv_cuartos" class="chk-permiso"> Inv. Cuartos</label></div>
                        <div class="permiso-item"><label><input type="checkbox" name="permisos[]" value="blancos" class="chk-permiso"> Blancos/Limpieza</label></div>
                        
                        <div class="permiso-item"><label><input type="checkbox" name="permisos[]" value="habitaciones" class="chk-permiso"> Config. Habitaciones</label></div>
                        <div class="permiso-item">
    <label>
        <input type="checkbox" name="permisos[]" value="dashboard" class="chk-permiso"> 
        Dashboard Gerencial
    </label>
                        <div class="permiso-item"><label><input type="checkbox" name="permisos[]" value="usuarios" class="chk-permiso"> Admin Usuarios</label></div>
                    </div>
                </div>

                <button type="submit" style="width:100%; padding:12px; background:#2ecc71; color:white; border:none; border-radius:5px; font-weight:bold; font-size:1rem; margin-top:10px;">GUARDAR DATOS</button>
            </form>
        </div>
    </div>

    <script>
        var modal = document.getElementById("modalUsuario");

        function abrirModalCrear() {
            document.getElementById("tituloModal").innerText = "Nuevo Personal";
            document.getElementById("inputAccion").value = "crear";
            document.getElementById("inputId").value = "";
            document.getElementById("inputNombre").value = "";
            document.getElementById("inputUsuario").value = "";
            document.getElementById("inputPassword").required = true;
            document.querySelectorAll('.chk-permiso').forEach(chk => chk.checked = false);
            modal.style.display = "block";
        }

        function abrirModalEditar(id, nombre, usuario, rol, permisos) {
            document.getElementById("tituloModal").innerText = "Editar Personal";
            document.getElementById("inputAccion").value = "editar";
            document.getElementById("inputId").value = id;
            document.getElementById("inputNombre").value = nombre;
            document.getElementById("inputUsuario").value = usuario;
            document.getElementById("inputRol").value = rol;
            document.getElementById("inputPassword").required = false;

            let listaPermisos = permisos.split(',');
            document.querySelectorAll('.chk-permiso').forEach(chk => {
                chk.checked = listaPermisos.includes(chk.value);
            });

            modal.style.display = "block";
        }

        function cerrarModal() { modal.style.display = "none"; }
        window.onclick = function(e) { if (e.target == modal) cerrarModal(); }
    </script>
</body>
</html>