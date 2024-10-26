<?php
session_start();
include_once '../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$id_pedido = $_POST['id_pedido'];
$id_emprendedor = $_SESSION['id_emprendedor'] ?? null;

if ($_FILES['factura']['error'] === UPLOAD_ERR_OK) {
    $extension = pathinfo($_FILES['factura']['name'], PATHINFO_EXTENSION);
    $nombre_archivo = 'factura_' . $id_pedido . '_' . $id_emprendedor . '.' . $extension;
    $ruta_destino = "../../../uploads/facturas_emprendedores/" . $nombre_archivo;

    if (move_uploaded_file($_FILES['factura']['tmp_name'], $ruta_destino)) {
        try {
            // Inicia la transacción
            $db->beginTransaction();

            // Actualiza la factura para todos los productos del emprendedor en el pedido
            $query = "
                UPDATE detalle_pedido 
                SET factura_emprendedor = :factura
                WHERE id_pedido = :id_pedido AND id_emprendedor = :id_emprendedor";

            $stmt = $db->prepare($query);
            $stmt->bindParam(':factura', $nombre_archivo);
            $stmt->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);
            $stmt->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);

            if ($stmt->execute()) {
                // Actualiza el estado del pedido
                $queryPedido = "
                    UPDATE pedido 
                    SET estado_pedido = 'Recolectado en centro de empaquetado.'
                    WHERE id_pedido = :id_pedido";

                $stmtPedido = $db->prepare($queryPedido);
                $stmtPedido->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);

                if ($stmtPedido->execute()) {
                    // Obtener información del cliente y productos
                    $queryCliente = "
                        SELECT c.correo, c.nombre1, c.apellido1, p.estado_pedido 
                        FROM pedido p 
                        JOIN cliente c ON p.id_cliente = c.id_cliente 
                        WHERE p.id_pedido = :id_pedido";

                    $stmtCliente = $db->prepare($queryCliente);
                    $stmtCliente->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);
                    $stmtCliente->execute();
                    $cliente = $stmtCliente->fetch(PDO::FETCH_ASSOC);

                    // Obtener detalles de los productos del pedido
                    $queryProductos = "
                        SELECT nombre_producto, cantidad, precio_unitario 
                        FROM detalle_pedido 
                        WHERE id_pedido = :id_pedido AND id_emprendedor = :id_emprendedor";

                    $stmtProductos = $db->prepare($queryProductos);
                    $stmtProductos->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);
                    $stmtProductos->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
                    $stmtProductos->execute();
                    $productos = $stmtProductos->fetchAll(PDO::FETCH_ASSOC);

                    // Construir la lista de productos para el correo
                    $listaProductos = "<ul>";
                    foreach ($productos as $producto) {
                        $listaProductos .= "<li>" . htmlspecialchars($producto['nombre_producto']) .
                            " - Cantidad: " . $producto['cantidad'] .
                            " - Precio Unitario: Q. " . number_format($producto['precio_unitario'], 2) . "</li>";
                    }
                    $listaProductos .= "</ul>";

                    // Configuración del correo
                    $to = $cliente['correo'];
                    $subject = "Actualización de tu Pedido #" . $id_pedido;
                    $headers = "From: tactic@store.com\r\n";
                    $headers .= "MIME-Version: 1.0\r\n";
                    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

                    // Contenido del correo
                    $message = "
                        <h1>Estimado " . htmlspecialchars($cliente['nombre1'] . " " . $cliente['apellido1']) . ",</h1>
                        <p>Te informamos que el estado de tu pedido #" . htmlspecialchars($id_pedido) . " ha cambiado.</p>
                        <p><strong>Estado actual:</strong> " . htmlspecialchars($cliente['estado_pedido']) . "</p>
                        <h3>Productos Recolectados:</h3>
                        $listaProductos
                        <p>Gracias por tu confianza en nuestra tienda.</p>
                        <p><a href='https://tactic-store.com/seguimiento_pedido.php?id_pedido=" . htmlspecialchars($id_pedido) . "'>Ver más detalles del pedido</a></p>";

                    // Enviar el correo
                    mail($to, $subject, $message, $headers);

                    $db->commit(); // Confirma la transacción
                    echo json_encode(['status' => 'success', 'message' => 'Factura subida y pedido actualizado.']);
                } else {
                    throw new Exception('No se pudo actualizar el estado del pedido.');
                }
            } else {
                throw new Exception('No se pudo actualizar los productos con la factura.');
            }
        } catch (Exception $e) {
            $db->rollBack(); // Revierte la transacción en caso de error
            echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al mover la factura al servidor.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Archivo inválido.']);
}
