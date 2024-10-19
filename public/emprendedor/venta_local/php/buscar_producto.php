<?php
include_once '../../../../src/config/database.php';

session_start();
$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';

$query = "SELECT id_producto, nombre_producto, descripcion, precio, stock, imagen 
          FROM producto 
          WHERE id_emprendedor = :id_emprendedor AND stock > 0 
          AND nombre_producto LIKE :filtro";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->bindValue(':filtro', "%$filtro%");
$stmt->execute();

$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($productos as $producto) {
    echo '<div class="col-md-4 mb-2">';
    echo '<div class="card h-100">';
    echo '<img src="/comercio_electronico/uploads/productos/' . htmlspecialchars($producto['imagen']) . '" class="card-img-top" alt="' . htmlspecialchars($producto['nombre_producto']) . '">';
    echo '<div class="card-body">';
    echo '<h5 class="card-title">' . htmlspecialchars($producto['nombre_producto']) . '</h5>';
    echo '<p class="card-text"><strong>Q' . number_format($producto['precio'], 2) . '</strong></p>';
    echo '<p class="card-text"><small>Stock: ' . $producto['stock'] . '</small></p>';
    echo '</div>';
    echo '<div class="card-footer text-center">';
    echo '<button class="btn btn-primary btn-sm btn-agregar-carrito" data-id="' . $producto['id_producto'] . '" data-nombre="' . htmlspecialchars($producto['nombre_producto']) . '" data-precio="' . $producto['precio'] . '" data-stock="' . $producto['stock'] . '">';
    echo '<i class="fas fa-cart-plus"></i> Agregar';
    echo '</button>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
}
