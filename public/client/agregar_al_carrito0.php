<?php
session_start();

include_once '../../src/config/database.php';

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['id_cliente'])) {
    // Redirigir al cliente a la página de inicio de sesión si no ha iniciado sesión
    $_SESSION['message'] = [
        'type' => 'warning',
        'text' => 'Debes iniciar sesión o registrarte para agregar productos al carrito.'
    ];
    header("Location: login_cliente.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = intval($_POST['id_producto']);
    $cantidad = intval($_POST['cantidad']);

    $database = new Database();
    $db = $database->getConnection();

    // Obtener el stock disponible del producto
    $query = "SELECT stock FROM producto WHERE id_producto = :id_producto";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_producto', $id_producto);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($producto && $cantidad <= $producto['stock']) {
        // Agregar al carrito (almacenar en sesión)
        if (isset($_SESSION['carrito'][$id_producto])) {
            $_SESSION['carrito'][$id_producto]['cantidad'] += $cantidad;
        } else {
            $_SESSION['carrito'][$id_producto] = [
                'cantidad' => $cantidad,
                // Nombre, precio...
            ];
        }

        // Calcular la cantidad total de productos en el carrito
        $total_items = 0;
        foreach ($_SESSION['carrito'] as $item) {
            $total_items += $item['cantidad'];
        }

        // Responder con la nueva cantidad total de productos en el carrito
        echo json_encode(['cart_count' => $total_items]);
    } else {
        // Si hay un error, puedes devolver un mensaje de error también en JSON
        echo json_encode(['error' => 'Stock insuficiente o producto no encontrado.']);
    }
}
