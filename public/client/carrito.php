<?php
session_start(); 

// Verificar si el cliente está conectado
$loggedIn = isset($_SESSION['id_cliente']);

if (!$loggedIn) {
    // Si el cliente no está conectado, redirigir a la página de inicio de sesión
    $_SESSION['message'] = [
        'type' => 'warning',
        'text' => 'Debes iniciar sesión para ver tu carrito de compras.'
    ];
    header("Location: login_cliente.php");
    exit;
}

include_once '../../src/config/database.php';
$database = new Database();
$db = $database->getConnection();

// Obtener los productos del carrito
$productos_en_carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background-color: #f8f9fa;
        }

        .navbar {
            background-color: #343a40;
            color: white;
        }

        .navbar-brand,
        .nav-link {
            color: white;
        }

        .navbar-brand:hover,
        .nav-link:hover {
            color: #ffc107;
        }

        .container {
            margin-top: 20px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="principal_cliente.php">Tienda Virtual</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="principal_cliente.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="principal_cliente.php">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="principal_cliente.php">Categorías</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout_cliente.php">Cerrar Sesión</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="carrito.php">
                            <i class="fas fa-shopping-cart"></i>
                            Carrito
                            <span class="badge bg-danger" id="cart-count">
                                <?php echo isset($_SESSION['carrito']) ? array_sum(array_column($_SESSION['carrito'], 'cantidad')) : 0; ?>
                            </span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <h1>Carrito de Compras</h1>

        <?php if (empty($productos_en_carrito)): ?>
            <p class="text-center">Tu carrito está vacío. <a href="principal_cliente.php">¡Continúa comprando!</a></p>
        <?php else: ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $total_carrito = 0;
                    foreach ($productos_en_carrito as $id_producto => $detalle):
                        // Obtener información del producto desde la base de datos
                        $query = "SELECT nombre_producto, precio FROM producto WHERE id_producto = :id_producto";
                        $stmt = $db->prepare($query);
                        $stmt->bindParam(':id_producto', $id_producto);
                        $stmt->execute();
                        $producto = $stmt->fetch(PDO::FETCH_ASSOC);

                        if ($producto):
                            $total_producto = $producto['precio'] * $detalle['cantidad'];
                            $total_carrito += $total_producto;
                    ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                                <td>Q. <?php echo number_format($producto['precio'], 2); ?></td>
                                <td>
                                    <form action="actualizar_carrito.php" method="POST" class="d-flex">
                                        <input type="hidden" name="id_producto" value="<?php echo $id_producto; ?>">
                                        <input type="number" name="cantidad" class="form-control me-2" value="<?php echo $detalle['cantidad']; ?>" min="1" max="10" style="max-width: 70px;">
                                        <button type="submit" class="btn btn-sm btn-primary">Actualizar</button>
                                    </form>
                                </td>
                                <td>Q. <?php echo number_format($total_producto, 2); ?></td>
                                <td>
                                    <form action="eliminar_del_carrito.php" method="POST">
                                        <input type="hidden" name="id_producto" value="<?php echo $id_producto; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td><strong>Q. <?php echo number_format($total_carrito, 2); ?></strong></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
            <div class="text-end">
                <a href="checkout.php" class="btn btn-success">Proceder al Pago</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php if (isset($message)): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: '<?php echo $message['type']; ?>',
                    title: '<?php echo $message['type'] === 'success' ? 'Éxito' : 'Atención'; ?>',
                    text: '<?php echo $message['text']; ?>',
                    confirmButtonText: 'Aceptar'
                });
            });
        </script>
    <?php endif; ?>

</body>

</html>