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

function enviarCorreo($destinatario, $asunto, $mensaje) {
    $headers = "From: tactic@store.com\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    mail($destinatario, $asunto, $mensaje, $headers);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pedido = htmlspecialchars(strip_tags($_POST['id_pedido']));
    $estado_pedido = htmlspecialchars(strip_tags($_POST['estado_pedido']));
    $estado_pago = htmlspecialchars(strip_tags($_POST['estado_pago']));

    try {
        $db->beginTransaction();

        // Obtener estados actuales del pedido y pago
        $query_estado_actual = "SELECT estado_pedido, pa.estado_pago 
                                FROM pedido p 
                                JOIN pago pa ON p.id_pedido = pa.id_pedido 
                                WHERE p.id_pedido = :id_pedido";
        $stmt_estado_actual = $db->prepare($query_estado_actual);
        $stmt_estado_actual->bindParam(':id_pedido', $id_pedido);
        $stmt_estado_actual->execute();
        $estado_actual = $stmt_estado_actual->fetch(PDO::FETCH_ASSOC);

        $estado_pedido_actual = $estado_actual['estado_pedido'];
        $estado_pago_actual = $estado_actual['estado_pago'];

        // Obtener detalles del pedido
        $query_detalles = "
            SELECT c.id_cliente, c.correo AS correo_cliente, c.nombre1, c.apellido1,
                   e.id_emprendedor, e.correo AS correo_emprendedor 
            FROM pedido p
            JOIN cliente c ON p.id_cliente = c.id_cliente
            JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
            JOIN emprendedor e ON dp.id_emprendedor = e.id_emprendedor
            WHERE p.id_pedido = :id_pedido LIMIT 1";
        $stmt_detalles = $db->prepare($query_detalles);
        $stmt_detalles->bindParam(':id_pedido', $id_pedido);
        $stmt_detalles->execute();
        $detalles = $stmt_detalles->fetch(PDO::FETCH_ASSOC);

        // Obtener productos del pedido
        $query_productos = "SELECT nombre_producto, cantidad, precio_unitario 
                            FROM detalle_pedido 
                            WHERE id_pedido = :id_pedido";
        $stmt_productos = $db->prepare($query_productos);
        $stmt_productos->bindParam(':id_pedido', $id_pedido);
        $stmt_productos->execute();
        $productos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

        $listaProductos = "<ul>";
        foreach ($productos as $producto) {
            $listaProductos .= "<li>" . htmlspecialchars($producto['nombre_producto']) .
                " - Cantidad: " . $producto['cantidad'] .
                " - Precio Unitario: Q. " . number_format($producto['precio_unitario'], 2) . "</li>";
        }
        $listaProductos .= "</ul>";

        // Actualizar estado del pedido
        if ($estado_pedido !== $estado_pedido_actual) {
            $query_pedido = "UPDATE pedido SET estado_pedido = :estado_pedido WHERE id_pedido = :id_pedido";
            $stmt_pedido = $db->prepare($query_pedido);
            $stmt_pedido->bindParam(':estado_pedido', $estado_pedido);
            $stmt_pedido->bindParam(':id_pedido', $id_pedido);
            $stmt_pedido->execute();

            $mensaje_cliente = "
                <h1>Estado de Pedido Actualizado</h1>
                <p>El pedido #" . $id_pedido . " ha cambiado a: <strong>" . $estado_pedido . "</strong></p>
                <h3>Detalles del Pedido:</h3>
                $listaProductos";
            enviarCorreo($detalles['correo_cliente'], "Actualización de estado de tu pedido", $mensaje_cliente);

            $mensaje_emprendedor = "El estado del pedido #" . $id_pedido . " ha sido actualizado a: " . $estado_pedido;
            agregarNotificacion($db, null, $detalles['id_emprendedor'], "Actualización de estado de pedido", strip_tags($mensaje_emprendedor));
        }

        // Generar factura si el pedido fue entregado
        if ($estado_pedido == 'Entregado') {
            $total_factura = array_reduce($productos, function ($total, $producto) {
                return $total + ($producto['cantidad'] * $producto['precio_unitario']);
            }, 0);

            $ruta_factura = generarFacturaPDF([], $productos, $detalles, [], []);

            $query_factura = "INSERT INTO factura (id_pedido, fecha_factura, total) 
                              VALUES (:id_pedido, NOW(), :total)";
            $stmt_factura = $db->prepare($query_factura);
            $stmt_factura->bindParam(':id_pedido', $id_pedido);
            $stmt_factura->bindParam(':total', $total_factura);
            $stmt_factura->execute();
        }

        // Actualizar estado del pago
        if ($estado_pago !== $estado_pago_actual) {
            $query_pago = "UPDATE pago SET estado_pago = :estado_pago WHERE id_pedido = :id_pedido";
            $stmt_pago = $db->prepare($query_pago);
            $stmt_pago->bindParam(':estado_pago', $estado_pago);
            $stmt_pago->bindParam(':id_pedido', $id_pedido);
            $stmt_pago->execute();

            $mensaje_pago_cliente = "
                <h1>Estado de Pago Actualizado</h1>
                <p>El estado del pago de tu pedido #" . $id_pedido . " es: <strong>" . $estado_pago . "</strong></p>";
            enviarCorreo($detalles['correo_cliente'], "Estado de Pago Actualizado", $mensaje_pago_cliente);

            $mensaje_pago_emprendedor = "El estado del pago para el pedido #" . $id_pedido . " es: " . $estado_pago;
            agregarNotificacion($db, null, $detalles['id_emprendedor'], "Estado de Pago Actualizado", strip_tags($mensaje_pago_emprendedor));
        }

        $db->commit();
        echo json_encode(['status' => 'success', 'message' => 'El estado del pedido y/o pago se ha actualizado correctamente.']);
    } catch (Exception $e) {
        $db->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
