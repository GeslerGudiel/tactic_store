<?php
session_start();
include_once '../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Capturar los filtros de canal y fechas
$canal = $_GET['canal'] ?? 'online';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

// Construir la consulta SQL según el canal de ventas
if ($canal === 'online') {
    $query = "
        SELECT DATE(p.fecha_pedido) AS fecha, SUM(pa.monto) AS ingresos
        FROM pedido p
        JOIN pago pa ON p.id_pedido = pa.id_pedido
        WHERE p.estado_pedido = 'Completado'
        AND pa.fecha_pago BETWEEN :fecha_inicio AND :fecha_fin
        GROUP BY fecha ORDER BY fecha ASC";
} else {
    $query = "
        SELECT DATE(v.fecha_venta) AS fecha, SUM(v.total) AS ingresos
        FROM ventas_locales v
        WHERE v.fecha_venta BETWEEN :fecha_inicio AND :fecha_fin
        GROUP BY fecha ORDER BY fecha ASC";
}

$stmt = $db->prepare($query);
$stmt->bindParam(':fecha_inicio', $fecha_inicio);
$stmt->bindParam(':fecha_fin', $fecha_fin);
$stmt->execute();
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Preparar los datos para la gráfica
$labels = [];
$ingresos = [];

foreach ($resultados as $fila) {
    $labels[] = $fila['fecha'];
    $ingresos[] = $fila['ingresos'];
}

echo json_encode(['labels' => $labels, 'ingresos' => $ingresos]);
