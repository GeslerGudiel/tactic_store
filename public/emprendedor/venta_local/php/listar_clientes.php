<?php
session_start();

if (!isset($_SESSION['id_emprendedor'])) {
    echo json_encode([]);
    exit;
}

include_once '../../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];
$filtro = isset($_GET['filtro']) ? '%' . $_GET['filtro'] . '%' : '';

// Consultar clientes del emprendedor que coincidan con el filtro
$query = "SELECT id_cliente_emprendedor, nombre_cliente, correo_cliente FROM cliente_emprendedor WHERE id_emprendedor = :id_emprendedor AND nombre_cliente LIKE :filtro";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->bindParam(':filtro', $filtro);
$stmt->execute();

$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($clientes);
