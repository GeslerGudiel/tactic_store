<?php
session_start();
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Verificar si el cliente está conectado
if (!isset($_SESSION['id_cliente'])) {
    $_SESSION['message'] = [
        'type' => 'warning',
        'text' => 'Debes iniciar sesión para subir el comprobante.'
    ];
    header("Location: login_cliente.php");
    exit;
}

$id_cliente = $_SESSION['id_cliente'];
$id_pedido = $_POST['id_pedido'];

if (isset($_FILES['imagen_comprobante']) && $_FILES['imagen_comprobante']['error'] === UPLOAD_ERR_OK) {
    $extension = pathinfo($_FILES['imagen_comprobante']['name'], PATHINFO_EXTENSION);
    $nombre_archivo = 'comprobante_' . $id_pedido . '.' . $extension;
    $ruta_destino = '../../uploads/comprobantes/' . $nombre_archivo;

    if (move_uploaded_file($_FILES['imagen_comprobante']['tmp_name'], $ruta_destino)) {
        $query_actualizar = "UPDATE pago SET imagen_comprobante = :imagen_comprobante WHERE id_pedido = :id_pedido";
        $stmt_actualizar = $db->prepare($query_actualizar);
        $stmt_actualizar->bindParam(':imagen_comprobante', $nombre_archivo);
        $stmt_actualizar->bindParam(':id_pedido', $id_pedido);

        if ($stmt_actualizar->execute()) {
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Comprobante subido con éxito.'
            ];
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Hubo un problema al actualizar el comprobante en la base de datos.'
            ];
        }
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Hubo un problema al subir el archivo.'
        ];
    }
} else {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Debes seleccionar un archivo para subir.'
    ];
}

header("Location: historial_pedidos.php?id_pedido=" . $id_pedido);
exit;
