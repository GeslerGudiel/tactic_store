<?php
function procesarPago($db, $id_pedido, $metodo_pago) {
    $estado_pago = ($metodo_pago === 'deposito_bancario') ? 'Pendiente' : 'Completado';
    $monto = 0;

    // Calcular el monto total del pedido
    $query_total = "SELECT SUM(subtotal) AS total FROM detalle_pedido WHERE id_pedido = :id_pedido";
    $stmt_total = $db->prepare($query_total);
    $stmt_total->bindParam(':id_pedido', $id_pedido);
    $stmt_total->execute();
    $resultado_total = $stmt_total->fetch(PDO::FETCH_ASSOC);
    $monto = $resultado_total['total'];

    // Insertar el pago en la tabla `pago`
    $query_pago = "INSERT INTO pago (id_pedido, metodo_pago, monto, fecha_pago, estado_pago)
                   VALUES (:id_pedido, :metodo_pago, :monto, NOW(), :estado_pago)";
    $stmt_pago = $db->prepare($query_pago);
    $stmt_pago->bindParam(':id_pedido', $id_pedido);
    $stmt_pago->bindParam(':metodo_pago', $metodo_pago);
    $stmt_pago->bindParam(':monto', $monto);
    $stmt_pago->bindParam(':estado_pago', $estado_pago);
    $stmt_pago->execute();

    $id_pago = $db->lastInsertId();

    // Manejo del archivo de comprobante para depósito bancario
    if ($metodo_pago === 'deposito_bancario') {
        if (isset($_FILES['imagen_comprobante']) && $_FILES['imagen_comprobante']['error'] === UPLOAD_ERR_OK) {
            $extension = pathinfo($_FILES['imagen_comprobante']['name'], PATHINFO_EXTENSION);
            $nombre_archivo = 'comprobante_' . $id_pago . '.' . $extension;
            $ruta_destino = '../../uploads/comprobantes/' . $nombre_archivo;

            if (move_uploaded_file($_FILES['imagen_comprobante']['tmp_name'], $ruta_destino)) {
                $query_actualizar_pago = "UPDATE pago SET comprobante = :comprobante WHERE id_pago = :id_pago";
                $stmt_actualizar_pago = $db->prepare($query_actualizar_pago);
                $stmt_actualizar_pago->bindParam(':comprobante', $nombre_archivo);
                $stmt_actualizar_pago->bindParam(':id_pago', $id_pago);
                $stmt_actualizar_pago->execute();
            } else {
                throw new Exception("Error al subir el comprobante de depósito.");
            }
        } else {
            throw new Exception("El comprobante es obligatorio para el método de depósito bancario.");
        }
    }
}
