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

    $fecha_inicio = $_GET['fecha_inicio'] ?? '';
    $fecha_fin = $_GET['fecha_fin'] ?? '';

    $query = "
        SELECT dp.nombre_producto, SUM(dp.cantidad) AS cantidad_vendida
        FROM detalle_pedido dp
        JOIN pedido p ON dp.id_pedido = p.id_pedido
        WHERE dp.id_emprendedor = :id_emprendedor
        AND p.fecha_pedido BETWEEN :fecha_inicio AND :fecha_fin
        GROUP BY dp.nombre_producto
        ORDER BY cantidad_vendida DESC
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
