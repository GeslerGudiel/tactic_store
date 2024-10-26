<?php
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$campo = isset($_GET['NIT']) ? 'NIT' : 'correo';
$valor = $_GET[$campo];

$query = "SELECT id_cliente FROM cliente WHERE $campo = :valor";
$stmt = $db->prepare($query);
$stmt->bindParam(':valor', $valor);
$stmt->execute();

echo json_encode(['exists' => $stmt->rowCount() > 0]);
?>
