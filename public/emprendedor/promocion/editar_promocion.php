<?php
session_start();

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

include_once '../../../src/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Verificar que los datos requeridos estén presentes
    if (empty($_POST['id_promocion']) || empty($_POST['id_producto']) || empty($_POST['tipo_promocion'])) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
        exit;
    }

    $id_promocion = $_POST['id_promocion'];
    $id_producto = $_POST['id_producto'];
    $tipo_promocion = $_POST['tipo_promocion'];
    $porcentaje_descuento = $_POST['porcentaje_descuento'] ?? null;
    $precio_promocional = $_POST['precio_promocional'] ?? null;
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    // Actualizar la promoción
    $queryPromocion = "
        UPDATE promocion 
        SET id_producto = :id_producto, tipo_promocion = :tipo_promocion, 
            porcentaje_descuento = :porcentaje_descuento, precio_oferta = :precio_promocional, 
            fecha_inicio = :fecha_inicio, fecha_fin = :fecha_fin 
        WHERE id_promocion = :id_promocion
    ";
    $stmtPromocion = $db->prepare($queryPromocion);
    $stmtPromocion->bindParam(':id_producto', $id_producto);
    $stmtPromocion->bindParam(':tipo_promocion', $tipo_promocion);
    $stmtPromocion->bindParam(':porcentaje_descuento', $porcentaje_descuento);
    $stmtPromocion->bindParam(':precio_promocional', $precio_promocional);
    $stmtPromocion->bindParam(':fecha_inicio', $fecha_inicio);
    $stmtPromocion->bindParam(':fecha_fin', $fecha_fin);
    $stmtPromocion->bindParam(':id_promocion', $id_promocion);

    if ($stmtPromocion->execute()) {
        // Actualizar el producto con el precio promocional y marcar que tiene promoción
        $queryProducto = "
            UPDATE producto 
            SET precio_promocional = :precio_promocional, tiene_promocion = 1 
            WHERE id_producto = :id_producto
        ";
        $stmtProducto = $db->prepare($queryProducto);
        $stmtProducto->bindParam(':precio_promocional', $precio_promocional);
        $stmtProducto->bindParam(':id_producto', $id_producto);
        $stmtProducto->execute();

        echo json_encode(['success' => true, 'message' => 'Promoción actualizada exitosamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo actualizar la promoción.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
