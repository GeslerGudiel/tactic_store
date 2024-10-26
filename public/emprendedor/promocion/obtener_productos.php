<?php
session_start();

// Verificar si el emprendedor está autenticado y tiene el rol adecuado
if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    echo json_encode(['success' => false, 'message' => 'Acceso no autorizado']);
    exit;
}

include_once '../../../src/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $id_emprendedor = $_SESSION['id_emprendedor'];

    // Consulta para obtener los productos del emprendedor
    $query = "
        SELECT id_producto, nombre_producto, precio 
        FROM producto 
        WHERE id_emprendedor = :id_emprendedor 
        AND stock > 0 
        AND estado = 'Disponible'
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
    $stmt->execute();

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Verificar si se encontraron productos
    if ($productos) {
        echo json_encode(['success' => true, 'productos' => $productos]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No se encontraron productos disponibles']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error en la base de datos: ' . $e->getMessage()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error al obtener productos: ' . $e->getMessage()]);
}
?>
