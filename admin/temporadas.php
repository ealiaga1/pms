<?php
session_start();
include '../db.php';
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

$sql = "SELECT * FROM temporadas ORDER BY inicio ASC";
$res = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Temporadas | Tulumayo</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { font-family: 'Montserrat', sans-serif; background: #ecf0f1; margin: 0; display: flex; height: 100vh; }
        
        /* Sidebar Simple para este archivo */
        .sidebar { width: 250px; background-color: #2E5C38; color: white; display: flex; flex-direction: column; }
        .sidebar h2 { padding: 20px; background: #244a2d; margin: 0; text-align: center; }
        .sidebar a { padding: 15px; color: white; text-decoration: none; display: block; border-bottom: 1px solid rgba(255,255,255,0.1); }
        .sidebar a:hover { background: #3A5A40; }

        .main { flex: 1; padding: 40px; }
        
        table { width: 100%; background: white; border-collapse: collapse; box-shadow: 0 4px 10px rgba(0,0,0,0.1); border-radius: 8px; overflow: hidden; }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #2E5C38; color: white; }
        
        .btn-del { background: #e74c3c; color: white; border: none; padding: 5px 10px; border-radius: 4px; cursor: pointer; }
        .btn-add { background: #2ecc71; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; font-weight: bold; float: right; margin-bottom: 20px; }
    </style>
</head>
<body>

   <?php include 'sidebar.php'; ?>

    <div class="main">
        <h1 style="color:#2E5C38; display:inline-block;">Configuración Temporadas Altas</h1>
        <button class="btn-add" onclick="document.getElementById('modalTemp').style.display='block'">+ Nueva Temporada</button>

        <table>
            <thead>
                <tr>
                    <th>Nombre Temporada</th>
                    <th>Inicia (Mes-Día)</th>
                    <th>Termina (Mes-Día)</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($res)): ?>
                <tr>
                    <td><?php echo $row['nombre']; ?></td>
                    <td><?php echo $row['inicio']; ?></td>
                    <td><?php echo $row['fin']; ?></td>
                    <td>
                        <form action="guardar_temporada.php" method="POST" onsubmit="return confirm('¿Borrar?');">
                            <input type="hidden" name="accion" value="eliminar">
                            <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
                            <button class="btn-del"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- MODAL -->
    <div id="modalTemp" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
        <div style="background:white; width:400px; margin:10% auto; padding:20px; border-radius:8px;">
            <h3>Nueva Temporada Alta</h3>
            <form action="guardar_temporada.php" method="POST">
                <input type="hidden" name="accion" value="crear">
                <label>Nombre:</label>
                <input type="text" name="nombre" required style="width:100%; padding:8px; margin-bottom:10px;">
                
                <label>Desde:</label>
                <input type="date" name="inicio" required style="width:100%; padding:8px; margin-bottom:10px;">
                
                <label>Hasta:</label>
                <input type="date" name="fin" required style="width:100%; padding:8px; margin-bottom:20px;">
                
                <button type="submit" style="width:100%; padding:10px; background:#2ecc71; border:none; color:white; font-weight:bold;">GUARDAR</button>
            </form>
            <button onclick="document.getElementById('modalTemp').style.display='none'" style="margin-top:10px; width:100%; padding:10px; background:#ccc; border:none;">CANCELAR</button>
        </div>
    </div>

</body>
</html>