<div class="row">
    <div class="col-md-4">
        <h4>Top 5 Clientes</h4>
        <ul id="topClientes" class="list-group">
            <?php foreach ($topClientes as $cliente): ?>
                <li class="list-group-item">
                    <?= htmlspecialchars($cliente['nombre_cliente']) ?>: <?= $cliente['total_ventas'] ?> ventas
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="col-md-4">
        <h4>Top 5 Productos Vendidos</h4>
        <ul id="topProductos" class="list-group">
            <?php foreach ($topProductos as $producto): ?>
                <li class="list-group-item">
                    <?= htmlspecialchars($producto['nombre_producto']) ?>: <?= $producto['total_vendido'] ?> unidades
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="col-md-4">
        <h4>Top 5 Meses con Mayor Venta</h4>
        <ul id="topMeses" class="list-group">
            <?php foreach ($topMeses as $mes): ?>
                <li class="list-group-item">
                    <?= htmlspecialchars($mes['mes']) ?>: Q<?= number_format($mes['total_ventas'], 2) ?>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
