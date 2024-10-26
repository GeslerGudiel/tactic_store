<?php
session_start();
if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    echo '<p class="text-center">Acceso denegado</p>';
    exit;
}

include_once '../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener las categorías
$queryCategorias = "SELECT id_categoria, nombre_categoria FROM categoria";
$stmtCategorias = $db->prepare($queryCategorias);
$stmtCategorias->execute();
$categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

$id_emprendedor = $_SESSION['id_emprendedor'];
$termino = isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '';

// Consulta SQL con búsqueda dinámica
$query = "SELECT p.*, c.nombre_categoria 
          FROM producto p 
          JOIN categoria c ON p.id_categoria = c.id_categoria 
          WHERE p.id_emprendedor = :id_emprendedor 
          AND (p.nombre_producto LIKE :buscar 
               OR p.descripcion LIKE :buscar 
               OR c.nombre_categoria LIKE :buscar 
               OR p.estado LIKE :buscar)
          LIMIT 50";  // Cantidad de productos solicitados

$stmt = $db->prepare($query);
$busqueda = "%$termino%";
$stmt->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
$stmt->bindParam(':buscar', $busqueda, PDO::PARAM_STR);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si hay productos
if (count($productos) > 0): ?>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Nombre</th>
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
            <?php foreach ($productos as $producto):
                $nombreProducto = htmlspecialchars($producto['nombre_producto']);
                $descripcionProducto = htmlspecialchars($producto['descripcion']);
                $precioProducto = number_format($producto['precio'], 2);
                $imagenProducto = htmlspecialchars($producto['imagen']);
            ?>
                <tr class="producto-item" data-id="<?= $producto['id_producto'] ?>">
                    <td class="nombre"><?= $nombreProducto ?></td>
                    <td class="descripcion"><?= $descripcionProducto ?></td>
                    <td class="costo">Q. <?= number_format($producto['costo'], 2) ?></td>
                    <td>Q. <?= $precioProducto ?></td>
                    <td class="stock"><?= $producto['stock'] ?></td>
                    <td class="categoria"><?= htmlspecialchars($producto['nombre_categoria']) ?></td>
                    <td>
                        <img
                            src="/comercio_electronico/uploads/productos/<?= $imagenProducto ?>"
                            alt="Imagen del producto: <?= $nombreProducto ?>"
                            width="100"
                            loading="lazy">
                    </td>
                    <td class="estado">
                        <?= $producto['estado'] === 'disponible' ? 'Disponible' : 'No disponible' ?>
                    </td>
                    <td>
                        <i class="fas fa-edit text-primary btn-editar" data-producto='<?= json_encode($producto) ?>'></i>
                        <i class="fas fa-trash-alt text-danger btn-eliminar" data-id="<?= $producto['id_producto'] ?>"></i>
                    </td>
                </tr>
                <!-- Datos estructurados (JSON-LD) -->
                <script type="application/ld+json">
                    {
                        "@context": "https://schema.org/",
                        "@type": "Product",
                        "name": "<?= $nombreProducto ?>",
                        "description": "<?= $descripcionProducto ?>",
                        "image": "https://tactic-store.com/uploads/productos/<?= $imagenProducto ?>",
                        "sku": "<?= $producto['id_producto'] ?>",
                        "offers": {
                            "@type": "Offer",
                            "priceCurrency": "GTQ",
                            "price": "<?= $precioProducto ?>",
                            "availability": "<?= $producto['estado'] === 'disponible' ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' ?>"
                        }
                    }
                </script>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php else: ?>
    <p class="text-center">No se encontraron productos.</p>
<?php endif; ?>