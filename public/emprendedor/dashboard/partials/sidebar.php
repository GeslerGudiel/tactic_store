<div class="sidebar" id="sidebar">
    <button class="toggle-btn" id="toggleBtn" onclick="toggleSidebar()">☰</button>
    <h4 class="sidebar-item-text">Dashboard</h4><br>

    <?php if ($estado_emprendedor == 3): // 3 = Pendiente de Validación ?>
        <a href="#" onclick="loadContent('notificaciones.php')">
            <i class="fas fa-bell"></i><span class="sidebar-item-text"> Notificaciones</span>
            <?php if ($notificaciones_no_leidas > 0): ?>
                <span class="notification-badge"><?php echo $notificaciones_no_leidas; ?></span>
            <?php endif; ?>
        </a>
        
        <a href="#" onclick="loadContent('ver_perfil.php')">
            <i class="fas fa-user"></i><span class="sidebar-item-text"> Ver Perfil</span>
        </a>
    <?php else: ?>
        <a href="#" onclick="loadContent('notificaciones.php')">
            <i class="fas fa-bell"></i><span class="sidebar-item-text"> Notificaciones</span>
            <?php if ($notificaciones_no_leidas > 0): ?>
                <span class="notification-badge"><?php echo $notificaciones_no_leidas; ?></span>
            <?php endif; ?>
        </a>
        <a href="#" onclick="loadContent('ver_perfil.php')">
            <i class="fas fa-user"></i><span class="sidebar-item-text"> Ver Perfil</span>
        </a>

        <!-- Registrar Cliente -->
        <a href="#" onclick="loadContent('/comercio_electronico/public/emprendedor/venta_local/registrar_cliente.php')">
            <i class="fas fa-user-plus"></i><span class="sidebar-item-text"> Registrar Cliente</span>
        </a>

        <!-- Registrar Venta Local -->
        <a href="#" onclick="loadContent('/comercio_electronico/public/emprendedor/venta_local/registrar_venta_local.php')">
            <i class="fas fa-shopping-cart"></i><span class="sidebar-item-text"> Registrar Venta Local</span>
        </a>

        <!-- Historial de Ventas Locales -->
        <a href="#" onclick="loadContent('venta_local/historial_ventas_locales.php')">
            <i class="fas fa-history"></i><span class="sidebar-item-text"> Historial de Ventas Locales</span>
        </a>

        <a href="#" onclick="loadContent('ver_productos.php?page=ver_productos')">
            <i class="fas fa-box"></i><span class="sidebar-item-text"> Ver Mis Productos</span>
        </a>
        <a href="#" onclick="loadContent('agregar_producto.php')">
            <i class="fas fa-plus"></i><span class="sidebar-item-text"> Agregar Producto</span>
        </a>
        <a href="#" onclick="loadContent('ver_pedidos.php')">
            <i class="fas fa-shopping-basket"></i><span class="sidebar-item-text"> Ver Pedidos</span>
        </a>
        <a href="#" onclick="loadContent('estado_cuenta.php')">
            <i class="fas fa-wallet"></i><span class="sidebar-item-text"> Estado de Cuenta</span>
        </a>
        <a href="#" onclick="loadContent('ver_inventario.php')">
            <i class="fa-solid fa-boxes-stacked"></i><span class="sidebar-item-text"> Inventario</span>
        </a>
        <a href="#" onclick="loadContent('ver_comentarios.php')">
            <i class="fas fa-comment"></i><span class="sidebar-item-text"> Comentarios</span>
        </a>
        <a href="#" onclick="loadContent('ver_reportes.php')">
            <i class="fas fa-chart-line"></i><span class="sidebar-item-text"> Reportes</span>
        </a>
    <?php endif; ?>

    <a href="logout.php"><i class="fas fa-sign-out-alt"></i><span class="sidebar-item-text"> Cerrar Sesión</span></a>
</div>
