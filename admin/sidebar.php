<div class="sidebar">
    <!-- LOGO -->
    <div style="text-align: center; padding: 20px 10px; background: #244a2d; border-bottom: 1px solid #3A5A40;">
        <img src="../img/logo1.jpg" alt="Tulumayo Logo" style="width: 100%; max-width: 150px; display: block; margin: 0 auto;">
    </div>
    
    <?php 
    // 1. FUNCIÓN DE PERMISOS
    if (!function_exists('puede')) {
        function puede($permiso) {
            if (!isset($_SESSION['rol'])) return false; 
            if ($_SESSION['rol'] == 'admin') return true; 
            
            $mis_permisos = is_array($_SESSION['permisos']) ? $_SESSION['permisos'] : explode(',', $_SESSION['permisos']);
            return in_array($permiso, $mis_permisos);
        }
    }

    // 2. FUNCIÓN PARA DETECTAR PÁGINA ACTIVA
    // Compara el nombre del archivo actual con el del enlace
    if (!function_exists('estaActivo')) {
        function estaActivo($archivo) {
            $pagina_actual = basename($_SERVER['PHP_SELF']);
            
            // Lógica para sub-páginas (Opcional: para que el menú se quede verde en reportes)
            if ($archivo == 'rack.php' && ($pagina_actual == 'ticket_checkout.php')) return 'class="active"';
            if ($archivo == 'caja.php' && ($pagina_actual == 'reporte_caja.php')) return 'class="active"';
            if ($archivo == 'restaurante.php' && ($pagina_actual == 'ver_comanda.php')) return 'class="active"';
            if ($archivo == 'productos.php' && ($pagina_actual == 'cierre_inventario.php' || $pagina_actual == 'reporte_inventario.php')) return 'class="active"';
            if ($archivo == 'clientes.php' && ($pagina_actual == 'exportar_clientes.php')) return 'class="active"';

            // Comparación normal
            return ($pagina_actual == $archivo) ? 'class="active"' : '';
        }
    }
    ?>

    <!-- ENLACES DEL MENÚ -->

    <?php if(puede('dashboard')): ?>
        <!-- CORREGIDO: Ahora usa la función estaActivo en lugar de estilo fijo -->
        <a href="dashboard.php" <?php echo estaActivo('dashboard.php'); ?>>
            <i class="fas fa-chart-line"></i> Dashboard Gerencial
        </a>
    <?php endif; ?>

    <?php if(puede('reservas')): ?>
        <a href="panel.php" <?php echo estaActivo('panel.php'); ?>>
            <i class="fas fa-calendar-alt"></i> Reservas Web
        </a>
    <?php endif; ?>

    <?php if(puede('rack')): ?>
        <a href="rack.php" <?php echo estaActivo('rack.php'); ?>>
            <i class="fas fa-th-large"></i> Rack Hotelero
        </a>
    <?php endif; ?>

    <?php if(puede('restaurante')): ?>
        <a href="restaurante.php" <?php echo estaActivo('restaurante.php'); ?>>
            <i class="fas fa-utensils"></i> Restaurante
        </a>
    <?php endif; ?>

    <?php if(puede('clientes')): ?>
        <a href="clientes.php" <?php echo estaActivo('clientes.php'); ?>>
            <i class="fas fa-users"></i> Clientes
        </a>
    <?php endif; ?>

    <?php if(puede('caja')): ?>
        <a href="caja.php" <?php echo estaActivo('caja.php'); ?>>
            <i class="fas fa-cash-register"></i> Caja / Turnos
        </a>
    <?php endif; ?>

    <?php if(puede('facturacion')): ?>
        <a href="https://20615071995.facturando.net.pe/login" target="_blank">
            <i class="fas fa-eye"></i> Facturación
        </a>
    <?php endif; ?>

    <?php if(puede('habitaciones')): ?>
        <a href="habitaciones.php" <?php echo estaActivo('habitaciones.php'); ?>>
            <i class="fas fa-bed"></i> Habitaciones
        </a>
    <?php endif; ?>

    <?php if(puede('inventario')): ?>
        <a href="productos.php" <?php echo estaActivo('productos.php'); ?>>
            <i class="fas fa-box"></i> Inventario Productos
        </a>
    <?php endif; ?>

    <?php if(puede('cocina')): ?>
        <a href="cocina.php" <?php echo estaActivo('cocina.php'); ?>>
            <i class="fas fa-utensils"></i> Almacén Cocina
        </a>
    <?php endif; ?>

    <?php if(puede('inv_cuartos')): ?>
        <a href="inventario_habitaciones.php" <?php echo estaActivo('inventario_habitaciones.php'); ?>>
            <i class="fas fa-boxes"></i> Inventario Cuartos
        </a>
    <?php endif; ?>

    <?php if(puede('blancos')): ?>
        <a href="blancos.php" <?php echo estaActivo('blancos.php'); ?>>
            <i class="fas fa-tshirt"></i> Blancos/Limpieza
        </a>
    <?php endif; ?>

    <?php if(puede('usuarios')): ?>
        <a href="usuarios.php" <?php echo estaActivo('usuarios.php'); ?>>
            <i class="fas fa-user-shield"></i> Usuarios
        </a>
    <?php endif; ?>
    
    <!-- Footer del menú -->
    <div style="margin-top: auto;">
        <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> Ver Web Pública</a>
        <a href="logout.php" style="background:#d9534f;"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </div>
</div>
<script>
    (function() {
        var link = document.querySelector("link[rel*='icon']") || document.createElement('link');
        link.type = 'image/png';
        link.rel = 'shortcut icon';
        // Asegúrate que esta ruta apunte a tu imagen real
        link.href = '../img/favicon.png'; 
        document.getElementsByTagName('head')[0].appendChild(link);
    })();
</script>