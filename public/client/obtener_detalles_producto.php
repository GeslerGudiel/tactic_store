<?php
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

if (isset($_GET['id_producto'])) {
    $id_producto = intval($_GET['id_producto']);
    
    // Obtener los detalles del producto
    $query_producto = "SELECT id_producto, nombre_producto, descripcion, precio, imagen, stock FROM producto WHERE id_producto = :id_producto";
    $stmt_producto = $db->prepare($query_producto);
    $stmt_producto->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
    $stmt_producto->execute();
    $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        // Obtener los comentarios del producto
        $query_comentarios = "SELECT c.comentario, c.calificacion, c.fecha_comentario, cl.nombre1, cl.apellido1, c.respuesta 
                              FROM comentario c 
                              INNER JOIN cliente cl ON c.id_cliente = cl.id_cliente 
                              WHERE c.id_producto = :id_producto 
                              ORDER BY c.fecha_comentario DESC";
        $stmt_comentarios = $db->prepare($query_comentarios);
        $stmt_comentarios->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        $stmt_comentarios->execute();
        $comentarios = $stmt_comentarios->fetchAll(PDO::FETCH_ASSOC);
        
        $producto['comentarios'] = $comentarios;
        
        echo json_encode($producto);
    } else {
        echo json_encode(['error' => 'Producto no encontrado']);
    }
} else {
    echo json_encode(['error' => 'ID de producto no especificado']);
}
?>
