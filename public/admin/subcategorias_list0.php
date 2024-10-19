<?php
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

if (isset($_GET['id_categoria'])) {
    $id_categoria = (int)$_GET['id_categoria'];

    // Consulta para obtener las subcategorías de la categoría seleccionada
    $query = "SELECT * FROM subcategoria WHERE id_categoria = :id_categoria ORDER BY nombre_subcategoria ASC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_categoria', $id_categoria);
    $stmt->execute();
    $subcategorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Devolver las subcategorías en formato JSON
    header('Content-Type: application/json');
    echo json_encode($subcategorias);
} else {
    // Error si no se proporciona el id_categoria
    echo json_encode(['status' => 'error', 'message' => 'No se proporcionó un ID de categoría']);
}
