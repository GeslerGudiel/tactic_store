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

// Capturar los parámetros de filtro (fechas y número de pedido)
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';
$numero_pedido = $_GET['numero_pedido'] ?? '';

// **Consulta SQL base** para obtener las comisiones y total pagado por productos del emprendedor
$query = "
    SELECT 
        p.id_pedido, 
        SUM(c.monto_comision) AS total_comision, 
        p.fecha_pedido, 
        MAX(c.estado_comision) AS estado_comision, 
        MAX(c.fecha_pago) AS fecha_pago, 
        MAX(c.comprobante_pago) AS comprobante_pago,
        (
            SELECT SUM(dp.subtotal) 
            FROM detalle_pedido dp 
            WHERE dp.id_pedido = p.id_pedido 
              AND dp.id_emprendedor = :id_emprendedor
        ) AS total_productos_vendidos
    FROM comision c
    INNER JOIN pedido p ON c.id_pedido = p.id_pedido
    WHERE c.id_emprendedor = :id_emprendedor";

// Agregar filtro por número de pedido si se proporciona
if (!empty($numero_pedido)) {
    $query .= " AND p.id_pedido LIKE :numero_pedido";
}

// Agregar filtro por fechas si se proporcionan
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $query .= " AND p.fecha_pedido BETWEEN :fecha_inicio AND :fecha_fin";
} elseif (!empty($fecha_inicio)) {
    $query .= " AND p.fecha_pedido >= :fecha_inicio";
} elseif (!empty($fecha_fin)) {
    $query .= " AND p.fecha_pedido <= :fecha_fin";
}

$query .= " GROUP BY p.id_pedido ORDER BY p.fecha_pedido DESC";

// preparar y ejecutar la consulta
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);

if (!empty($numero_pedido)) {
    $numero_pedido_param = "%$numero_pedido%";
    $stmt->bindParam(':numero_pedido', $numero_pedido_param);
}

if (!empty($fecha_inicio)) {
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
}

if (!empty($fecha_fin)) {
    $stmt->bindParam(':fecha_fin', $fecha_fin);
}

$stmt->execute();
$comisiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

//Devolver la respuesta en formato JSON
if ($comisiones) {
    echo json_encode($comisiones);
} else {
    echo json_encode(['error' => 'No se encontraron resultados para los filtros aplicados.']);
}
exit;
?>
