<?php
session_start();
if (!isset($_SESSION['id_emprendedor'])) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
    exit;
}

include_once '../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Consulta para obtener las categorías
$query = "SELECT id_categoria, nombre_categoria FROM categoria";
$stmt = $db->prepare($query);
$stmt->execute();

$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retornar las categorías en formato JSON
echo json_encode($categorias);
?>
