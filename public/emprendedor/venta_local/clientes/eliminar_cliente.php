<?php
session_start();
include_once '../../../../src/config/database.php';

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];
$id_cliente = $_POST['id'] ?? null;

if (!$id_cliente) {
    echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
    exit;
}

try {
    // Eliminar el cliente
    $query = "DELETE FROM cliente_emprendedor WHERE id_cliente_emprendedor = :id_cliente AND id_emprendedor = :id_emprendedor";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_cliente', $id_cliente);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cliente eliminado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se pudo eliminar el cliente']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al eliminar el cliente: ' . $e->getMessage()]);
}
