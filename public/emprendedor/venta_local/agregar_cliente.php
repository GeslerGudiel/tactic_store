<?php
session_start();
include_once '../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$id_emprendedor = $_SESSION['id_emprendedor'] ?? null;
$nombre_cliente = $_POST['nombre_cliente'] ?? '';
$correo_cliente = $_POST['correo_cliente'] ?? '';
$telefono_cliente = $_POST['telefono_cliente'] ?? '';
$direccion_cliente = $_POST['direccion_cliente'] ?? '';
$fecha_registro = date('Y-m-d H:i:s');

// Verificación de los campos requeridos estén completos
if (empty($id_emprendedor) || empty($nombre_cliente) || empty($telefono_cliente)) {
    echo json_encode(['success' => false, 'message' => 'Campos obligatorios faltantes']);
    exit;
}

// Verificación de cliente si ya existe en la base de datos
$queryCheck = "SELECT id_cliente_emprendedor FROM cliente_emprendedor WHERE nombre_cliente = :nombre_cliente OR telefono_cliente = :telefono_cliente AND id_emprendedor = :id_emprendedor";
$stmtCheck = $db->prepare($queryCheck);
$stmtCheck->bindParam(':nombre_cliente', $nombre_cliente);
$stmtCheck->bindParam(':telefono_cliente', $telefono_cliente);
$stmtCheck->bindParam(':id_emprendedor', $id_emprendedor);
$stmtCheck->execute();

if ($stmtCheck->rowCount() > 0) {
    // Cliente ya existe
    echo json_encode(['success' => false, 'message' => 'El cliente ya ha sido registrado anteriormente.']);
    exit;
}

// Insertar el nuevo cliente si no existe
$queryInsert = "INSERT INTO cliente_emprendedor (id_emprendedor, nombre_cliente, correo_cliente, telefono_cliente, direccion_cliente, fecha_registro)
                VALUES (:id_emprendedor, :nombre_cliente, :correo_cliente, :telefono_cliente, :direccion_cliente, :fecha_registro)";
$stmtInsert = $db->prepare($queryInsert);
$stmtInsert->bindParam(':id_emprendedor', $id_emprendedor);
$stmtInsert->bindParam(':nombre_cliente', $nombre_cliente);
$stmtInsert->bindParam(':correo_cliente', $correo_cliente);
$stmtInsert->bindParam(':telefono_cliente', $telefono_cliente);
$stmtInsert->bindParam(':direccion_cliente', $direccion_cliente);
$stmtInsert->bindParam(':fecha_registro', $fecha_registro);

if ($stmtInsert->execute()) {
    echo json_encode(['success' => true, 'id_cliente' => $db->lastInsertId(), 'message' => 'Cliente registrado exitosamente']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error al agregar el cliente']);
}
?>
