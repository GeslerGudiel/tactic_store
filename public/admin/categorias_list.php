<?php
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM categoria";
$stmt = $db->prepare($query);
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($categorias);
?>
