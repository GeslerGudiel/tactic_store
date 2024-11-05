<?php
session_start();

/*ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

include_once '../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';

$query = "SELECT id_producto, nombre_producto, descripcion, precio, stock, imagen 
          FROM producto 
          WHERE id_emprendedor = :id_emprendedor AND stock > 0 
          AND nombre_producto LIKE :filtro";

$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->bindValue(':filtro', "%$filtro%");
$stmt->execute();

$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

header('Content-Type: application/json');
echo json_encode($productos);
