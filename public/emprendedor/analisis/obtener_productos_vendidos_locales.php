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
        SELECT p.nombre_producto, SUM(dv.cantidad) AS cantidad_vendida
        FROM detalle_venta_local dv
        JOIN producto p ON dv.id_producto = p.id_producto
        JOIN ventas_locales vl ON dv.id_venta_local = vl.id_venta_local
        WHERE vl.id_emprendedor = :id_emprendedor
        AND vl.fecha_venta BETWEEN :fecha_inicio AND :fecha_fin
        GROUP BY p.nombre_producto
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
