<?php
session_start();
include_once '../../src/config/database.php';

// Verificar si el administrador está conectado
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$id_cliente = isset($_GET['id_cliente']) ? $_GET['id_cliente'] : null;

if (!$id_cliente) {
    echo json_encode(['error' => 'ID de cliente no proporcionado']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Obtener los mensajes del chat
$query = "SELECT * FROM mensajes_chat 
          WHERE id_cliente = :id_cliente 
          ORDER BY fecha_envio ASC";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_cliente', $id_cliente);
$stmt->execute();
$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Enviar los mensajes al frontend en formato JSON
echo json_encode($mensajes);
?>
