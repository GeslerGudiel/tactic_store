<?php
include_once '../../src/config/database.php';

function contarPedidosPorEstado($mes_seleccionado, $anio_actual) {
    $database = new Database();
    $db = $database->getConnection();

    // Calcular el primer y último día del mes seleccionado
    $fecha_inicio = "$anio_actual-$mes_seleccionado-01"; // Primer día del mes
    $fecha_fin = date("Y-m-t", strtotime($fecha_inicio)); // Último día del mes

    // Consulta para obtener el conteo de pedidos por estado
    $query_estados = "SELECT estado_pedido, COUNT(*) as total 
                      FROM pedido 
                      WHERE fecha_pedido BETWEEN :fecha_inicio AND :fecha_fin 
                      GROUP BY estado_pedido";

    $stmt_estados = $db->prepare($query_estados);
    $stmt_estados->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt_estados->bindParam(':fecha_fin', $fecha_fin);
    $stmt_estados->execute();
    
    return $stmt_estados->fetchAll(PDO::FETCH_ASSOC);
}
?>
