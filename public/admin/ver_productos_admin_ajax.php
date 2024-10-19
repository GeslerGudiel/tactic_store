<?php
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$productos_por_pagina = 5; // Número de productos por página
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $productos_por_pagina;

$termino_busqueda = isset($_GET['buscar']) ? htmlspecialchars(trim($_GET['buscar'])) : '';
$sin_stock = isset($_GET['sin_stock']) && $_GET['sin_stock'] == '1' ? true : false;
$categoria_filtro = isset($_GET['categoria']) && !empty($_GET['categoria']) ? (int)$_GET['categoria'] : null;
$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'nombre_producto';
$order = isset($_GET['order']) && $_GET['order'] === 'asc' ? 'asc' : 'desc';

try {
    $stock_query = $sin_stock ? "AND p.stock = 0" : "";
    $categoria_query = $categoria_filtro ? "AND p.id_categoria = :id_categoria" : "";

    // Contar el total de productos
    $query_total = "SELECT COUNT(*) AS total_productos FROM producto p 
                    WHERE (p.nombre_producto LIKE :buscar OR p.descripcion LIKE :buscar) 
                    $stock_query $categoria_query";

    $stmt_total = $db->prepare($query_total);
    $stmt_total->bindValue(':buscar', "%$termino_busqueda%", PDO::PARAM_STR);

    if ($categoria_filtro) {
        $stmt_total->bindParam(':id_categoria', $categoria_filtro, PDO::PARAM_INT);
    }

    $stmt_total->execute();
    $total_productos = $stmt_total->fetch(PDO::FETCH_ASSOC)['total_productos'];

    $total_paginas = ceil($total_productos / $productos_por_pagina);

    $query = "SELECT p.*, c.nombre_categoria, e.nombre1 AS nombre_emprendedor, e.apellido1 AS apellido_emprendedor, n.nombre_negocio
              FROM producto p
              JOIN categoria c ON p.id_categoria = c.id_categoria
              JOIN emprendedor e ON p.id_emprendedor = e.id_emprendedor
              JOIN negocio n ON p.id_negocio = n.id_negocio
              WHERE (p.nombre_producto LIKE :buscar OR p.descripcion LIKE :buscar OR n.nombre_negocio LIKE :buscar) 
              $stock_query $categoria_query
              ORDER BY $order_by $order
              LIMIT :limit OFFSET :offset";

    $stmt = $db->prepare($query);
    $stmt->bindValue(':buscar', "%$termino_busqueda%", PDO::PARAM_STR);

    if ($categoria_filtro) {
        $stmt->bindParam(':id_categoria', $categoria_filtro, PDO::PARAM_INT);
    }

    $stmt->bindParam(':limit', $productos_por_pagina, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error al cargar productos: " . $e->getMessage();
    exit;
}

// Generar la tabla con los resultados y la paginación
?>

<table class="table table-striped table-bordered">
    <thead>
        <tr>
            <th><a href="#" data-order="nombre_emprendedor" data-sort="<?php echo $order; ?>" class="sort-link">Emprendedor <i class="fas fa-sort sort-icon"></i></a></th>
            <th><a href="#" data-order="nombre_negocio" data-sort="<?php echo $order; ?>" class="sort-link">Negocio <i class="fas fa-sort sort-icon"></i></a></th>
            <th><a href="#" data-order="nombre_producto" data-sort="<?php echo $order; ?>" class="sort-link">Nombre del Producto <i class="fas fa-sort sort-icon"></i></a></th>
            <th>Descripción</th>
            <th>Costo</th>
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
                <td><?php echo htmlspecialchars($producto['nombre_emprendedor']); ?></td>
                <td><?php echo htmlspecialchars($producto['nombre_negocio']); ?></td>
                <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                <td><?php echo 'Q. ' . number_format($producto['costo'], 2); ?></td>
                <td><?php echo 'Q. ' . number_format($producto['precio'], 2); ?></td>
                <td><?php echo htmlspecialchars($producto['stock']); ?></td>
                <td><?php echo htmlspecialchars($producto['nombre_categoria']); ?></td>
                <td><img src="../../uploads/productos/<?php echo htmlspecialchars($producto['imagen']); ?>" alt="Imagen" width="100"></td>
                <td><?php echo $producto['estado'] == 'disponible' ? 'Disponible' : 'No disponible'; ?></td>
                <td>
                    <a href="editar_producto_admin.php?id_producto=<?php echo $producto['id_producto']; ?>" class="btn btn-primary btn-sm">Editar</a>
                    <a href="eliminar_producto.php?id_producto=<?php echo $producto['id_producto']; ?>" class="btn btn-danger btn-sm">Eliminar</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<!-- Paginación -->
<nav aria-label="Page navigation">
    <ul class="pagination justify-content-center">
        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <li class="page-item <?php echo $pagina_actual == $i ? 'active' : ''; ?>">
                <a class="page-link load-page" href="#" data-page="<?php echo $i; ?>">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php endfor; ?>
    </ul>
</nav>
