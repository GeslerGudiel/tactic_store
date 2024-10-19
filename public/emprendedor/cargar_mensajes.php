<?php
session_start();
include_once '../../src/config/database.php';

// Verificar si el emprendedor está conectado
if (!isset($_SESSION['id_emprendedor'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

$id_emprendedor = $_SESSION['id_emprendedor'];

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

// Recorrer los mensajes para agregar la URL de la imagen si existe
foreach ($mensajes as &$mensaje) {
    if ($mensaje['imagen']) {
        $mensaje['imagen_url'] = '../../uploads/chat_imagenes/' . $mensaje['imagen']; // URL de la imagen
    }
}

// Marcar los mensajes no leídos como leídos (solo los que no envió el emprendedor)
$query_update = "UPDATE mensajes_chat 
                 SET leido = 1 
                 WHERE id_emprendedor = :id_emprendedor 
                 AND leido = 0 
                 AND enviado_por != 'emprendedor'";
$stmt_update = $db->prepare($query_update);
$stmt_update->bindParam(':id_emprendedor', $id_emprendedor);
$stmt_update->execute();

// Enviar los mensajes al frontend en formato JSON
echo json_encode($mensajes);
?>
