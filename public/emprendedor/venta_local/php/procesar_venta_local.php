<?php
session_start();
include_once '../../../../src/config/database.php';

$response = ['success' => false, 'message' => 'Error al procesar la solicitud.'];

try {
    // Verificar si hay datos enviados
    if (!isset($_POST['id_cliente_emprendedor']) || !isset($_POST['carrito'])) {
        $response['message'] = 'Datos incompletos.';
        echo json_encode($response);
        exit;
    }

    // Obtener los datos enviados
    $id_cliente_emprendedor = intval($_POST['id_cliente_emprendedor']);
    $carrito = json_decode($_POST['carrito'], true);
    $id_emprendedor = $_SESSION['id_emprendedor'] ?? null;

    if (!$id_emprendedor) {
        $response['message'] = 'Usuario no autenticado.';
        echo json_encode($response);
        exit;
    }

    // Calcular el total de la venta
    $total_venta = 0;
    foreach ($carrito as $producto) {
        $subtotal = floatval($producto['precio']) * intval($producto['cantidad']);
        $total_venta += $subtotal;
    }

    // Iniciar la conexión con la base de datos
    $database = new Database();
    $db = $database->getConnection();

    // Iniciar una transacción
    $db->beginTransaction();

    // Insertar la venta en la tabla `ventas_locales`
    $query_venta = "INSERT INTO ventas_locales (id_emprendedor, total, fecha_venta, id_cliente_emprendedor) 
                    VALUES (:id_emprendedor, :total, NOW(), :id_cliente)";
    $stmt_venta = $db->prepare($query_venta);
    $stmt_venta->bindParam(':id_emprendedor', $id_emprendedor);
    $stmt_venta->bindParam(':total', $total_venta);
    $stmt_venta->bindParam(':id_cliente', $id_cliente_emprendedor);
    $stmt_venta->execute();

    // Obtener el ID de la venta recién insertada
    $id_venta_local = $db->lastInsertId();

    // Insertar los detalles de la venta en la tabla `detalle_venta_local`
    $query_detalle = "INSERT INTO detalle_venta_local (id_venta_local, id_producto, cantidad, precio_unitario, subtotal) 
                      VALUES (:id_venta, :id_producto, :cantidad, :precio_unitario, :subtotal)";
    $stmt_detalle = $db->prepare($query_detalle);

    foreach ($carrito as $producto) {
        $id_producto = intval($producto['id']);
        $cantidad = intval($producto['cantidad']);
        $precio_unitario = floatval($producto['precio']);
        $subtotal = $precio_unitario * $cantidad;

        $stmt_detalle->bindParam(':id_venta', $id_venta_local);
        $stmt_detalle->bindParam(':id_producto', $id_producto);
        $stmt_detalle->bindParam(':cantidad', $cantidad);
        $stmt_detalle->bindParam(':precio_unitario', $precio_unitario);
        $stmt_detalle->bindParam(':subtotal', $subtotal);
        $stmt_detalle->execute();

        // Actualizar el stock del producto
        $query_update_stock = "UPDATE producto SET stock = stock - :cantidad WHERE id_producto = :id_producto";
        $stmt_update_stock = $db->prepare($query_update_stock);
        $stmt_update_stock->bindParam(':cantidad', $cantidad);
        $stmt_update_stock->bindParam(':id_producto', $id_producto);
        $stmt_update_stock->execute();
    }

    // Confirmar la transacción
    $db->commit();

    $response['success'] = true;
    $response['message'] = 'Venta registrada exitosamente.';
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    $response['message'] = 'Error: ' . $e->getMessage();
}

// Responder con el resultado del proceso
echo json_encode($response);
