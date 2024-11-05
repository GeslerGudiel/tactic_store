<?php
session_start();
include_once '../../../../src/config/database.php';

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];
$id_cliente = $_GET['id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'GET' && $id_cliente) {
    // Obtener los datos del cliente
    $query = "SELECT id_cliente_emprendedor AS id, nombre_cliente AS nombre, correo_cliente AS correo, telefono_cliente AS telefono, direccion_cliente AS direccion 
              FROM cliente_emprendedor 
              WHERE id_cliente_emprendedor = :id_cliente AND id_emprendedor = :id_emprendedor";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_cliente', $id_cliente);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor);
    $stmt->execute();
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cliente) {
        echo json_encode(['success' => true, 'cliente' => $cliente]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Cliente no encontrado']);
    }
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cliente = $_POST['id'];
    $nombre_cliente = $_POST['nombre_cliente'];
    $correo_cliente = $_POST['correo_cliente'];
    $telefono_cliente = $_POST['telefono_cliente'];
    $direccion_cliente = $_POST['direccion_cliente'];

    // Validar si ya existe otro cliente con el mismo nombre y/o teléfono
    $queryVerificar = "SELECT COUNT(*) FROM cliente_emprendedor 
                       WHERE id_emprendedor = :id_emprendedor 
                       AND nombre_cliente = :nombre_cliente 
                       OR telefono_cliente = :telefono_cliente 
                       AND id_cliente_emprendedor != :id_cliente";
    $stmtVerificar = $db->prepare($queryVerificar);
    $stmtVerificar->bindParam(':id_emprendedor', $id_emprendedor);
    $stmtVerificar->bindParam(':nombre_cliente', $nombre_cliente);
    $stmtVerificar->bindParam(':telefono_cliente', $telefono_cliente);
    $stmtVerificar->bindParam(':id_cliente', $id_cliente);
    $stmtVerificar->execute();

    $duplicado = $stmtVerificar->fetchColumn();

    if ($duplicado > 0) {
        echo json_encode(['success' => false, 'message' => 'Cliente duplicado']);
        exit;
    }

    // Actualizar el cliente si no hay duplicados
    $query = "UPDATE cliente_emprendedor 
              SET nombre_cliente = :nombre_cliente, correo_cliente = :correo_cliente, 
                  telefono_cliente = :telefono_cliente, direccion_cliente = :direccion_cliente 
              WHERE id_cliente_emprendedor = :id_cliente AND id_emprendedor = :id_emprendedor";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':nombre_cliente', $nombre_cliente);
    $stmt->bindParam(':correo_cliente', $correo_cliente);
    $stmt->bindParam(':telefono_cliente', $telefono_cliente);
    $stmt->bindParam(':direccion_cliente', $direccion_cliente);
    $stmt->bindParam(':id_cliente', $id_cliente);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cliente actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el cliente']);
    }
    exit;
}
