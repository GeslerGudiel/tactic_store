<?php
session_start();
include_once '../../src/config/database.php';

// Verificar si el administrador está conectado
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}
$id_emprendedor = isset($_GET['id_emprendedor']) ? $_GET['id_emprendedor'] : null;
$id_administrador = isset($_SESSION['id_administrador']) ? $_SESSION['id_administrador'] : null;

if (!$id_emprendedor) {
    echo json_encode(['error' => 'ID de emprendedor no proporcionado']);
    exit;
}

$database = new Database(); 
$db = $database->getConnection();

// Obtener los mensajes del chat
$query = "SELECT * FROM mensajes_chat 
          WHERE id_emprendedor = :id_emprendedor 
          ORDER BY fecha_envio ASC";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->execute();
$mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Marcar los mensajes no leídos como leídos (solo los que no envió el administrador)
$query_update = "UPDATE mensajes_chat 
                 SET leido = 1 
                 WHERE id_emprendedor = :id_emprendedor 
                 AND leido = 0 
                 AND enviado_por != 'administrador'";
$stmt_update = $db->prepare($query_update);
$stmt_update->bindParam(':id_emprendedor', $id_emprendedor);
$stmt_update->execute();

// Enviar los mensajes al frontend en formato JSON
echo json_encode($mensajes);
?>
