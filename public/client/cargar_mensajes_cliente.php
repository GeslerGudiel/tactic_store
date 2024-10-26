<?php
session_start();
include_once '../../src/config/database.php';

// Verificar si el cliente está conectado
if (!isset($_SESSION['id_cliente'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit; 
}

$id_cliente = $_SESSION['id_cliente'];

$database = new Database();
$db = $database->getConnection();

// Obtener los mensajes del chat entre el cliente y el administrador
$query = "SELECT * FROM mensajes_chat 
          WHERE id_cliente = :id_cliente 
          ORDER BY fecha_envio ASC";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_cliente', $id_cliente);
$stmt->execute();
$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Marcar los mensajes no leídos como leídos (solo los que no envió el cliente)
$query_update = "UPDATE mensajes_chat 
                 SET leido = 1 
                 WHERE id_cliente = :id_cliente 
                 AND leido = 0 
                 AND enviado_por != 'cliente'";
$stmt_update = $db->prepare($query_update);
$stmt_update->bindParam(':id_cliente', $id_cliente);
$stmt_update->execute();

// Enviar los mensajes al frontend en formato JSON
echo json_encode($mensajes);
?>
