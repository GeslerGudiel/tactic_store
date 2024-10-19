<?php
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

header('Content-Type: application/json');

if (isset($_GET['id_categoria'])) {
    $id_categoria = (int)$_GET['id_categoria'];

    // Consulta para obtener las subcategorías de la categoría seleccionada
    $query = "SELECT * FROM subcategoria WHERE id_categoria = :id_categoria";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_categoria', $id_categoria);
    $stmt->execute();
    $subcategorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($subcategorias) {
        // Devolver subcategorías en formato JSON
        http_response_code(200); // OK
        echo json_encode($subcategorias);
    } else {
        // Devolver array vacío si no hay subcategorías
        echo json_encode([]);
    }
} else {
    // Enviar error si no se proporciona el id_categoria
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'No se proporcionó un ID de categoría']);
}
