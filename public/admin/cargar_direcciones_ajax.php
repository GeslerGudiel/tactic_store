<?php
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Obtener las direcciones
    $query_direccion = "SELECT * FROM direccion";
    $stmt = $db->prepare($query_direccion);
    $stmt->execute();
    $direcciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($direcciones);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al cargar direcciones: ' . $e->getMessage()]);
}
?>
