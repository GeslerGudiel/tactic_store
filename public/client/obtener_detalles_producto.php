<?php
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

if (isset($_GET['id_producto'])) {
    $id_producto = intval($_GET['id_producto']);

    // Obtener los detalles del producto junto con la promoción activa (si la tiene)
    $query_producto = "
        SELECT p.id_producto, p.nombre_producto, p.descripcion, p.precio, p.imagen, p.stock, 
               pr.tipo_promocion, pr.precio_oferta, pr.porcentaje_descuento, pr.fecha_fin 
        FROM producto p
        LEFT JOIN promocion pr 
            ON p.id_producto = pr.id_producto 
            AND pr.estado = 'Activo' 
            AND pr.fecha_fin >= CURDATE()
        WHERE p.id_producto = :id_producto
    ";

    $stmt_producto = $db->prepare($query_producto);
    $stmt_producto->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
    $stmt_producto->execute();
    $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);

    if ($producto) {
        // Obtener los comentarios del producto
        $query_comentarios = "
            SELECT c.comentario, c.calificacion, c.fecha_comentario, cl.nombre1, cl.apellido1, c.respuesta 
            FROM comentario c 
            INNER JOIN cliente cl ON c.id_cliente = cl.id_cliente 
            WHERE c.id_producto = :id_producto 
            ORDER BY c.fecha_comentario DESC
        ";

        $stmt_comentarios = $db->prepare($query_comentarios);
        $stmt_comentarios->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        $stmt_comentarios->execute();
        $comentarios = $stmt_comentarios->fetchAll(PDO::FETCH_ASSOC);

        $producto['comentarios'] = $comentarios;

        // Enviar los datos del producto y los comentarios como JSON
        echo json_encode($producto);
    } else {
        echo json_encode(['error' => 'Producto no encontrado']);
    }
} else {
    echo json_encode(['error' => 'ID de producto no especificado']);
}
?>
