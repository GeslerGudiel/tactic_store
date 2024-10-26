<?php
include_once '../../../src/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Seleccionar promociones expiradas
    $queryPromociones = "
        SELECT id_promocion, id_producto 
        FROM promocion 
        WHERE fecha_fin < CURDATE() AND estado = 'Activo'
    ";
    $stmtPromociones = $db->prepare($queryPromociones);
    $stmtPromociones->execute();
    $promocionesExpiradas = $stmtPromociones->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($promocionesExpiradas)) {
        // Obtener los IDs de las promociones y productos
        $idsPromociones = array_column($promocionesExpiradas, 'id_promocion');
        $idsProductos = array_column($promocionesExpiradas, 'id_producto');

        // Desactivar promociones expiradas
        $queryDesactivarPromociones = "
            UPDATE promocion 
            SET estado = 'Inactivo' 
            WHERE id_promocion IN (" . implode(',', array_map('intval', $idsPromociones)) . ")
        ";
        $stmtDesactivar = $db->prepare($queryDesactivarPromociones);
        $stmtDesactivar->execute();

        // Actualizar los productos eliminando la promoción aplicada
        $queryActualizarProductos = "
            UPDATE producto 
            SET tiene_promocion = 0, precio_promocional = NULL 
            WHERE id_producto IN (" . implode(',', array_map('intval', $idsProductos)) . ")
        ";
        $stmtActualizar = $db->prepare($queryActualizarProductos);
        $stmtActualizar->execute();

        echo json_encode(['success' => true, 'message' => 'Promociones expiradas desactivadas y productos actualizados.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No hay promociones expiradas.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
