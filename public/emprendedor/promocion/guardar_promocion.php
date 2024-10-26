<?php
session_start();

// Verificar si el usuario está autenticado y tiene el rol adecuado
if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

include_once '../../../src/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Validar los datos enviados
    if (
        empty($_POST['tipo_promocion']) || empty($_POST['fecha_inicio']) ||
        empty($_POST['fecha_fin']) || empty($_POST['id_producto'])
    ) {
        echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
        exit;
    }

    // Asignación de datos del formulario
    $tipo_promocion = $_POST['tipo_promocion'];
    $porcentaje_descuento = $_POST['porcentaje_descuento'] ?? NULL;
    $precio_descuento = $_POST['precio_promocional'] ?? NULL;
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];
    $id_producto = $_POST['id_producto'];

    // Validar rango de fechas
    if ($fecha_inicio > $fecha_fin) {
        echo json_encode(['success' => false, 'message' => 'La fecha de inicio no puede ser mayor que la fecha de fin.']);
        exit;
    }

    // Verificar si ya existe una promoción activa para este producto
    $queryVerificar = "
        SELECT * FROM promocion 
        WHERE id_producto = :id_producto AND estado = 'Activo'
    ";
    $stmtVerificar = $db->prepare($queryVerificar);
    $stmtVerificar->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
    $stmtVerificar->execute();

    if ($stmtVerificar->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'Este producto ya tiene una promoción activa.']);
        exit;
    }

    // Iniciar una transacción para asegurar ambas operaciones
    $db->beginTransaction();

    // Insertar la nueva promoción
    $queryPromocion = "
        INSERT INTO promocion (id_producto, tipo_promocion, porcentaje_descuento, 
                               precio_oferta, fecha_inicio, fecha_fin, estado)
        VALUES (:id_producto, :tipo_promocion, :porcentaje_descuento, 
                :precio_descuento, :fecha_inicio, :fecha_fin, 'Activo')
    ";
    $stmtPromocion = $db->prepare($queryPromocion);
    $stmtPromocion->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
    $stmtPromocion->bindParam(':tipo_promocion', $tipo_promocion, PDO::PARAM_STR);
    $stmtPromocion->bindParam(':porcentaje_descuento', $porcentaje_descuento, PDO::PARAM_STR);
    $stmtPromocion->bindParam(':precio_descuento', $precio_descuento, PDO::PARAM_STR);
    $stmtPromocion->bindParam(':fecha_inicio', $fecha_inicio, PDO::PARAM_STR);
    $stmtPromocion->bindParam(':fecha_fin', $fecha_fin, PDO::PARAM_STR);

    if (!$stmtPromocion->execute()) {
        $db->rollBack(); // Revertir si falla
        echo json_encode(['success' => false, 'message' => 'No se pudo crear la promoción.']);
        exit;
    }

    // Actualizar el producto con la promoción aplicada
    $queryProducto = "
        UPDATE producto 
        SET tiene_promocion = 1, precio_promocional = :precio_descuento 
        WHERE id_producto = :id_producto
    ";
    $stmtProducto = $db->prepare($queryProducto);
    $stmtProducto->bindParam(':precio_descuento', $precio_descuento, PDO::PARAM_STR);
    $stmtProducto->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);

    if (!$stmtProducto->execute()) {
        $db->rollBack(); // Revertir si falla
        echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el producto con la promoción.']);
        exit;
    }

    // Confirmar la transacción
    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Promoción creada exitosamente.']);
} catch (Exception $e) {
    $db->rollBack(); // Revertir en caso de error
    echo json_encode(['success' => false, 'message' => 'Error al guardar la promoción: ' . $e->getMessage()]);
}
