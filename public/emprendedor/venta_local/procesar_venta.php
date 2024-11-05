<?php
session_start();
include_once '../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$id_emprendedor = $_SESSION['id_emprendedor'] ?? null;
$id_cliente_emprendedor = $_POST['id_cliente_emprendedor'] ?? null;
$carrito = json_decode($_POST['carrito'], true);
$total = array_reduce($carrito, function($carry, $item) {
    return $carry + ($item['precio'] * $item['cantidad']);
}, 0);
$fecha_venta = date('Y-m-d H:i:s');

if (!$id_emprendedor || !$id_cliente_emprendedor || empty($carrito)) {
    echo json_encode(['success' => false, 'message' => 'Datos incompletos para registrar la venta']);
    exit;
}

// Iniciar transacción para asegurar que todas las operaciones se realicen correctamente
$db->beginTransaction();

try {
    // Insertar venta en la tabla ventas_locales
    $queryVenta = "INSERT INTO ventas_locales (id_emprendedor, id_cliente_emprendedor, total, fecha_venta)
                   VALUES (:id_emprendedor, :id_cliente_emprendedor, :total, :fecha_venta)";
    $stmtVenta = $db->prepare($queryVenta);
    $stmtVenta->bindParam(':id_emprendedor', $id_emprendedor);
    $stmtVenta->bindParam(':id_cliente_emprendedor', $id_cliente_emprendedor);
    $stmtVenta->bindParam(':total', $total);
    $stmtVenta->bindParam(':fecha_venta', $fecha_venta);

    if ($stmtVenta->execute()) {
        $id_venta_local = $db->lastInsertId();

        // Preparar consulta para insertar detalles de la venta
        $queryDetalle = "INSERT INTO detalle_venta_local (id_venta_local, id_producto, cantidad, precio_unitario, subtotal)
                         VALUES (:id_venta_local, :id_producto, :cantidad, :precio_unitario, :subtotal)";
        $stmtDetalle = $db->prepare($queryDetalle);

        // Preparar consulta para actualizar el stock de cada producto
        $queryUpdateStock = "UPDATE producto SET stock = stock - :cantidad WHERE id_producto = :id_producto";
        $stmtUpdateStock = $db->prepare($queryUpdateStock);

        // Insertar cada producto en detalle_venta_local y actualizar el stock
        foreach ($carrito as $producto) {
            $subtotal = $producto['precio'] * $producto['cantidad'];

            // Insertar detalle de venta
            $stmtDetalle->bindParam(':id_venta_local', $id_venta_local);
            $stmtDetalle->bindParam(':id_producto', $producto['id']);
            $stmtDetalle->bindParam(':cantidad', $producto['cantidad']);
            $stmtDetalle->bindParam(':precio_unitario', $producto['precio']);
            $stmtDetalle->bindParam(':subtotal', $subtotal);
            $stmtDetalle->execute();

            // Actualizar el stock del producto
            $stmtUpdateStock->bindParam(':id_producto', $producto['id']);
            $stmtUpdateStock->bindParam(':cantidad', $producto['cantidad']);
            $stmtUpdateStock->execute();
        }

        // Confirmar la transacción
        $db->commit();
        echo json_encode(['success' => true, 'message' => 'Venta registrada con éxito y stock actualizado']);
    } else {
        // Revertir la transacción en caso de error
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'Error al registrar la venta']);
    }
} catch (Exception $e) {
    // Revertir la transacción en caso de excepción
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al procesar la venta: ' . $e->getMessage()]);
}
?>
