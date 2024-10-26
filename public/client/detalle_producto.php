<?php
include_once '../../src/config/database.php';
$database = new Database();
$db = $database->getConnection();

// Verificar si se recibe el slug como parámetro
$producto = null;
if (isset($_GET['slug'])) {
    $slug = $_GET['slug'];

    // Consultar los detalles del producto utilizando el slug
    $query = "SELECT * FROM producto WHERE slug = :slug";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':slug', $slug, PDO::PARAM_STR);
    $stmt->execute();
    $producto = $stmt->fetch(PDO::FETCH_ASSOC);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php echo $producto ? htmlspecialchars($producto['nombre_producto']) : 'Producto no encontrado'; ?>
    </title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .img-fluid {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
        }

        .product-details {
            margin-top: 20px;
        }

        .comentario {
            margin-bottom: 15px;
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }

        .comentario small {
            color: #777;
        }
    </style>
</head>

<body class="bg-light">

    <div class="container mt-5">
        <?php if ($producto): ?>
            <div class="row">
                <div class="col-md-6">
                    <img src="../../uploads/productos/<?php echo htmlspecialchars($producto['imagen']); ?>"
                        alt="<?php echo htmlspecialchars($producto['nombre_producto']); ?>"
                        class="img-fluid rounded">
                </div>
                <div class="col-md-6 product-details">
                    <h1><?php echo htmlspecialchars($producto['nombre_producto']); ?></h1>
                    <p class="text-muted">
                        Precio: <strong>Q. <?php echo number_format($producto['precio'], 2); ?></strong>
                    </p>
                    <p><strong>Stock disponible:</strong> <?php echo htmlspecialchars($producto['stock']); ?></p>
                    <p><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>

                    <form class="agregar-carrito-form" action="agregar_al_carrito.php" method="POST">
                        <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                        <div class="input-group mb-3" style="max-width: 200px;">
                            <input type="number" name="cantidad" class="form-control" value="1" min="1"
                                max="<?php echo htmlspecialchars($producto['stock']); ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-cart-plus"></i> Agregar al Carrito
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <hr>
            <h3>Comentarios y Calificaciones</h3>
            <div id="comentarios">
                <?php
                // Aquí se cargan los comentarios del producto desde la base de datos
                $queryComentarios = "
                    SELECT c.*, cl.nombre1, cl.apellido1 
                    FROM comentario c
                    JOIN cliente cl ON c.id_cliente = cl.id_cliente
                    WHERE c.id_producto = :id_producto
                ";
                $stmtComentarios = $db->prepare($queryComentarios);
                $stmtComentarios->bindParam(':id_producto', $producto['id_producto'], PDO::PARAM_INT);
                $stmtComentarios->execute();
                $comentarios = $stmtComentarios->fetchAll(PDO::FETCH_ASSOC);


                if ($comentarios):
                    foreach ($comentarios as $comentario): ?>
                        <div class="comentario">
                            <p>
                                <strong>
                                    <?php echo htmlspecialchars($comentario['nombre1']) . ' ' . htmlspecialchars($comentario['apellido1']); ?>
                                </strong>
                                - Calificación: <?php echo str_repeat('★', $comentario['calificacion']); ?>
                            </p>
                            <p><?php echo nl2br(htmlspecialchars($comentario['comentario'])); ?></p>
                            <small><?php echo htmlspecialchars($comentario['fecha_comentario']); ?></small>
                        </div>
                    <?php endforeach;
                else: ?>
                    <p>No hay comentarios aún.</p>
                <?php endif; ?>
            </div>

            <hr>
            <h4>Deja tu Comentario</h4>
            <form id="form-comentario" method="POST" action="guardar_comentario.php">
                <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                <div class="mb-3">
                    <label for="comentario" class="form-label">Comentario:</label>
                    <textarea class="form-control" name="comentario" id="comentario" rows="3" required></textarea>
                </div>
                <div class="mb-3">
                    <label for="calificacion" class="form-label">Calificación:</label>
                    <select class="form-select" name="calificacion" id="calificacion" required>
                        <option value="5">5 - Excelente</option>
                        <option value="4">4 - Muy bueno</option>
                        <option value="3">3 - Bueno</option>
                        <option value="2">2 - Regular</option>
                        <option value="1">1 - Malo</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-paper-plane"></i> Enviar Comentario
                </button>
            </form>
        <?php else: ?>
            <div class="alert alert-warning text-center" role="alert">
                <h4>Producto no encontrado</h4>
                <p>El producto que buscas no está disponible o no existe.</p>
                <a href="principal_cliente.php" class="btn btn-secondary">Volver a la tienda</a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(".agregar-carrito-form").on("submit", function(e) {
            e.preventDefault(); // Previene el envío por defecto

            var form = $(this);
            $.ajax({
                url: form.attr("action"),
                method: form.attr("method"),
                data: form.serialize(),
                success: function(response) {
                    var data = JSON.parse(response);

                    if (data.status === 'success') {
                        Swal.fire({
                            title: 'Producto agregado al carrito',
                            icon: 'success',
                            showCancelButton: true,
                            confirmButtonText: 'Ir al carrito',
                            cancelButtonText: 'Seguir comprando'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = 'carrito.php';
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al agregar el producto al carrito.'
                    });
                }
            });
        });
    </script>

</body>

</html>