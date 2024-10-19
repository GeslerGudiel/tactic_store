<?php
session_start();

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    header("Location: ../../../auth/login.php");
    exit;
}

include_once '../../../../src/config/database.php';

$fechaInicio = $_GET['fecha_inicio'] ?? date('Y-m-01');
$fechaFin = $_GET['fecha_fin'] ?? date('Y-m-d');

try {
    $database = new Database();
    $db = $database->getConnection();
    $id_emprendedor = $_SESSION['id_emprendedor'];

    // Top 5 Clientes
    $queryTopClientes = "SELECT ce.nombre_cliente, COUNT(vl.id_venta_local) AS total_ventas
                         FROM ventas_locales vl
                         JOIN cliente_emprendedor ce ON vl.id_cliente_emprendedor = ce.id_cliente_emprendedor
                         WHERE vl.id_emprendedor = :id_emprendedor
                         AND vl.fecha_venta BETWEEN :fecha_inicio AND :fecha_fin
                         GROUP BY ce.nombre_cliente
                         ORDER BY total_ventas DESC
                         LIMIT 5";
    $stmtTopClientes = $db->prepare($queryTopClientes);
    $stmtTopClientes->bindParam(':id_emprendedor', $id_emprendedor);
    $stmtTopClientes->bindParam(':fecha_inicio', $fechaInicio);
    $stmtTopClientes->bindParam(':fecha_fin', $fechaFin);
    $stmtTopClientes->execute();
    $topClientes = $stmtTopClientes->fetchAll(PDO::FETCH_ASSOC);

    // Top 5 Productos
    $queryTopProductos = "SELECT p.nombre_producto, SUM(dv.cantidad) AS total_vendido
                          FROM detalle_venta_local dv
                          JOIN producto p ON dv.id_producto = p.id_producto
                          JOIN ventas_locales vl ON dv.id_venta_local = vl.id_venta_local
                          WHERE vl.id_emprendedor = :id_emprendedor
                          AND vl.fecha_venta BETWEEN :fecha_inicio AND :fecha_fin
                          GROUP BY p.nombre_producto
                          ORDER BY total_vendido DESC
                          LIMIT 5";
    $stmtTopProductos = $db->prepare($queryTopProductos);
    $stmtTopProductos->bindParam(':id_emprendedor', $id_emprendedor);
    $stmtTopProductos->bindParam(':fecha_inicio', $fechaInicio);
    $stmtTopProductos->bindParam(':fecha_fin', $fechaFin);
    $stmtTopProductos->execute();
    $topProductos = $stmtTopProductos->fetchAll(PDO::FETCH_ASSOC);

    // Generar HTML para los resultados
    ob_start();
    ?>
    <div class="row">
        <div class="col-md-6">
            <h4>Top 5 Clientes</h4>
            <ul class="list-group">
                <?php foreach ($topClientes as $cliente): ?>
                    <li class="list-group-item">
                        <?= htmlspecialchars($cliente['nombre_cliente']) ?>: <?= $cliente['total_ventas'] ?> ventas
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="col-md-6">
            <h4>Top 5 Productos Vendidos</h4>
            <ul class="list-group">
                <?php foreach ($topProductos as $producto): ?>
                    <li class="list-group-item">
                        <?= htmlspecialchars($producto['nombre_producto']) ?>: <?= $producto['total_vendido'] ?> unidades
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
    <?php
    echo ob_get_clean();
} catch (Exception $e) {
    echo "<p class='text-danger'>Error al cargar los datos.</p>";
}
?>
