<?php
session_start();

// Verificar si el cliente está conectado
$loggedIn = isset($_SESSION['id_cliente']);

if (!$loggedIn) {
    // Si el cliente no está conectado, redirigir a la página de inicio de sesión
    $_SESSION['message'] = [
        'type' => 'warning',
        'text' => 'Debes iniciar sesión para proceder al pago.'
    ];
    header("Location: login_cliente.php");
    exit;
}

include_once '../../src/config/database.php';
$database = new Database();
$db = $database->getConnection();

// Obtener los productos del carrito
$productos_en_carrito = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];

if (empty($productos_en_carrito)) {
    // Si el carrito está vacío, redirigir al cliente a la página principal
    $_SESSION['message'] = [
        'type' => 'warning',
        'text' => 'Tu carrito está vacío. Agrega productos antes de proceder al pago.'
    ];
    header("Location: principal_cliente.php");
    exit;
}

// Verificar si hay mensajes en la sesión para mostrar
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Eliminar el mensaje después de mostrarlo
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proceso de Pago</title>
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
        <h1>Confirmar Pedido</h1>
        <h3>Productos en tu Carrito</h3>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Total</th>
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
                            <td><?php echo $detalle['cantidad']; ?></td>
                            <td>Q. <?php echo number_format($total_producto, 2); ?></td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <tr>
                    <td colspan="3" class="text-end"><strong>Total del Pedido:</strong></td>
                    <td><strong>Q. <?php echo number_format($total_carrito, 2); ?></strong></td>
                </tr>
            </tbody>
        </table>

        <h3>Información de Envío</h3>
        <form action="procesar_pedido.php" method="POST" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="direccion_envio" class="form-label">Dirección de Envío</label>
                <input type="text" class="form-control" id="direccion_envio" name="direccion_envio" required>
            </div>
            <div class="mb-3">
                <label for="telefono_contacto" class="form-label">Teléfono de Contacto</label>
                <input type="text" class="form-control" id="telefono_contacto" name="telefono_contacto" required>
            </div>

            <h3>Método de Pago</h3>
            <div class="mb-3">
                <label for="metodo_pago" class="form-label">Selecciona el método de pago:</label>
                <select class="form-select" id="metodo_pago" name="metodo_pago" required>
                    <option value="tarjeta">Tarjeta de crédito/débito</option>
                    <option value="deposito_bancario">Depósito Bancario</option>
                </select>
            </div>

            <div id="informacion_bancaria" style="display: none;">
                <h5>Información para Depósito Bancario:</h5>
                <p>Banco: Banco Ejemplo</p>
                <p>Número de cuenta: 123456789</p>
                <p>Nombre de la cuenta: Nombre Ejemplo</p>
                <p>Tipo de cuenta: Ahorro</p>
                <p>Por favor, realiza el depósito y sube el comprobante a continuación.</p>

                <div class="mb-3">
                    <label for="imagen_comprobante" class="form-label">Subir Comprobante de Depósito:</label>
                    <input class="form-control" type="file" id="imagen_comprobante" name="imagen_comprobante" accept="image/*">
                </div>
                <div class="mb-3 form-check">
                    <input class="form-check-input" type="checkbox" id="subir_despues" name="subir_despues">
                    <label class="form-check-label" for="subir_despues">
                        Subir comprobante después
                    </label>
                </div>
            </div>

            <div class="text-end mt-4">
                <button type="submit" class="btn btn-success">Confirmar Pedido</button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('metodo_pago').addEventListener('change', function() {
            var metodo = this.value;
            var informacionBancaria = document.getElementById('informacion_bancaria');
            if (metodo === 'deposito_bancario') {
                informacionBancaria.style.display = 'block';
            } else {
                informacionBancaria.style.display = 'none';
            }
        });
    </script>

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