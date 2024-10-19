<?php
session_start();

if (!isset($_SESSION['id_emprendedor'])) {
    echo json_encode(['success' => false, 'message' => 'No se ha iniciado sesión.']);
    exit;
}

include_once '../../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

// Obtener datos del cliente del formulario
$nombre_cliente = isset($_POST['nombre_cliente']) ? htmlspecialchars(strip_tags($_POST['nombre_cliente'])) : '';
$correo_cliente = isset($_POST['correo_cliente']) ? htmlspecialchars(strip_tags($_POST['correo_cliente'])) : '';
$telefono_cliente = isset($_POST['telefono_cliente']) ? htmlspecialchars(strip_tags($_POST['telefono_cliente'])) : '';
$fecha_registro = date('Y-m-d');

// Validar que el nombre del cliente no esté vacío
if (empty($nombre_cliente)) {
    echo json_encode(['success' => false, 'message' => 'El nombre del cliente es obligatorio.']);
    exit;
}

// Insertar el nuevo cliente en la base de datos
$query = "INSERT INTO cliente_emprendedor (id_emprendedor, nombre_cliente, correo_cliente, telefono_cliente, fecha_registro) 
          VALUES (:id_emprendedor, :nombre_cliente, :correo_cliente, :telefono_cliente, :fecha_registro)";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->bindParam(':nombre_cliente', $nombre_cliente);
$stmt->bindParam(':correo_cliente', $correo_cliente);
$stmt->bindParam(':telefono_cliente', $telefono_cliente);
$stmt->bindParam(':fecha_registro', $fecha_registro);

if ($stmt->execute()) {
    $id_cliente_emprendedor = $db->lastInsertId();
    echo json_encode(['success' => true, 'id_cliente_emprendedor' => $id_cliente_emprendedor, 'nombre_cliente' => $nombre_cliente]);
} else {
    echo json_encode(['success' => false, 'message' => 'No se pudo agregar el cliente.']);
}
