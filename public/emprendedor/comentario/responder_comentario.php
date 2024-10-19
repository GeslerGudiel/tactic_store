<?php
session_start();
include_once '../../../src/config/database.php';

if (!isset($_SESSION['id_emprendedor'])) {
    echo json_encode(['status' => 'error', 'message' => 'No tienes acceso a esta sección.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_comentario']) && isset($_POST['respuesta'])) {
    $id_comentario = htmlspecialchars(strip_tags($_POST['id_comentario']));
    $respuesta = htmlspecialchars(strip_tags($_POST['respuesta']));

    $query = "UPDATE comentario SET respuesta = :respuesta WHERE id_comentario = :id_comentario";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':respuesta', $respuesta);
    $stmt->bindParam(':id_comentario', $id_comentario);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Respuesta enviada correctamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Hubo un error al enviar la respuesta.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Solicitud inválida.']);
}
?>
