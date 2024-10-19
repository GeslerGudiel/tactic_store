<?php
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!isset($_SESSION['id_admin'])) {
    $_SESSION['message'] = [
        'type' => 'warning',
        'text' => 'Debes iniciar sesión como administrador para realizar esta acción.'
    ];
    header("Location: login_admin.php");
    exit;
}

// Seleccionar pedidos que están pendientes y cuya fecha límite ha pasado
$query = "SELECT id_pedido FROM pedido WHERE estado_pedido = 'Pendiente' AND metodo_pago = 'deposito_bancario' AND fecha_limite < NOW()";
$stmt = $db->prepare($query);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cancelados = 0;

foreach ($pedidos as $pedido) {
    $id_pedido = $pedido['id_pedido'];

    // Restaurar el stock de los productos en el pedido antes de cancelar
    $query_detalle = "SELECT id_producto, cantidad FROM detalle_pedido WHERE id_pedido = :id_pedido";
    $stmt_detalle = $db->prepare($query_detalle);
    $stmt_detalle->bindParam(':id_pedido', $id_pedido);
    $stmt_detalle->execute();
    $detalles = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);

    foreach ($detalles as $detalle) {
        $id_producto = $detalle['id_producto'];
        $cantidad = $detalle['cantidad'];

        // Incrementar el stock del producto
        $query_update_stock = "UPDATE producto SET stock = stock + :cantidad WHERE id_producto = :id_producto";
        $stmt_update_stock = $db->prepare($query_update_stock);
        $stmt_update_stock->bindParam(':cantidad', $cantidad);
        $stmt_update_stock->bindParam(':id_producto', $id_producto);
        $stmt_update_stock->execute();
    }

    // Cambiar el estado del pedido a "Cancelado"
    $query_update = "UPDATE pedido SET estado_pedido = 'Cancelado' WHERE id_pedido = :id_pedido";
    $stmt_update = $db->prepare($query_update);
    $stmt_update->bindParam(':id_pedido', $id_pedido);
    $stmt_update->execute();

    $cancelados++;

    // Falta: Notificar al cliente sobre la cancelación del pedido
    // Envair correo de notificación
    $query_cliente = "SELECT correo FROM cliente WHERE id_cliente = (SELECT id_cliente FROM pedido WHERE id_pedido = :id_pedido)";
    $stmt_cliente = $db->prepare($query_cliente);
    $stmt_cliente->bindParam(':id_pedido', $id_pedido);
    $stmt_cliente->execute();
    $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        $correo = $cliente['correo'];
        $asunto = "Pedido Cancelado - Tienda Virtual";
        $mensaje = "Estimado cliente,\n\nLamentamos informarle que su pedido con ID $id_pedido ha sido cancelado porque no recibimos el comprobante de depósito dentro del plazo de 24 horas.\n\nSi tiene alguna duda, por favor contáctenos.\n\nGracias.";
        $headers = "From: no-reply@tiendavirtual.com";

        mail($correo, $asunto, $mensaje, $headers);
    }
}

// Configurar un mensaje de éxito con el número de pedidos cancelados
$_SESSION['message'] = [
    'type' => 'success',
    'text' => "$cancelados pedidos han sido cancelados debido a que no se confirmó el pago dentro del plazo de 24 horas. El stock ha sido restablecido."
];

header("Location: admin_dashboard.php"); // Redirigir al dashboard
exit;