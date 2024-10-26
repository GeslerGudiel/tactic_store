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

    // Capturar las fechas de los dos períodos
    $periodo1_inicio = $_GET['periodo1_inicio'] ?? '';
    $periodo1_fin = $_GET['periodo1_fin'] ?? '';
    $periodo2_inicio = $_GET['periodo2_inicio'] ?? '';
    $periodo2_fin = $_GET['periodo2_fin'] ?? '';

    // Consulta para el primer período
    $query_periodo1 = "
        SELECT SUM(dp.subtotal) AS total_ingresos
        FROM detalle_pedido dp
        JOIN pedido p ON dp.id_pedido = p.id_pedido
        WHERE p.fecha_pedido BETWEEN :inicio1 AND :fin1 
        AND dp.id_emprendedor = :id_emprendedor";

    // Consulta para el segundo período
    $query_periodo2 = "
        SELECT SUM(dp.subtotal) AS total_ingresos
        FROM detalle_pedido dp
        JOIN pedido p ON dp.id_pedido = p.id_pedido
        WHERE p.fecha_pedido BETWEEN :inicio2 AND :fin2 
        AND dp.id_emprendedor = :id_emprendedor";

    $stmt1 = $db->prepare($query_periodo1);
    $stmt2 = $db->prepare($query_periodo2);

    $stmt1->bindParam(':inicio1', $periodo1_inicio);
    $stmt1->bindParam(':fin1', $periodo1_fin);
    $stmt1->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);

    $stmt2->bindParam(':inicio2', $periodo2_inicio);
    $stmt2->bindParam(':fin2', $periodo2_fin);
    $stmt2->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);

    $stmt1->execute();
    $stmt2->execute();

    $ingreso_periodo1 = $stmt1->fetch(PDO::FETCH_ASSOC)['total_ingresos'] ?? 0;
    $ingreso_periodo2 = $stmt2->fetch(PDO::FETCH_ASSOC)['total_ingresos'] ?? 0;

    echo json_encode([
        'periodo1' => $ingreso_periodo1,
        'periodo2' => $ingreso_periodo2
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
