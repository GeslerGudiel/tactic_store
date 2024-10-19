<?php
session_start();

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    header("Location: ../auth/login.php");
    exit;
}

include_once '../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

// Obtener los productos con stock mayor a 0
$query = "SELECT id_producto, nombre_producto, descripcion, precio, stock, imagen FROM producto WHERE id_emprendedor = :id_emprendedor AND stock > 0";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Ventas Locales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .card img {
            width: 100%;
            height: 150px;
            object-fit: contain;
        }

        .card-body {
            padding: 10px;
        }

        .card-title {
            font-size: 1rem;
            margin-bottom: 5px;
        }

        .card-text {
            font-size: 0.875rem;
            margin-bottom: 5px;
        }

        .card-footer {
            padding: 5px;
        }

        .col-md-4 {
            padding: 5px;
        }

        .carrito-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>

</head>

<body>
    <div class="container my-4">
        <h2 class="text-center">Productos Disponibles para Venta Local</h2>
        <div class="mb-3">
            <input type="text" id="buscarProducto" class="form-control" placeholder="Buscar producto...">
        </div>
        <div class="row" id="listaProductos">
            <?php if (count($productos) > 0): ?>
                <?php foreach ($productos as $producto): ?>
                    <div class="col-md-4 mb-2">
                        <div class="card h-100">
                            <img src="/comercio_electronico/uploads/productos/<?php echo htmlspecialchars($producto['imagen']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre_producto']); ?></h5>
                                <p class="card-text"><strong>Q<?php echo number_format($producto['precio'], 2); ?></strong></p>
                                <p class="card-text"><small>Stock: <?php echo $producto['stock']; ?></small></p>
                                <input type="number" class="form-control mb-2 cantidad-producto" data-id="<?php echo $producto['id_producto']; ?>" value="1" min="1" max="<?php echo $producto['stock']; ?>" placeholder="Cantidad">
                            </div>
                            <div class="card-footer text-center">
                                <button class="btn btn-primary btn-sm btn-agregar-carrito" data-id="<?php echo $producto['id_producto']; ?>" data-nombre="<?php echo htmlspecialchars($producto['nombre_producto']); ?>" data-precio="<?php echo $producto['precio']; ?>" data-stock="<?php echo $producto['stock']; ?>">
                                    <i class="fas fa-cart-plus"></i> Agregar
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center">No hay productos disponibles para la venta local.</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Botón para ver el carrito -->
    <button class="btn btn-success carrito-btn" id="verCarrito">
        <i class="fas fa-shopping-cart"></i> <span id="contadorCarrito">0</span>
    </button>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let carrito = [];

        // Búsqueda dinámica
        document.getElementById('buscarProducto').addEventListener('input', function() {
            const filtro = this.value;

            // Realizar una solicitud AJAX para buscar productos
            $.ajax({
                url: '/comercio_electronico/public/emprendedor/venta_local/buscar_producto.php',
                method: 'GET',
                data: {
                    filtro: filtro
                },
                success: function(data) {
                    $('#listaProductos').html(data);
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo realizar la búsqueda.', 'error');
                }
            });
        });

        // Validar el stock antes de agregar al carrito
        $(document).on('click', '.btn-agregar-carrito', function() {
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            const precio = $(this).data('precio');
            const stock = $(this).data('stock');
            const cantidad = parseInt($(`.cantidad-producto[data-id="${id}"]`).val());

            if (cantidad > stock) {
                Swal.fire('Error', 'La cantidad solicitada supera el stock disponible.', 'error');
                return;
            }

            // Verificar si el producto ya está en el carrito
            const productoExistente = carrito.find(p => p.id === id);
            if (productoExistente) {
                productoExistente.cantidad += cantidad;
            } else {
                carrito.push({ id, nombre, precio, cantidad });
            }

            actualizarCarrito();
            Swal.fire('Éxito', 'Producto agregado al carrito.', 'success');
        });

        // Función para actualizar el contador del carrito
        function actualizarCarrito() {
            const totalProductos = carrito.reduce((total, producto) => total + producto.cantidad, 0);
            $('#contadorCarrito').text(totalProductos);
        }

        // Mostrar contenido del carrito
        $('#verCarrito').click(function() {
            let contenidoCarrito = '<h3>Carrito de Compras</h3><ul class="list-group">';
            carrito.forEach(producto => {
                contenidoCarrito += `
                    <li class="list-group-item">
                        ${producto.nombre} - Cantidad: ${producto.cantidad} - Subtotal: Q${(producto.cantidad * producto.precio).toFixed(2)}
                    </li>`;
            });
            contenidoCarrito += '</ul>';
            Swal.fire({
                title: 'Contenido del Carrito',
                html: contenidoCarrito,
                confirmButtonText: 'Cerrar'
            });
        });
    </script>

</body>

</html>
