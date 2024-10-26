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

    // Verificar si se recibieron los IDs de las promociones
    if (empty($_POST['ids_promociones'])) {
        echo json_encode(['success' => false, 'message' => 'No se seleccionaron promociones para eliminar.']);
        exit;
    }

    $ids = $_POST['ids_promociones'];

    // Iniciar una transacción para asegurar la consistencia
    $db->beginTransaction();

    // Obtener los productos asociados a las promociones
    $queryProductos = "
        SELECT id_producto 
        FROM promocion 
        WHERE id_promocion IN (" . implode(',', array_map('intval', $ids)) . ")
    ";
    $stmtProductos = $db->prepare($queryProductos);
    $stmtProductos->execute();
    $productos = $stmtProductos->fetchAll(PDO::FETCH_COLUMN);

    // Eliminar las promociones seleccionadas
    $queryEliminar = "DELETE FROM promocion WHERE id_promocion IN (" . implode(',', array_map('intval', $ids)) . ")";
    $stmtEliminar = $db->prepare($queryEliminar);

    if (!$stmtEliminar->execute()) {
        // Si falla la eliminación, revertir la transacción
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => 'No se pudieron eliminar las promociones.']);
        exit;
    }

    // Actualizar los productos afectados para eliminar la promoción
    if (!empty($productos)) {
        $queryActualizarProductos = "
            UPDATE producto 
            SET tiene_promocion = 0, precio_promocional = NULL 
            WHERE id_producto IN (" . implode(',', array_map('intval', $productos)) . ")
        ";
        $stmtActualizar = $db->prepare($queryActualizarProductos);

        if (!$stmtActualizar->execute()) {
            // Si falla la actualización, revertir la transacción
            $db->rollBack();
            echo json_encode(['success' => false, 'message' => 'No se pudieron actualizar los productos.']);
            exit;
        }
    }

    // Confirmar la transacción si todo fue exitoso
    $db->commit();
    echo json_encode(['success' => true, 'message' => 'Promociones eliminadas correctamente.']);
} catch (Exception $e) {
    // En caso de error, revertir la transacción y reportar el error
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error al eliminar las promociones: ' . $e->getMessage()]);
}
