<?php
session_start();
include_once '../../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$id_emprendedor = $_SESSION['id_emprendedor'] ?? null;
$nombre_cliente = $_POST['nombre_cliente'] ?? '';
$correo_cliente = $_POST['correo_cliente'] ?? '';
$telefono_cliente = $_POST['telefono_cliente'] ?? '';
$direccion_cliente = $_POST['direccion_cliente'] ?? '';
$fecha_registro = date('Y-m-d H:i:s');

if (empty($id_emprendedor) || empty($nombre_cliente) || empty($correo_cliente)) {
    echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
    exit;
}

// Verificar si ya existe un cliente con el mismo nombre y/o teléfono
$queryVerificar = "SELECT COUNT(*) FROM cliente_emprendedor 
                   WHERE id_emprendedor = :id_emprendedor 
                   AND nombre_cliente = :nombre_cliente 
                   OR telefono_cliente = :telefono_cliente";
$stmtVerificar = $db->prepare($queryVerificar);
$stmtVerificar->bindParam(':id_emprendedor', $id_emprendedor);
$stmtVerificar->bindParam(':nombre_cliente', $nombre_cliente);
$stmtVerificar->bindParam(':telefono_cliente', $telefono_cliente);
$stmtVerificar->execute();

if ($stmtVerificar->fetchColumn() > 0) {
    echo json_encode(['success' => false, 'message' => 'Cliente duplicado']);
    exit;
}

// Insertar nuevo cliente si no hay duplicados
$query = "INSERT INTO cliente_emprendedor (id_emprendedor, nombre_cliente, correo_cliente, telefono_cliente, direccion_cliente, fecha_registro)
          VALUES (:id_emprendedor, :nombre_cliente, :correo_cliente, :telefono_cliente, :direccion_cliente, :fecha_registro)";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->bindParam(':nombre_cliente', $nombre_cliente);
$stmt->bindParam(':correo_cliente', $correo_cliente);
$stmt->bindParam(':telefono_cliente', $telefono_cliente);
$stmt->bindParam(':direccion_cliente', $direccion_cliente);
$stmt->bindParam(':fecha_registro', $fecha_registro);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'id_cliente' => $db->lastInsertId()]);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al agregar el cliente']);
}
