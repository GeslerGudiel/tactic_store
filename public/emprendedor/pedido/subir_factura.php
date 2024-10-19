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
                    SET estado_pedido = 'Entregado a centro de empaquetado'
                    WHERE id_pedido = :id_pedido";

                $stmtPedido = $db->prepare($queryPedido);
                $stmtPedido->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);

                if ($stmtPedido->execute()) {
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
?>
