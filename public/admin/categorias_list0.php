<?php
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Consultar categorías
$query = "SELECT * FROM categoria";
$stmt = $db->prepare($query);
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Devolver las categorías en formato JSON
header('Content-Type: application/json');
echo json_encode($categorias);
