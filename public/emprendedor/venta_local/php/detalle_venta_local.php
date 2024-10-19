<?php
session_start();
if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    header("Location: ../../auth/login.php");
    exit;
}

include_once '../../../../src/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $id_venta = isset($_GET['id_venta']) ? $_GET['id_venta'] : null;

    if (!$id_venta) {
        echo "<p class='text-danger'>ID de venta no especificado.</p>";
        exit;
    }

    // Obtener los detalles de los productos de la venta
    $query = "SELECT dv.cantidad, dv.precio_unitario, dv.subtotal, p.nombre_producto 
              FROM detalle_venta_local dv 
              JOIN producto p ON dv.id_producto = p.id_producto 
              WHERE dv.id_venta_local = :id_venta";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
    $stmt->execute();
    $detalles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener el total de la venta
    $queryTotal = "SELECT total FROM ventas_locales WHERE id_venta_local = :id_venta";
    $stmtTotal = $db->prepare($queryTotal);
    $stmtTotal->bindParam(':id_venta', $id_venta, PDO::PARAM_INT);
    $stmtTotal->execute();
    $totalVenta = $stmtTotal->fetchColumn();

} catch (Exception $e) {
    echo "<p class='text-danger'>Error al cargar los detalles de la venta. Por favor, inténtelo más tarde.</p>";
    exit;
}
?>

<?php if ($detalles && count($detalles) > 0): ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cantidad</th>
                <th>Precio Unitario (Q)</th>
                <th>Subtotal (Q)</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalles as $detalle): ?>
                <tr>
                    <td><?php echo htmlspecialchars($detalle['nombre_producto']); ?></td>
                    <td><?php echo htmlspecialchars($detalle['cantidad']); ?></td>
                    <td><?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                    <td><?php echo number_format($detalle['subtotal'], 2); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="mt-3">
        <strong>Total de la Venta: Q<?php echo number_format($totalVenta, 2); ?></strong>
    </div>
<?php else: ?>
    <p class="text-center">No hay detalles disponibles para esta venta.</p>
<?php endif; ?>
