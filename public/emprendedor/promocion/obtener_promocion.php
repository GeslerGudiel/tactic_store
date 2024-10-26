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

    // Verificar si se ha enviado el id_promocion
    if (!isset($_GET['id_promocion'])) {
        echo json_encode(['success' => false, 'message' => 'ID de promoción no proporcionado']);
        exit;
    }

    $id_promocion = $_GET['id_promocion'];

    // Preparar la consulta para obtener la promoción específica
    $query = "
        SELECT pr.id_promocion, pr.id_producto, pr.tipo_promocion, 
               pr.porcentaje_descuento, pr.precio_oferta, 
               pr.fecha_inicio, pr.fecha_fin, pr.estado,
               p.nombre_producto
        FROM promocion pr
        INNER JOIN producto p ON pr.id_producto = p.id_producto
        WHERE pr.id_promocion = :id_promocion
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_promocion', $id_promocion, PDO::PARAM_INT);
    $stmt->execute();

    $promocion = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si se encontró la promoción
    if ($promocion) {
        echo json_encode(['success' => true, 'promocion' => $promocion]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Promoción no encontrada']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener la promoción: ' . $e->getMessage()]);
}
?>
