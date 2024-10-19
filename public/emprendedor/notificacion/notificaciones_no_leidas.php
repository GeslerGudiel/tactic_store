<?php
session_start();
include_once '../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

$query = "SELECT COUNT(*) as no_leidas FROM notificacion WHERE id_emprendedor = :id_emprendedor AND leido = 0";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode(['no_leidas' => $result['no_leidas']]);
?>
