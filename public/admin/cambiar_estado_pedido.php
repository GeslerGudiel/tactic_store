<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
    exit;
}

include_once '../../src/config/database.php';
include_once '../../src/config/funciones.php';
require('generar_factura.php');

$database = new Database();
$db = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitizar los datos recibidos
    $id_pedido = htmlspecialchars(strip_tags($_POST['id_pedido']));
    $estado_pedido = htmlspecialchars(strip_tags($_POST['estado_pedido']));
    $estado_pago = htmlspecialchars(strip_tags($_POST['estado_pago']));

    try {
        // Obtener detalles del pedido, cliente y emprendedor
        $query_detalles = "SELECT p.id_cliente, dp.id_emprendedor, c.correo AS correo_cliente, e.correo AS correo_emprendedor
                           FROM pedido p
                           JOIN cliente c ON p.id_cliente = c.id_cliente
                           JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
                           JOIN emprendedor e ON dp.id_emprendedor = e.id_emprendedor
                           WHERE p.id_pedido = :id_pedido
                           LIMIT 1";
        $stmt_detalles = $db->prepare($query_detalles);
        $stmt_detalles->bindParam(':id_pedido', $id_pedido);
        $stmt_detalles->execute();
        $detalles = $stmt_detalles->fetch(PDO::FETCH_ASSOC);

        // Actualizar el estado del pedido
        $query_pedido = "UPDATE pedido SET estado_pedido = :estado_pedido WHERE id_pedido = :id_pedido";
        $stmt_pedido = $db->prepare($query_pedido);
        $stmt_pedido->bindParam(':estado_pedido', $estado_pedido);
        $stmt_pedido->bindParam(':id_pedido', $id_pedido);
        $stmt_pedido->execute();

        // Generar la factura si el pedido ha sido entregado
        if ($estado_pedido == 'Entregado') {
            $query_pedido = "SELECT * FROM pedido WHERE id_pedido = :id_pedido";
            $stmt_pedido = $db->prepare($query_pedido);
            $stmt_pedido->bindParam(':id_pedido', $id_pedido);
            $stmt_pedido->execute();
            $pedido = $stmt_pedido->fetch(PDO::FETCH_ASSOC);

            $query_detalle_pedido = "SELECT * FROM detalle_pedido WHERE id_pedido = :id_pedido";
            $stmt_detalle_pedido = $db->prepare($query_detalle_pedido);
            $stmt_detalle_pedido->bindParam(':id_pedido', $id_pedido);
            $stmt_detalle_pedido->execute();
            $detalles_pedido = $stmt_detalle_pedido->fetchAll(PDO::FETCH_ASSOC);

            $total_factura = 0;
            foreach ($detalles_pedido as $detalle) {
                $total_factura += isset($detalle['subtotal']) ? $detalle['subtotal'] : $detalle['cantidad'] * $detalle['precio_unitario'];
            }

            $query_pago = "SELECT * FROM pago WHERE id_pedido = :id_pedido";
            $stmt_pago = $db->prepare($query_pago);
            $stmt_pago->bindParam(':id_pedido', $id_pedido);
            $stmt_pago->execute();
            $pago = $stmt_pago->fetch(PDO::FETCH_ASSOC);

            $ruta_factura = generarFacturaPDF($pedido, $detalles_pedido, $detalles, $detalles, $pago);

            $query_factura = "INSERT INTO factura (id_pedido, fecha_factura, total) VALUES (:id_pedido, NOW(), :total)";
            $stmt_factura = $db->prepare($query_factura);
            $stmt_factura->bindParam(':id_pedido', $id_pedido);
            $stmt_factura->bindParam(':total', $total_factura);
            $stmt_factura->execute();

            $id_factura = $db->lastInsertId();

            $query_update_pago = "UPDATE pago SET id_factura = :id_factura WHERE id_pedido = :id_pedido";
            $stmt_update_pago = $db->prepare($query_update_pago);
            $stmt_update_pago->bindParam(':id_factura', $id_factura);
            $stmt_update_pago->bindParam(':id_pedido', $id_pedido);
            $stmt_update_pago->execute();

            $titulo_factura = "Factura disponible para el pedido #" . $id_pedido;
            $mensaje_factura = "La factura de tu pedido está lista. Puedes descargarla aquí: " . $ruta_factura;
            agregarNotificacion($db, $detalles['id_cliente'], null, $titulo_factura, $mensaje_factura);
        }

        // Notificar al cliente sobre el cambio de estado del pedido
        $titulo_pedido = "Actualización de estado de tu pedido";
        $mensaje_pedido = "El estado de tu pedido #" . $id_pedido . " ha sido actualizado a: " . $estado_pedido;
        agregarNotificacion($db, $detalles['id_cliente'], null, $titulo_pedido, $mensaje_pedido);
        agregarNotificacion($db, null, $detalles['id_emprendedor'], $titulo_pedido, $mensaje_pedido);

        // Actualizar el estado del pago
        if (!empty($estado_pago)) {
            $query_pago = "UPDATE pago SET estado_pago = :estado_pago WHERE id_pedido = :id_pedido";
            $stmt_pago = $db->prepare($query_pago);
            $stmt_pago->bindParam(':estado_pago', $estado_pago);
            $stmt_pago->bindParam(':id_pedido', $id_pedido);
            $stmt_pago->execute();

            $titulo_pago_cliente = "Confirmación de Pago";
            $mensaje_pago_cliente = "El estado del pago para el pedido #" . $id_pedido . " es: " . $estado_pago;
            agregarNotificacion($db, $detalles['id_cliente'], null, $titulo_pago_cliente, $mensaje_pago_cliente);

            $titulo_pago_emprendedor = "Pago recibido para el pedido #" . $id_pedido;
            $mensaje_pago_emprendedor = "El estado del pago de tu pedido ha sido " . strtolower($estado_pago) . ".";
            agregarNotificacion($db, null, $detalles['id_emprendedor'], $titulo_pago_emprendedor, $mensaje_pago_emprendedor);
        }

        // Responder con éxito
        echo json_encode(['status' => 'success', 'message' => 'El estado del pedido y del pago se han actualizado correctamente.']);
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el estado: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
