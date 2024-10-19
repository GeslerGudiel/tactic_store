<?php
session_start();

include_once '../../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$id_emprendedor = $_SESSION['id_emprendedor'];
$nombre_cliente = $_POST['nombre_cliente'];
$correo_cliente = $_POST['correo_cliente'];
$telefono_cliente = $_POST['telefono_cliente'];
$direccion_cliente = $_POST['direccion_cliente'];

$query = "INSERT INTO cliente_emprendedor (id_emprendedor, nombre_cliente, correo_cliente, telefono_cliente, direccion_cliente, fecha_registro) 
          VALUES (:id_emprendedor, :nombre_cliente, :correo_cliente, :telefono_cliente, :direccion_cliente, NOW())";

$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->bindParam(':nombre_cliente', $nombre_cliente);
$stmt->bindParam(':correo_cliente', $correo_cliente);
$stmt->bindParam(':telefono_cliente', $telefono_cliente);
$stmt->bindParam(':direccion_cliente', $direccion_cliente);

$response = [];
if ($stmt->execute()) {
    $response['success'] = true;
    $response['message'] = 'Cliente creado exitosamente.';
} else {
    $response['success'] = false;
    $response['message'] = 'Error al crear el cliente.';
}

echo json_encode($response);
