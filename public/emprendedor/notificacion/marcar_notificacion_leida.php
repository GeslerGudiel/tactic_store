<?php
session_start();
include_once '../../../src/config/database.php';

if (!isset($_POST['id_notificacion'])) {
    echo json_encode(['success' => false]);
    exit;
}

$database = new Database();
$db = $database->getConnection();

$idNotificacion = $_POST['id_notificacion'];

$query = "UPDATE notificacion SET leido = 1 WHERE id_notificacion = :id_notificacion";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_notificacion', $idNotificacion);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
