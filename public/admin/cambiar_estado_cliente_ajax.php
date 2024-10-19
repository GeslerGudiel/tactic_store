<?php
session_start();
include_once '../../src/config/database.php';

// Verificar si el usuario tiene rol de administrador o superadmin
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    echo json_encode(['status' => 'error', 'message' => 'No tienes permiso para realizar esta acción.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Validar que el ID del cliente esté presente y sea un número válido
$id_cliente = isset($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : null;

if (!$id_cliente) {
    echo json_encode(['status' => 'error', 'message' => 'ID de cliente no especificado o inválido.']);
    exit;
}

try {
    // Obtener el estado actual del cliente
    $query = "SELECT id_estado_usuario FROM cliente WHERE id_cliente = :id_cliente";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt->execute();
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si el cliente existe
    if (!$cliente) {
        echo json_encode(['status' => 'error', 'message' => 'Cliente no encontrado.']);
        exit;
    }

    // Cambiar el estado del cliente (2 = activado, 4 = desactivado)
    $nuevo_estado = ($cliente['id_estado_usuario'] == 2) ? 4 : 2;
    $query_update = "UPDATE cliente SET id_estado_usuario = :nuevo_estado WHERE id_cliente = :id_cliente";
    $stmt_update = $db->prepare($query_update);
    $stmt_update->bindParam(':nuevo_estado', $nuevo_estado, PDO::PARAM_INT);
    $stmt_update->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
    $stmt_update->execute();

    echo json_encode(['status' => 'success', 'message' => 'Estado del cliente actualizado correctamente.']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el estado del cliente: ' . $e->getMessage()]);
}
?>
