<?php
session_start();
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$id_emprendedor = $_SESSION['id_emprendedor'];

$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'nombre_producto';
$order_direction = isset($_GET['order_direction']) && $_GET['order_direction'] == 'desc' ? 'desc' : 'asc';

$query = "SELECT id_producto, nombre_producto, stock, precio, costo, (precio - costo) AS ganancia_unitaria
          FROM producto 
          WHERE id_emprendedor = :id_emprendedor
          ORDER BY $order_by $order_direction";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($productos);
