<?php
session_start();
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$id_pedido = isset($_POST['id_pedido']) ? $_POST['id_pedido'] : '';
$id_emprendedor = $_SESSION['id_emprendedor'];

// Verificar si ya existe una factura para este pedido
$query = "SELECT factura_emprendedor FROM detalle_pedido WHERE id_pedido = :id_pedido AND id_emprendedor = :id_emprendedor LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_pedido', $id_pedido);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->execute();
$detalle = $stmt->fetch(PDO::FETCH_ASSOC);

if ($detalle && !empty($detalle['factura_emprendedor'])) {
    $_SESSION['message'] = [
        'type' => 'warning',
        'text' => 'Ya has subido una factura para este pedido. No es posible volver a subir otra factura.'
    ];
    header("Location: dashboard.php?page=ver_pedidos");
    exit;
}

if ($_FILES['factura']['error'] === UPLOAD_ERR_OK) {
    $extension = pathinfo($_FILES['factura']['name'], PATHINFO_EXTENSION);
    $nombre_archivo = 'factura_' . $id_pedido . '_' . $id_emprendedor . '.' . $extension;
    $ruta_destino = '../../uploads/facturas_emprendedores/' . $nombre_archivo;

    if (move_uploaded_file($_FILES['factura']['tmp_name'], $ruta_destino)) {
        try {
            // Inicia una transacción
            $db->beginTransaction();

            // Actualiza la factura del emprendedor y el estado del pedido
            $query = "
                UPDATE detalle_pedido dp
                JOIN pedido p ON dp.id_pedido = p.id_pedido
                SET dp.factura_emprendedor = :factura_emprendedor, p.estado_pedido = 'entregado a centro de empaquetado'
                WHERE dp.id_pedido = :id_pedido AND dp.id_emprendedor = :id_emprendedor";
                
            $stmt = $db->prepare($query);
            $stmt->bindParam(':factura_emprendedor', $nombre_archivo);
            $stmt->bindParam(':id_pedido', $id_pedido);
            $stmt->bindParam(':id_emprendedor', $id_emprendedor);
            $stmt->execute();

            // Confirmar la transacción
            $db->commit();

            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Factura subida exitosamente y el pedido ha sido actualizado a "entregado a centro de empaquetado".'
            ];
        } catch (Exception $e) {
            $db->rollBack();
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Error al subir la factura: ' . $e->getMessage()
            ];
        }
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Error al subir la factura. Inténtalo de nuevo.'
        ];
    }
} else {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Error al cargar el archivo. Asegúrate de seleccionar un archivo válido.'
    ];
}

header("Location: dashboard.php?page=ver_pedidos");
exit;
?>
