<?php
include_once '../../src/config/database.php';

if (!isset($_GET['id_producto'])) {
    header("Location: principal_cliente.php");
    exit;
}

// Verifica si hay algún error almacenado en la sesión
if (isset($_SESSION['error'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '" . htmlspecialchars($_SESSION['error']) . "',
                confirmButtonText: 'Aceptar'
            });
        });
    </script>";
    unset($_SESSION['error']); // Limpia el mensaje de error después de mostrarlo
}

// Verifica si el producto fue agregado al carrito y muestra la alerta
if (isset($_SESSION['carrito_alerta'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                title: 'Producto agregado al carrito',
                text: '¿Qué te gustaría hacer?',
                icon: 'success',
                showCancelButton: true,
                confirmButtonText: 'Ir al carrito',
                cancelButtonText: 'Seguir comprando',
                allowOutsideClick: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = 'carrito.php';
                }
            });
        });
    </script>";
    unset($_SESSION['carrito_alerta']); // Limpia la alerta después de mostrarla
}

$id_producto = intval($_GET['id_producto']);
$database = new Database();
$db = $database->getConnection();

// Obtener detalles del producto
$query_producto = "SELECT p.nombre_producto, p.descripcion, p.precio, p.imagen, p.stock, c.nombre_categoria 
                   FROM producto p 
                   JOIN categoria c ON p.id_categoria = c.id_categoria
                   WHERE p.id_producto = :id_producto AND p.estado = 'disponible'";
$stmt_producto = $db->prepare($query_producto);
$stmt_producto->bindParam(':id_producto', $id_producto);
$stmt_producto->execute();
$producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo "Producto no encontrado.";
    exit;
}

// Obtener comentarios del producto
$query_comentarios = "SELECT cl.nombre1, c.comentario, c.calificacion, c.fecha 
                      FROM comentarios c 
                      JOIN cliente cl ON c.id_cliente = cl.id_cliente 
                      WHERE c.id_producto = :id_producto 
                      ORDER BY c.fecha DESC";
$stmt_comentarios = $db->prepare($query_comentarios);
$stmt_comentarios->bindParam(':id_producto', $id_producto);
$stmt_comentarios->execute();
$comentarios = $stmt_comentarios->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($producto['nombre_producto']); ?> - Detalles</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            background-color: #f8f9fa;
        }

        .product-details {
            display: flex;
            gap: 20px;
            margin-top: 20px;
        }

        .product-details img {
            max-width: 400px;
            border-radius: 10px;
        }

        .product-info {
            max-width: 600px;
        }

        .product-info h1 {
            margin-bottom: 20px;
            font-size: 2rem;
        }

        .product-info p {
            margin-bottom: 10px;
            font-size: 1.2rem;
        }

        .product-info .btn {
            background-color: #007bff;
            border-color: #007bff;
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            transition: background-color 0.3s ease-in-out;
        }

        .product-info .btn:hover {
            background-color: #0056b3;
        }

        .comments-section {
            margin-top: 40px;
        }

        .comment {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .comment h5 {
            margin-bottom: 10px;
            font-size: 1.2rem;
        }

        .comment p {
            margin-bottom: 10px;
        }

        .comment .rating {
            color: #ffc107;
        }

        .footer {
            background-color: #343a40;
            color: white;
            padding: 20px 0;
            text-align: center;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Tienda Virtual</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="principal_cliente.php">Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Categorías</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="login_cliente.php">Iniciar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="product-details">
            <img src="../../uploads/productos/<?php echo htmlspecialchars($producto['imagen']); ?>" alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
            <div class="product-info">
                <h1><?php echo htmlspecialchars($producto['nombre_producto']); ?></h1>
                <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                <p><strong>Precio:</strong> Q<?php echo number_format($producto['precio'], 2); ?></p>
                <p><strong>Categoría:</strong> <?php echo htmlspecialchars($producto['nombre_categoria']); ?></p>
                <p><strong>Stock disponible:</strong> <?php echo htmlspecialchars($producto['stock']); ?></p>
                <form action="agregar_al_carrito.php" method="POST" class="d-flex">
                    <input type="hidden" name="id_producto" value="<?php echo $id_producto; ?>">
                    <input type="number" name="cantidad" class="form-control me-2" value="1" min="1" max="<?php echo htmlspecialchars($producto['stock']); ?>" style="max-width: 100px;">
                    <?php if ($producto['stock'] > 0) { ?>
                        <button type="submit" class="btn btn-primary">Agregar al Carrito</button>
                    <?php } else { ?>
                        <button class="btn btn-secondary" disabled>Sin Stock</button>
                    <?php } ?>
                </form>
            </div>
        </div>

        <div class="comments-section">
            <h2>Comentarios y Calificaciones</h2>
            <?php if ($comentarios): ?>
                <?php foreach ($comentarios as $comentario): ?>
                    <div class="comment">
                        <h5><?php echo htmlspecialchars($comentario['nombre']); ?></h5>
                        <p><?php echo htmlspecialchars($comentario['comentario']); ?></p>
                        <div class="rating">
                            <?php for ($i = 0; $i < $comentario['calificacion']; $i++): ?>
                                <i class="fas fa-star"></i>
                            <?php endfor; ?>
                        </div>
                        <small><?php echo htmlspecialchars($comentario['fecha']); ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No hay comentarios para este producto.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>© 2024 Tienda Virtual. Todos los derechos reservados.</p>
            <p>
                <a href="#">Política de privacidad</a> |
                <a href="#">Términos y condiciones</a> |
                <a href="#">Contacto</a>
            </p>
        </div>
    </footer>

    <?php
    // Mostrar SweetAlert si hay un error
    if (isset($_SESSION['carrito_alerta'])) {
        echo "<script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    title: 'Producto agregado al carrito',
                    text: '¿Qué te gustaría hacer?',
                    icon: 'success',
                    showCancelButton: true,
                    confirmButtonText: 'Ir al carrito',
                    cancelButtonText: 'Seguir comprando',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = 'carrito.php';
                    }
                });
            });
        </script>";
        unset($_SESSION['carrito_alerta']);
    }
    ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>