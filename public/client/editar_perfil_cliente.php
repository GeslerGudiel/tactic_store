<?php
session_start();
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Verificar si el cliente está conectado
if (!isset($_SESSION['id_cliente'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Debes iniciar sesión para editar tu perfil.'
    ]);
    exit;
}

$id_cliente = $_SESSION['id_cliente'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre1 = $_POST['nombre1'];
    $nombre2 = $_POST['nombre2'];
    $nombre3 = $_POST['nombre3'];
    $apellido1 = $_POST['apellido1'];
    $apellido2 = $_POST['apellido2'];
    $telefono1 = $_POST['telefono1'];
    $telefono2 = $_POST['telefono2'];

    // Validación de datos
    if (empty($nombre1) || empty($nombre2) || empty($apellido1) || empty($apellido2)|| empty($telefono1) || empty($telefono2)) {
        echo json_encode([
            'success' => false,
            'message' => 'Por favor, completa los campos obligatorios.'
        ]);
        exit;
    }

    // Actualizar datos del cliente
    $query = "UPDATE cliente 
              SET nombre1 = :nombre1, nombre2 = :nombre2, nombre3 = :nombre3,
                  apellido1 = :apellido1, apellido2 = :apellido2, 
                  telefono1 = :telefono1, telefono2 = :telefono2
              WHERE id_cliente = :id_cliente";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':nombre1', $nombre1);
    $stmt->bindParam(':nombre2', $nombre2);
    $stmt->bindParam(':nombre3', $nombre3);
    $stmt->bindParam(':apellido1', $apellido1);
    $stmt->bindParam(':apellido2', $apellido2);
    $stmt->bindParam(':telefono1', $telefono1);
    $stmt->bindParam(':telefono2', $telefono2);
    $stmt->bindParam(':id_cliente', $id_cliente);

    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Tu perfil ha sido actualizado correctamente.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Hubo un error al actualizar tu perfil. Intenta nuevamente.'
        ]);
    }
}
