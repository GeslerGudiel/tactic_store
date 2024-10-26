<?php
session_start(); // Iniciar la sesión

// Verificar si el cliente está autenticado
if (!isset($_SESSION['id_cliente'])) {
    echo json_encode(['status' => 'error', 'message' => 'Debes iniciar sesión para agregar productos al carrito.']);
    exit;
}

include_once '../../src/config/database.php';
$database = new Database();
$db = $database->getConnection();

// Verificar que la solicitud sea POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = intval($_POST['id_producto']);
    $cantidad = intval($_POST['cantidad']);

    // Obtener los detalles del producto junto con la promoción activa (si existe)
    $query = "
        SELECT p.id_producto, p.nombre_producto, p.stock, p.precio, 
               pr.precio_oferta, pr.fecha_fin 
        FROM producto p
        LEFT JOIN promocion pr 
            ON p.id_producto = pr.id_producto 
            AND pr.estado = 'Activo' 
            AND pr.fecha_fin >= CURDATE()
        WHERE p.id_producto = :id_producto
    ";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verificar si el producto existe y tiene stock suficiente
    if ($producto && $producto['stock'] >= $cantidad) {
        // Determinar el precio a usar (precio promocional o regular)
        $precio_final = !empty($producto['precio_oferta']) ? $producto['precio_oferta'] : $producto['precio'];

        // Agregar al carrito (almacenar en sesión)
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        if (isset($_SESSION['carrito'][$id_producto])) {
            $_SESSION['carrito'][$id_producto]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito'][$id_producto] = [
                'nombre' => $producto['nombre_producto'],
                'precio' => $precio_final,
                'cantidad' => $cantidad
            ];
        }

        echo json_encode(['status' => 'success', 'message' => 'Producto agregado al carrito.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No hay suficiente stock disponible.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido.']);
}
?>
