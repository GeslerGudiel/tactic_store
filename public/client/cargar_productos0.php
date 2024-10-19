<?php
include_once '../../src/config/database.php';
session_start();

$database = new Database();
$db = $database->getConnection();

$query = "SELECT p.id_producto, p.nombre_producto, p.precio, p.imagen, p.stock FROM producto p WHERE p.estado = 'disponible'";
$stmt = $db->prepare($query);
$stmt->execute();

while ($producto = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo '<div class="product-item">';
    echo '<img src="../../uploads/productos/' . htmlspecialchars($producto['imagen']) . '" alt="' . htmlspecialchars($producto['nombre_producto']) . '">';
    echo '<h5>' . htmlspecialchars($producto['nombre_producto']) . '</h5>';
    echo '<p>Q. ' . number_format($producto['precio'], 2) . '</p>';
    echo '<p><strong>Stock disponible:</strong> ' . htmlspecialchars($producto['stock']) . '</p>';

    if (isset($_SESSION['cliente_id'])) {
        echo '<form action="agregar_al_carrito.php" method="POST">';
        echo '<input type="hidden" name="id_producto" value="' . $producto['id_producto'] . '">';
        echo '<button type="submit" class="btn btn-primary">Agregar al Carrito</button>';
        echo '</form>';
    } else {
        echo '<a href="login_cliente.php" class="btn btn-warning">Inicia sesión para agregar al carrito</a>';
    }

    echo '</div>';
}
