<?php
session_start();
include_once '../../../../src/config/database.php';

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    echo 'Acceso denegado';
    exit;
}

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

// Verificar que se haya recibido un ID de venta válido
$id_venta_local = isset($_GET['id_venta']) ? $_GET['id_venta'] : null;

if (!$id_venta_local) {
    echo '<p class="text-danger">No se encontró la venta.</p>';
    exit;
}

// Obtener los detalles de la venta
$queryVenta = "SELECT vl.fecha_venta, vl.total, ce.nombre_cliente, ce.telefono_cliente 
               FROM ventas_locales vl 
               JOIN cliente_emprendedor ce ON vl.id_cliente_emprendedor = ce.id_cliente_emprendedor 
               WHERE vl.id_venta_local = :id_venta_local AND vl.id_emprendedor = :id_emprendedor";
$stmtVenta = $db->prepare($queryVenta);
$stmtVenta->bindParam(':id_venta_local', $id_venta_local);
$stmtVenta->bindParam(':id_emprendedor', $id_emprendedor);
$stmtVenta->execute();
$venta = $stmtVenta->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    echo '<p class="text-danger">No se encontró la venta.</p>';
    exit;
}

// Obtener los productos de la venta
$queryDetalle = "SELECT dv.cantidad, dv.precio_unitario, p.nombre_producto 
                 FROM detalle_venta_local dv 
                 JOIN producto p ON dv.id_producto = p.id_producto 
                 WHERE dv.id_venta_local = :id_venta_local";
$stmtDetalle = $db->prepare($queryDetalle);
$stmtDetalle->bindParam(':id_venta_local', $id_venta_local);
$stmtDetalle->execute();
$productos = $stmtDetalle->fetchAll(PDO::FETCH_ASSOC);
?>

<div>
    <h5>Cliente: <?php echo htmlspecialchars($venta['nombre_cliente']); ?></h5>
    <h5>Teféfono Cliente: <?php echo htmlspecialchars($venta['telefono_cliente']); ?></h5>
    <p>Fecha de Venta: <?php echo htmlspecialchars($venta['fecha_venta']); ?></p>
    <p>Total de la Venta: Q<?php echo number_format($venta['total'], 2); ?></p>

    <h6>Productos Vendidos:</h6>
    <ul class="list-group">
        <?php foreach ($productos as $producto): ?>
            <li class="list-group-item">
                <?php echo htmlspecialchars($producto['nombre_producto']); ?> - 
                Cantidad: <?php echo $producto['cantidad']; ?> - 
                Precio Unitario: Q<?php echo number_format($producto['precio_unitario'], 2); ?> - 
                Subtotal: Q<?php echo number_format($producto['cantidad'] * $producto['precio_unitario'], 2); ?>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
