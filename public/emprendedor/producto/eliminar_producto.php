<?php
session_start();
header('Content-Type: application/json'); 

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
    exit;
}

include_once '../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$data = json_decode(file_get_contents('php://input'), true);
$idProducto = $data['id_producto'] ?? null;

if (!$idProducto) {
    echo json_encode(['status' => 'error', 'message' => 'Producto no encontrado']);
    exit;
}

try {
    // Eliminar el producto
    $query = "DELETE FROM producto WHERE id_producto = :id_producto";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_producto', $idProducto, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Producto eliminado correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el producto']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Excepción: ' . $e->getMessage()]);
}
