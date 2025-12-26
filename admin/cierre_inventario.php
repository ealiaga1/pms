<?php
session_start();
include '../db.php';
if (!isset($_SESSION['usuario'])) { header("Location: login.php"); exit(); }

// Solo traemos PRODUCTOS (los servicios no se cuentan)
$sql = "SELECT * FROM productos WHERE tipo = 'producto' ORDER BY nombre ASC";
$res = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cierre de Inventario | Tulumayo</title>
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
        
        table { width: 100%; border-collapse: collapse; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        th, td { padding: 15px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #34495e; color: white; text-transform: uppercase; font-size: 0.85rem; }
        tr:hover { background: #f9f9f9; }

        input[type="number"] { padding: 8px; width: 80px; text-align: center; border: 1px solid #ccc; border-radius: 4px; font-weight: bold; }
        
        .diff-neg { color: #e74c3c; font-weight: bold; } /* Rojo si falta */
        .diff-pos { color: #2ecc71; font-weight: bold; } /* Verde si sobra */
        .diff-zero { color: #ccc; }

        .btn-save { background: #e67e22; color: white; border: none; padding: 12px 25px; border-radius: 5px; font-weight: bold; cursor: pointer; font-size: 1rem; }
        .btn-save:hover { background: #d35400; }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>Admin Tulumayo</h2>
        <a href="panel.php"><i class="fas fa-calendar"></i> Reservas Web</a>
        <a href="rack.php"    class="active"><i class="fas fa-th-large"></i> Rack Habitaciones</a>
        <a href="productos.php"><i class="fas fa-box"></i> Inventario</a>
        <a href="habitaciones.php"><i class="fas fa-bed"></i> Habitaciones</a>
        <a href="clientes.php"><i class="fas fa-users"></i> Clientes</a>
        <a href="caja.php"><i class="fas fa-cash-register"></i> Caja / Turnos</a>
        <a href="usuarios.php"><i class="fas fa-cash-register"></i> Usuarios</a>
        <a href="../index.php" target="_blank"><i class="fas fa-eye"></i> Ver Web</a>
        <div style="margin-top: auto;">
            <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Ver Web Pública</a>
            <a href="logout.php" style="background:#d9534f;"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>

    </div>

    <div class="main">
        <div class="header-main">
            <h1>Cierre de Inventario (Físico)</h1>
            <a href="productos.php" style="color: #555; text-decoration: none;">&larr; Volver</a>
        </div>

        <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
            <i class="fas fa-info-circle"></i> <strong>Instrucciones:</strong> Ingresa la cantidad real que cuentas en el almacén. El sistema ajustará el stock automáticamente y registrará las diferencias en el Kardex.
        </div>

        <form action="procesar_inventario.php" method="POST" onsubmit="return confirm('¿Estás seguro de realizar el ajuste de inventario?');">
            <table>
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Stock Sistema</th>
                        <th style="background:#e67e22;">Stock Físico (Real)</th>
                        <th>Diferencia</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($p = mysqli_fetch_assoc($res)) { ?>
                    <tr>
                        <td><?php echo $p['nombre']; ?></td>
                        
                        <!-- Stock Actual (Oculto para enviar, Visible para ver) -->
                        <td>
                            <span id="sis_<?php echo $p['id']; ?>"><?php echo $p['stock']; ?></span>
                            <input type="hidden" name="stock_sistema[<?php echo $p['id']; ?>]" value="<?php echo $p['stock']; ?>">
                        </td>
                        
                        <!-- Input para el Stock Real -->
                        <td style="background:#fffaf4;">
                            <input type="number" name="stock_real[<?php echo $p['id']; ?>]" 
                                   value="<?php echo $p['stock']; ?>" 
                                   oninput="calcularDiferencia(<?php echo $p['id']; ?>)"
                                   id="real_<?php echo $p['id']; ?>" required>
                        </td>
                        
                        <!-- Diferencia Calculada con JS -->
                        <td>
                            <span id="diff_<?php echo $p['id']; ?>" class="diff-zero">0</span>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>

            <div style="margin-top: 20px; text-align: right;">
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> GUARDAR AJUSTES Y CERRAR</button>
            </div>
        </form>
    </div>

    <script>
        function calcularDiferencia(id) {
            let sistema = parseInt(document.getElementById('sis_' + id).innerText);
            let real = parseInt(document.getElementById('real_' + id).value);
            
            // Si está vacío, asumimos 0
            if(isNaN(real)) real = 0;

            let diferencia = real - sistema;
            let spanDiff = document.getElementById('diff_' + id);

            if (diferencia > 0) {
                spanDiff.innerText = "+" + diferencia + " (Sobra)";
                spanDiff.className = "diff-pos";
            } else if (diferencia < 0) {
                spanDiff.innerText = diferencia + " (Falta)";
                spanDiff.className = "diff-neg";
            } else {
                spanDiff.innerText = "OK";
                spanDiff.className = "diff-zero";
            }
        }
    </script>

</body>
</html>