<?php
session_start();
include_once '../../src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_emprendedor = $_SESSION['id_emprendedor'] ?? null;
    
    if ($id_emprendedor) {
        $database = new Database();
        $db = $database->getConnection();

        // Marcar todos los mensajes enviados por el administrador como leídos
        $query = "UPDATE mensajes_chat 
                  SET leido = 1 
                  WHERE id_emprendedor = :id_emprendedor 
                  AND leido = 0 
                  AND enviado_por = 'admin'";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_emprendedor', $id_emprendedor);
        $stmt->execute();
        
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Emprendedor no encontrado']);
    }
}
?>
