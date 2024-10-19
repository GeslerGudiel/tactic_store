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

    // Consulta para obtener los productos más vendidos por el emprendedor
    $query = "
        SELECT 
            p.nombre_producto, 
            SUM(dp.cantidad) AS cantidad_vendida
        FROM detalle_pedido dp
        INNER JOIN producto p ON dp.id_producto = p.id_producto
        WHERE p.id_emprendedor = :id_emprendedor
        GROUP BY p.id_producto
        ORDER BY cantidad_vendida DESC
        LIMIT 10";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$productos) {
        echo json_encode(['error' => 'No se encontraron productos vendidos.']);
        exit;
    }

    echo json_encode($productos);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error al cargar los productos vendidos: ' . $e->getMessage()]);
}
?>
