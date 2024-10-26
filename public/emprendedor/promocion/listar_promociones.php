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
    $id_emprendedor = $_SESSION['id_emprendedor'];

    // Obtener los filtros de fecha si existen
    $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
    $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

    // Construir la consulta con filtros de fecha opcionales
    $query = "
        SELECT pr.id_promocion, p.nombre_producto, pr.tipo_promocion, 
               pr.porcentaje_descuento, pr.precio_oferta, pr.fecha_inicio, 
               pr.fecha_fin, pr.estado
        FROM promocion pr
        INNER JOIN producto p ON pr.id_producto = p.id_producto
        WHERE p.id_emprendedor = :id_emprendedor
    ";

    // Agregar condiciones de rango de fechas si se proporcionan
    if ($fechaInicio && $fechaFin) {
        $query .= " AND pr.fecha_inicio >= :fecha_inicio AND pr.fecha_fin <= :fecha_fin";
    }

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);

    if ($fechaInicio && $fechaFin) {
        $stmt->bindParam(':fecha_inicio', $fechaInicio, PDO::PARAM_STR);
        $stmt->bindParam(':fecha_fin', $fechaFin, PDO::PARAM_STR);
    }

    $stmt->execute();
    $promociones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verificar si se encontraron promociones
    if ($promociones) {
        echo json_encode(['success' => true, 'promociones' => $promociones]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontraron promociones']);
    }
} catch (Exception $e) {
    // Manejar cualquier error que ocurra durante la ejecución
    echo json_encode(['success' => false, 'message' => 'Error al listar promociones: ' . $e->getMessage()]);
}
