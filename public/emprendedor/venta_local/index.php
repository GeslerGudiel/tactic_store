<?php
session_start();

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    header("Location: ../auth/login.php");
    exit;
}

include_once '../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

// Obtener lista de clientes
$query_clientes = "SELECT id_cliente_emprendedor, nombre_cliente FROM cliente_emprendedor WHERE id_emprendedor = :id_emprendedor";
$stmt_clientes = $db->prepare($query_clientes);
$stmt_clientes->bindParam(':id_emprendedor', $id_emprendedor);
$stmt_clientes->execute();
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

// Cargar la interfaz principal
include 'templates/gestionar_venta_local.php';
?>
