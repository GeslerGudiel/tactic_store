<?php
session_start();
include_once '../../src/config/database.php';

// Verificar que el emprendedor esté autenticado
if (!isset($_SESSION['id_emprendedor'])) {
    echo json_encode(['error' => 'Usuario no autenticado']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

// Consulta para obtener las ventas totales, productos vendidos y ganancia neta
$query = "SELECT 
    SUM(dp.subtotal) as ventas_totales, 
    COUNT(dp.id_producto) as productos_vendidos, 
    SUM(dp.subtotal - (dp.cantidad * p.costo)) as ganancia_neta
    FROM detalle_pedido dp
    JOIN producto p ON dp.id_producto = p.id_producto
    JOIN pedido pe ON dp.id_pedido = pe.id_pedido
    WHERE p.id_emprendedor = :id_emprendedor";

$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->execute();
$resumen = $stmt->fetch(PDO::FETCH_ASSOC);

// Consulta para contar los productos bajos en stock
$query_stock = "SELECT COUNT(*) as productos_bajos_stock FROM producto WHERE id_emprendedor = :id_emprendedor AND stock < 5";
$stmt_stock = $db->prepare($query_stock);
$stmt_stock->bindParam(':id_emprendedor', $id_emprendedor);
$stmt_stock->execute();
$productos_bajos_stock = $stmt_stock->fetch(PDO::FETCH_ASSOC)['productos_bajos_stock'];

// Enviar los resultados como JSON
echo json_encode([
    'ventas_totales' => $resumen['ventas_totales'] ?? 0,
    'productos_vendidos' => $resumen['productos_vendidos'] ?? 0,
    'ganancia_neta' => $resumen['ganancia_neta'] ?? 0,
    'productos_bajos_stock' => $productos_bajos_stock
]);
