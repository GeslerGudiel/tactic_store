<?php
session_start();
include_once '../../src/config/database.php';

// Verificar si el usuario está autenticado y tiene el rol adecuado
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para realizar esta acción.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Validar el id_cliente recibido por POST
$id_cliente = isset($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : null;

if (!$id_cliente) {
    echo json_encode(['status' => 'error', 'message' => 'ID de cliente no especificado o inválido.']);
    exit;
}

try {
    // Eliminar el cliente
    $query = "DELETE FROM cliente WHERE id_cliente = :id_cliente";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'Cliente eliminado correctamente.']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al eliminar el cliente: ' . $e->getMessage()]);
}
?>
