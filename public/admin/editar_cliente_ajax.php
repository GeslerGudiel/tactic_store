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

// Validar los datos recibidos
$id_cliente = isset($_POST['id_cliente']) ? (int)$_POST['id_cliente'] : null;
$NIT = isset($_POST['NIT']) ? htmlspecialchars(strip_tags($_POST['NIT'])) : null;
$nombre1 = isset($_POST['nombre1']) ? htmlspecialchars(strip_tags($_POST['nombre1'])) : null;
$nombre2 = isset($_POST['nombre2']) ? htmlspecialchars(strip_tags($_POST['nombre2'])) : '';
$nombre3 = isset($_POST['nombre3']) ? htmlspecialchars(strip_tags($_POST['nombre3'])) : '';
$apellido1 = isset($_POST['apellido1']) ? htmlspecialchars(strip_tags($_POST['apellido1'])) : null;
$apellido2 = isset($_POST['apellido2']) ? htmlspecialchars(strip_tags($_POST['apellido2'])) : null;
$telefono1 = isset($_POST['telefono1']) ? htmlspecialchars(strip_tags($_POST['telefono1'])) : null;
$telefono2 = isset($_POST['telefono2']) ? htmlspecialchars(strip_tags($_POST['telefono2'])) : '';
$correo = isset($_POST['correo']) ? htmlspecialchars(strip_tags($_POST['correo'])) : null;
$id_direccion = isset($_POST['id_direccion']) ? (int)$_POST['id_direccion'] : null;

// Validar que los campos requeridos no estén vacíos
if (!$id_cliente || !$NIT || !$nombre1 || !$apellido1 || !$apellido2 || !$telefono1 || !$correo || !$id_direccion) {
    echo json_encode(['status' => 'error', 'message' => 'Todos los campos requeridos deben ser completados.']);
    exit;
}

try {
    // Actualizar los datos del cliente en la base de datos
    $query = "UPDATE cliente 
              SET NIT = :NIT, nombre1 = :nombre1, nombre2 = :nombre2, nombre3 = :nombre3,
                  apellido1 = :apellido1, apellido2 = :apellido2, telefono1 = :telefono1, telefono2 = :telefono2,
                  correo = :correo, id_direccion = :id_direccion 
              WHERE id_cliente = :id_cliente";
    
    $stmt = $db->prepare($query);

    // Vincular los parámetros
    $stmt->bindParam(':NIT', $NIT);
    $stmt->bindParam(':nombre1', $nombre1);
    $stmt->bindParam(':nombre2', $nombre2);
    $stmt->bindParam(':nombre3', $nombre3);
    $stmt->bindParam(':apellido1', $apellido1);
    $stmt->bindParam(':apellido2', $apellido2);
    $stmt->bindParam(':telefono1', $telefono1);
    $stmt->bindParam(':telefono2', $telefono2);
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':id_direccion', $id_direccion, PDO::PARAM_INT);
    $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Cliente actualizado exitosamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el cliente.']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>
