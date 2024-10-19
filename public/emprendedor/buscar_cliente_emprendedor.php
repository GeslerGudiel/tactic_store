<?php
include_once '../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$query = $_GET['query'];

$stmt = $db->prepare("SELECT id_cliente_emprendedor, nombre_cliente FROM cliente_emprendedor WHERE nombre_cliente LIKE :query AND id_emprendedor = :id_emprendedor");
$stmt->bindValue(':query', '%' . $query . '%');
$stmt->bindParam(':id_emprendedor', $_SESSION['id_emprendedor']);
$stmt->execute();

$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($clientes);
?>
