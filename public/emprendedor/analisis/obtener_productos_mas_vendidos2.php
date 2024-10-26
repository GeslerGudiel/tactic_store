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

    // Consulta para obtener la cantidad vendida en línea y localmente para cada producto
    $query = "
        SELECT 
            p.nombre_producto,
            COALESCE(SUM(dp.cantidad), 0) AS cantidad_online,
            COALESCE(SUM(dvl.cantidad), 0) AS cantidad_local,
            (COALESCE(SUM(dp.cantidad), 0) + COALESCE(SUM(dvl.cantidad), 0)) AS cantidad_total
        FROM producto p
        LEFT JOIN detalle_pedido dp ON p.id_producto = dp.id_producto
        LEFT JOIN pedido po ON dp.id_pedido = po.id_pedido
        LEFT JOIN detalle_venta_local dvl ON p.id_producto = dvl.id_producto
        LEFT JOIN ventas_locales vl ON dvl.id_venta_local = vl.id_venta_local
        WHERE p.id_emprendedor = :id_emprendedor
        AND (po.fecha_pedido BETWEEN :fecha_inicio AND :fecha_fin
             OR vl.fecha_venta BETWEEN :fecha_inicio AND :fecha_fin)
        GROUP BY p.id_producto
        ORDER BY cantidad_total DESC
        LIMIT 10";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_fin', $fecha_fin);

    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($productos);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
