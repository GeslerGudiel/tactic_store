<?php
session_start();
include_once '../../../src/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $id_emprendedor = $_SESSION['id_emprendedor'] ?? null;
    if (!$id_emprendedor) {
        throw new Exception('El usuario no ha iniciado sesión correctamente.');
    }

    // Capturar fechas de filtro
    $fecha_inicio = $_GET['fecha_inicio'] ?? '';
    $fecha_fin = $_GET['fecha_fin'] ?? '';

    // Validar fechas
    if (!empty($fecha_inicio) && !empty($fecha_fin) && $fecha_inicio > $fecha_fin) {
        throw new Exception('La fecha de inicio no puede ser mayor que la fecha de fin.');
    }

    // Consulta para ingresos por categorías en ventas en línea
    $query_online = "
        SELECT cat.nombre_categoria, SUM(dp.subtotal) AS total_ingresos
        FROM detalle_pedido dp
        JOIN producto p ON dp.id_producto = p.id_producto
        JOIN categoria cat ON p.id_categoria = cat.id_categoria
        JOIN pedido ped ON dp.id_pedido = ped.id_pedido
        WHERE p.id_emprendedor = :id_emprendedor
        AND ped.fecha_pedido BETWEEN :fecha_inicio AND :fecha_fin
        GROUP BY cat.nombre_categoria";

    // Consulta para ingresos por categorías en ventas locales
    $query_local = "
        SELECT cat.nombre_categoria, SUM(dv.subtotal) AS total_ingresos
        FROM detalle_venta_local dv
        JOIN producto p ON dv.id_producto = p.id_producto
        JOIN categoria cat ON p.id_categoria = cat.id_categoria
        JOIN ventas_locales vl ON dv.id_venta_local = vl.id_venta_local
        WHERE p.id_emprendedor = :id_emprendedor
        AND vl.fecha_venta BETWEEN :fecha_inicio AND :fecha_fin
        GROUP BY cat.nombre_categoria";

    // Preparar y ejecutar consultas
    $stmt_online = $db->prepare($query_online);
    $stmt_local = $db->prepare($query_local);

    $stmt_online->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
    $stmt_local->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
    $stmt_online->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt_local->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt_online->bindParam(':fecha_fin', $fecha_fin);
    $stmt_local->bindParam(':fecha_fin', $fecha_fin);

    $stmt_online->execute();
    $stmt_local->execute();

    $ingresos_online = $stmt_online->fetchAll(PDO::FETCH_ASSOC);
    $ingresos_local = $stmt_local->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['online' => $ingresos_online, 'local' => $ingresos_local]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
    exit;
}
