<?php
session_start();
include_once '../../../src/config/database.php';

if (!isset($_SESSION['id_emprendedor'])) {
    echo json_encode(['error' => 'No se ha iniciado sesión correctamente.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

// Capturar las fechas de inicio y fin
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

// Consulta para obtener el total de ventas online
$query_online = "
    SELECT SUM(p.monto) AS total_online 
    FROM pago p
    INNER JOIN pedido pe ON p.id_pedido = pe.id_pedido
    INNER JOIN detalle_pedido dp ON pe.id_pedido = dp.id_pedido
    WHERE dp.id_emprendedor = :id_emprendedor
    AND pe.fecha_pedido BETWEEN :fecha_inicio AND :fecha_fin
    AND p.estado_pago = 'Completado'";

$stmt_online = $db->prepare($query_online);
$stmt_online->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
$stmt_online->bindParam(':fecha_inicio', $fecha_inicio);
$stmt_online->bindParam(':fecha_fin', $fecha_fin);
$stmt_online->execute();
$total_online = $stmt_online->fetch(PDO::FETCH_ASSOC)['total_online'] ?? 0;

// Consulta para obtener el total de ventas locales
$query_local = "
    SELECT SUM(v.total) AS total_local 
    FROM ventas_locales v
    WHERE v.id_emprendedor = :id_emprendedor
    AND v.fecha_venta BETWEEN :fecha_inicio AND :fecha_fin";

$stmt_local = $db->prepare($query_local);
$stmt_local->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
$stmt_local->bindParam(':fecha_inicio', $fecha_inicio);
$stmt_local->bindParam(':fecha_fin', $fecha_fin);
$stmt_local->execute();
$total_local = $stmt_local->fetch(PDO::FETCH_ASSOC)['total_local'] ?? 0;

echo json_encode([
    'total_online' => $total_online,
    'total_local' => $total_local,
]);
exit;
