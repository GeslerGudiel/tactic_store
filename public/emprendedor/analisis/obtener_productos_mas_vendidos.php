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

    // Capturar las fechas del filtro
    $fecha_inicio = $_GET['fecha_inicio'] ?? '';
    $fecha_fin = $_GET['fecha_fin'] ?? '';

    // Consulta para obtener los productos más vendidos
    $query = "
        SELECT 
            dp.nombre_producto, 
            SUM(dp.cantidad) AS total_vendido, 
            SUM(dp.subtotal) AS total_ingresos
        FROM detalle_pedido dp
        JOIN pedido p ON dp.id_pedido = p.id_pedido
        WHERE p.fecha_pedido BETWEEN :fecha_inicio AND :fecha_fin
        AND dp.id_emprendedor = :id_emprendedor
        GROUP BY dp.id_producto
        ORDER BY total_vendido DESC
        LIMIT 5";  // Los 5 productos más vendidos

    $stmt = $db->prepare($query);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_fin', $fecha_fin);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
    $stmt->execute();

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($productos);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
