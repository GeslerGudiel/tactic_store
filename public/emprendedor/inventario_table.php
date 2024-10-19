<table class="table table-bordered table-inventario">
    <thead>
        <tr>
            <th><a href="#" class="order-link" data-order_by="id_producto" data-order_direction="<?php echo $order_by == 'id_producto' ? $order_direction : 'asc'; ?>"><i class="fas fa-hashtag"></i> ID Producto</a></th>
            <th><a href="#" class="order-link" data-order_by="nombre_producto" data-order_direction="<?php echo $order_by == 'nombre_producto' ? $order_direction : 'asc'; ?>"><i class="fas fa-tag"></i> Nombre</a></th>
            <th><a href="#" class="order-link" data-order_by="stock" data-order_direction="<?php echo $order_by == 'stock' ? $order_direction : 'asc'; ?>"><i class="fas fa-warehouse"></i> Stock</a></th>
            <th><a href="#" class="order-link" data-order_by="precio" data-order_direction="<?php echo $order_by == 'precio' ? $order_direction : 'asc'; ?>"><i class="fas fa-money-check-alt"></i> Precio</a></th>
            <th><a href="#" class="order-link" data-order_by="costo" data-order_direction="<?php echo $order_by == 'costo' ? $order_direction : 'asc'; ?>"><i class="fas fa-dollar-sign"></i> Costo</a></th>
            <th><a href="#" class="order-link" data-order_by="ganancia_unitaria" data-order_direction="<?php echo $order_by == 'ganancia_unitaria' ? $order_direction : 'asc'; ?>"><i class="fas fa-coins"></i> Ganancia Unitaria</a></th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($productos)) : ?>
            <?php foreach ($productos as $producto): ?>
                <tr>
                    <td><?php echo $producto['id_producto']; ?></td>
                    <td><?php echo $producto['nombre_producto']; ?></td>
                    <td><?php echo $producto['stock']; ?></td>
                    <td>Q. <?php echo number_format($producto['precio'], 2); ?></td>
                    <td>Q. <?php echo number_format($producto['costo'], 2); ?></td>
                    <td>Q. <?php echo number_format($producto['ganancia_unitaria'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else : ?>
            <tr>
                <td colspan="6">No hay productos disponibles.</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>
