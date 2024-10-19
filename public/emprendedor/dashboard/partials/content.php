<div class="loading-overlay" id="loading-overlay" style="display: none;">
    <div class="spinner-border text-success" role="status">
        <span class="visually-hidden">Cargando...</span>
    </div>
</div>

<div class="content" id="content">
    <div id="dashboard-content">
        <?php
        // Determinar qué página se va a cargar según el valor de $page
        if ($page == 'ver_productos' && $estado_emprendedor != 3) {
            include 'ver_productos.php';
        } elseif ($page == 'ver_perfil') {
            include 'ver_perfil.php';
        } elseif ($page == 'ver_inventario' && $estado_emprendedor != 3) {
            include 'ver_inventario.php';
        } elseif ($page == 'registrar_venta_local' && $estado_emprendedor != 3) {
            include '/comercio_electronico/public/emprendedor/venta_local/registrar_venta_local.php'; // Asegúrate de que el nombre del archivo sea correcto
        } elseif ($page == 'ver_comentarios' && $estado_emprendedor != 3) {
            include 'ver_comentarios.php';
        } elseif ($page == 'historial_ventas_locales' && $estado_emprendedor != 3) {
            include '../../venta_local/historial_ventas_locales.php'; // Agrega la opción para el historial de ventas locales
        } elseif ($page == 'resumen_emprendedor') {
            include 'resumen_emprendedor.php';
        } else {
            include 'resumen_emprendedor.php';
        }
        ?>
    </div>
</div>
