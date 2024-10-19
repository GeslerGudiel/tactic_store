<?php
session_start(); // Asegurarse de iniciar la sesión

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    echo '<p class="text-center">Acceso no autorizado. Por favor, inicia sesión.</p>';
    exit;
}

include_once '../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

// Obtener el filtro de búsqueda
$filtro = isset($_GET['filtro']) ? htmlspecialchars($_GET['filtro']) : '';

// Consultar productos que coincidan con el filtro y que tengan stock mayor a 0
$query = "SELECT id_producto, nombre_producto, descripcion, precio, stock, imagen 
          FROM producto 
          WHERE id_emprendedor = :id_emprendedor 
          AND stock > 0 
          AND nombre_producto LIKE :filtro";
$stmt = $db->prepare($query);
$filtro_param = "%$filtro%";
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->bindParam(':filtro', $filtro_param);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generar el HTML con los resultados
if (count($productos) > 0) {
    foreach ($productos as $producto) {
        echo '<div class="col-md-4 mb-2">';
        echo '    <div class="card h-100">';
        echo '        <img src="/comercio_electronico/uploads/productos/' . htmlspecialchars($producto['imagen']) . '" class="card-img-top" alt="' . htmlspecialchars($producto['nombre_producto']) . '">';
        echo '        <div class="card-body">';
        echo '            <h5 class="card-title">' . htmlspecialchars($producto['nombre_producto']) . '</h5>';
        echo '            <p class="card-text"><strong>Q' . number_format($producto['precio'], 2) . '</strong></p>';
        echo '            <p class="card-text"><small>Stock: ' . $producto['stock'] . '</small></p>';
        echo '        </div>';
        echo '        <div class="card-footer text-center">';
        echo '            <button class="btn btn-primary btn-sm btn-agregar-carrito" data-id="' . $producto['id_producto'] . '" data-nombre="' . htmlspecialchars($producto['nombre_producto']) . '" data-precio="' . $producto['precio'] . '">';
        echo '                <i class="fas fa-cart-plus"></i> Agregar';
        echo '            </button>';
        echo '        </div>';
        echo '    </div>';
        echo '</div>';
    }
} else {
    echo '<p class="text-center">No se encontraron productos que coincidan con la búsqueda.</p>';
}
