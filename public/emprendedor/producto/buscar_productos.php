<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    http_response_code(403);
    echo 'Acceso denegado';
    exit;
} 

include_once '../../src/config/database.php';

$emprendedor_id = $_SESSION['id_emprendedor'];
$termino_busqueda = isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '';

try {
    $database = new Database();
    $db = $database->getConnection();

    $productos_por_pagina = 4; // Limite de productos por página
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    $offset = ($pagina_actual - 1) * $productos_por_pagina;

    // Consulta principal
    $query = "SELECT p.*, c.nombre AS categoria_nombre FROM productos p
              JOIN categorias c ON p.categoria_id = c.id
              WHERE p.emprendedor_id = :emprendedor_id
              AND (p.nombre LIKE :buscar OR p.descripcion LIKE :buscar)
              LIMIT :limit OFFSET :offset";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':emprendedor_id', $emprendedor_id, PDO::PARAM_INT);
    $stmt->bindValue(':buscar', "%$termino_busqueda%", PDO::PARAM_STR);
    $stmt->bindParam(':limit', $productos_por_pagina, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($productos)): ?>
        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Categoría</th>
                    <th>Imagen</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                        <td><?php echo 'Q. ' . number_format(htmlspecialchars($producto['precio']), 2); ?></td>
                        <td><?php echo htmlspecialchars($producto['stock']); ?></td>
                        <td><?php echo htmlspecialchars($producto['categoria_nombre']); ?></td>
                        <td><img src="../../uploads/<?php echo htmlspecialchars($producto['imagen']); ?>" alt="Imagen del Producto" width="100"></td>
                        <td><?php echo htmlspecialchars($producto['estado']); ?></td>
                        <td>
                            <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-primary btn-sm">Editar</a>
                            <a href="eliminar_producto.php?id=<?php echo $producto['id']; ?>" onclick="return confirm('¿Estás seguro de que deseas eliminar este producto?');" class="btn btn-danger btn-sm">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="text-center">No se encontraron productos con ese término de búsqueda.</p>
    <?php endif; ?>

<?php
} catch (PDOException $e) {
    http_response_code(500);
    echo 'Error al realizar la búsqueda: ' . $e->getMessage();
}
?>