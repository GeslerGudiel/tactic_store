<?php
session_start(); // Iniciar la sesión

if (!isset($_SESSION['id_cliente'])) {
    echo json_encode(['status' => 'error', 'message' => 'Debes iniciar sesión para agregar productos al carrito.']);
    exit;
}

include_once '../../src/config/database.php';
$database = new Database();
$db = $database->getConnection();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = intval($_POST['id_producto']);
    $cantidad = intval($_POST['cantidad']);

    // Obtener el stock disponible del producto
    $query = "SELECT stock FROM producto WHERE id_producto = :id_producto";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_producto', $id_producto);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto && $producto['stock'] >= $cantidad) {
        // Agregar al carrito (almacenar en sesión)
        if (!isset($_SESSION['carrito'])) {
            $_SESSION['carrito'] = [];
        }

        if (isset($_SESSION['carrito'][$id_producto])) {
            $_SESSION['carrito'][$id_producto]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito'][$id_producto] = [
                'cantidad' => $cantidad
            ];
        }

        echo json_encode(['status' => 'success', 'message' => 'Producto agregado al carrito.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No hay suficiente stock disponible.']);
    }
}
?>
