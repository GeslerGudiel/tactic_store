<?php
session_start();
include_once '../../../../src/config/database.php';

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_emprendedor'])) {
    echo json_encode(['error' => 'No se ha iniciado sesión correctamente.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

if (isset($_POST['id_cliente_emprendedor'])) {
    $id_cliente_emprendedor = $_POST['id_cliente_emprendedor'];

    try {
        $query = "DELETE FROM cliente_emprendedor WHERE id_cliente_emprendedor = :id_cliente_emprendedor";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_cliente_emprendedor', $id_cliente_emprendedor, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Cliente eliminado correctamente.']);
        } else {
            echo json_encode(['error' => 'No se pudo eliminar el cliente.']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'No se recibió el ID del cliente.']);
}
