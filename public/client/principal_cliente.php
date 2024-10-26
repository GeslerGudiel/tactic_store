<?php
session_start();  // Iniciar la sesión

// Verificar si el cliente está conectado
$loggedIn = isset($_SESSION['id_cliente']);

include_once '../../src/config/database.php';
$database = new Database();
$db = $database->getConnection();
?>

<?php
if (isset($_SESSION['message'])) {
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: '" . $_SESSION['message']['type'] . "',
            title: 'Atención',
            text: '" . $_SESSION['message']['text'] . "',
            confirmButtonText: 'Aceptar'
        });
    });
    </script>";
    unset($_SESSION['message']);
}

$query_categorias = "SELECT * FROM categoria";
$stmt_categorias = $db->prepare($query_categorias);
$stmt_categorias->execute();
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

$productos_por_pagina = 2; // Número de productos a mostrar por página
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1; // Obtener el número de la página actual
$inicio = ($pagina_actual - 1) * $productos_por_pagina; // Calcular el offset para la consulta SQL

// Consulta para contar el total de productos disponibles
$query_total_productos = "SELECT COUNT(*) as total FROM producto WHERE estado = 'disponible'";

// Aplicar filtro de búsqueda
if (!empty($_GET['search'])) {
    $query_total_productos .= " AND nombre_producto LIKE :search";
}

// Aplicar filtro de categoría
if (!empty($_GET['category'])) {
    $query_total_productos .= " AND id_categoria = :category";
}

// Aplicar filtro de precios
if (!empty($_GET['min_price'])) {
    $query_total_productos .= " AND precio >= :min_price";
}
if (!empty($_GET['max_price'])) {
    $query_total_productos .= " AND precio <= :max_price";
}

$stmt_total_productos = $db->prepare($query_total_productos);

// Bind de los valores de los filtros
if (!empty($_GET['search'])) {
    $search = "%" . $_GET['search'] . "%";
    $stmt_total_productos->bindParam(':search', $search, PDO::PARAM_STR);
}
if (!empty($_GET['category'])) {
    $stmt_total_productos->bindParam(':category', $_GET['category'], PDO::PARAM_INT);
}
if (!empty($_GET['min_price'])) {
    $stmt_total_productos->bindParam(':min_price', $_GET['min_price'], PDO::PARAM_INT);
}
if (!empty($_GET['max_price'])) {
    $stmt_total_productos->bindParam(':max_price', $_GET['max_price'], PDO::PARAM_INT);
}

$stmt_total_productos->execute();
$total_productos = $stmt_total_productos->fetch(PDO::FETCH_ASSOC)['total'];

$total_paginas = ceil($total_productos / $productos_por_pagina);

if ($pagina_actual > $total_paginas && $total_paginas > 0) {
    header("Location: principal_cliente.php?" . $query_string . "&pagina=" . $total_paginas);
    exit;
}


?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?php
        if (isset($_GET['search'])) {
            echo "Resultados para '" . htmlspecialchars($_GET['search']) . "' - Tienda Virtual";
        } elseif (isset($_GET['category'])) {
            $categoria_seleccionada = $categorias[array_search($_GET['category'], array_column($categorias, 'id_categoria'))]['nombre_categoria'] ?? 'Categoría';
            echo "Categoría: " . htmlspecialchars($categoria_seleccionada) . " - Tienda Virtual";
        } else {
            echo "Bienvenido a la Tienda Virtual - Los mejores productos en línea";
        }
        ?>
    </title>

    <meta name="description" content="<?php
                                        if (isset($_GET['search'])) {
                                            echo "Explora los resultados de búsqueda para '" . htmlspecialchars($_GET['search']) . "' en nuestra tienda en línea.";
                                        } elseif (isset($_GET['category'])) {
                                            echo "Productos disponibles en la categoría: " . htmlspecialchars($categoria_seleccionada) . ".";
                                        } else {
                                            echo "Descubre productos únicos en nuestra tienda virtual con precios competitivos y promociones.";
                                        }
                                        ?>">

    <meta name="keywords" content="<?php
                                    if (isset($_GET['search'])) {
                                        echo htmlspecialchars($_GET['search']) . ", tienda virtual, productos en línea, comprar";
                                    } elseif (isset($_GET['category'])) {
                                        echo htmlspecialchars($categoria_seleccionada) . ", productos, tienda, promociones";
                                    } else {
                                        echo "tienda virtual, productos, ofertas, comprar en línea";
                                    }
                                    ?>">

    <meta name="author" content="Tienda Virtual">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
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

        .product-item {
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            text-align: center;
            padding: 15px;
            transition: transform 0.3s ease;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: calc(20% - 20px);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
            /* Tarjetas con la misma altura */
        }

        .product-item img {
            width: 100%;
            /* Imagen ocupa el ancho completo del contenedor */
            height: 200px;
            /* Altura fija para todas las imágenes */
            object-fit: scale-down;
            /* Se mantiene la proporción de la imagen mientras cubre el contenedor */
            margin-bottom: 15px;
            border-radius: 8px;
            /* Borde redondeado para las imágenes */
        }

        .product-item h5 {
            font-size: 1.2rem;
            margin-bottom: 10px;
            color: #343a40;
            flex-grow: 1;
            /* Título ocupando el espacio disponible */
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .product-item p {
            margin-bottom: 10px;
            color: #555;
        }

        .product-item .btn {
            width: 100%;
            margin-top: auto;
            /* Botón hacia el final del contenedor */
        }

        .product-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .product-link {
            text-decoration: none;
            /* Elimina el subrayado */
            color: #000;
            /* Cambia el color del texto a negro */
            font-weight: bold;
            /* Opcional: Hace el texto más visible */
        }

        .product-link:hover {
            color: #0056b3;
            /* Cambia el color del enlace al pasar el cursor (opcional) */
        }

        .search-bar .input-group-text {
            background-color: #f8f9fa;
            border-right: 0;
        }

        .search-bar .form-control,
        .search-bar .form-select {
            border-left: 0;
        }

        .chat-box {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }

        .message {
            margin-bottom: 10px;
        }

        .message.admin {
            text-align: right;
            color: blue;
        }

        .message.cliente {
            text-align: left;
            color: green;
        }

        .message img {
            max-width: 100%;
            border-radius: 8px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="principal_cliente.php"><i class="fas fa-store"></i> Tienda Virtual</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="principal_cliente.php"><i class="fas fa-home"></i> Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="principal_cliente.php"><i class="fas fa-box-open"></i> Productos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="principal_cliente.php"><i class="fas fa-th-list"></i> Categorías</a>
                    </li>

                    <?php if ($loggedIn): ?>
                        <!-- Menú desplegable del cliente -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user"></i> <?php echo isset($_SESSION['nombre_cliente']) ? htmlspecialchars($_SESSION['nombre_cliente'] . ' ' . $_SESSION['apellido_cliente']) : 'Cliente'; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                <li><a class="dropdown-item" href="perfil_cliente.php"><i class="fas fa-user-circle"></i> Perfil</a></li>
                                <li><a class="dropdown-item" href="historial_pedidos.php"><i class="fas fa-history"></i> Historial de Pedidos</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li><a class="dropdown-item" href="logout_cliente.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
                            </ul>
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
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="login_cliente.php"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">

        <h1 class="text-center my-4">Bienvenido a la Tienda Virtual</h1>

        <div class="search-bar mb-4">
            <form id="searchForm" action="principal_cliente.php" method="GET" class="row g-2 align-items-center">
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-search"></i></span>
                        <input type="text" class="form-control" name="search" placeholder="Buscar productos..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" onchange="this.form.submit()">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-list"></i></span>
                        <select class="form-select" name="category" onchange="this.form.submit()">
                            <option value="">Todas las categorías</option>
                            <?php
                            foreach ($categorias as $categoria) {
                                echo '<option value="' . $categoria['id_categoria'] . '"' . (isset($_GET['category']) && $_GET['category'] == $categoria['id_categoria'] ? 'selected' : '') . '>' . htmlspecialchars($categoria['nombre_categoria']) . '</option>';
                            }
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        <input type="number" class="form-control" name="min_price" placeholder="Precio mínimo" value="<?php echo isset($_GET['min_price']) ? htmlspecialchars($_GET['min_price']) : ''; ?>">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-dollar-sign"></i></span>
                        <input type="number" class="form-control" name="max_price" placeholder="Precio máximo" value="<?php echo isset($_GET['max_price']) ? htmlspecialchars($_GET['max_price']) : ''; ?>" onchange="this.form.submit()">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-sort"></i></span>
                        <select class="form-select" name="order_by" onchange="this.form.submit()">
                            <option value="">Ordenar por Precio</option>
                            <option value="asc" <?php echo isset($_GET['order_by']) && $_GET['order_by'] == 'asc' ? 'selected' : ''; ?>>Precio: Bajo a Alto</option>
                            <option value="desc" <?php echo isset($_GET['order_by']) && $_GET['order_by'] == 'desc' ? 'selected' : ''; ?>>Precio: Alto a Bajo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <a href="principal_cliente.php" class="btn btn-secondary w-100"><i class="fas fa-times"></i> Eliminar Filtros</a>
                </div>
            </form>
        </div>



        <div class="product-grid" id="productos">
            <?php
            $query = "
                SELECT p.id_producto, p.nombre_producto, p.precio, p.imagen, p.stock, p.slug, 
                    pr.tipo_promocion, pr.precio_oferta, pr.porcentaje_descuento, pr.fecha_fin
                FROM producto p
                LEFT JOIN promocion pr 
                    ON p.id_producto = pr.id_producto 
                    AND pr.estado = 'Activo' 
                    AND pr.fecha_fin >= CURDATE()
                WHERE p.estado = 'disponible'
                ";
            // Aplicar filtro de búsqueda
            if (!empty($_GET['search'])) {
                $query .= " AND nombre_producto LIKE :search";
            }

            // Aplicar filtro de categoría
            if (!empty($_GET['category'])) {
                $query .= " AND id_categoria = :category";
            }

            // Aplicar filtro de precios
            if (!empty($_GET['min_price'])) {
                $query .= " AND precio >= :min_price";
            }
            if (!empty($_GET['max_price'])) {
                $query .= " AND precio <= :max_price";
            }

            // Aplicar ordenamiento por precio
            if (!empty($_GET['order_by']) && in_array($_GET['order_by'], ['asc', 'desc'])) {
                $query .= " ORDER BY precio " . ($_GET['order_by'] === 'asc' ? 'ASC' : 'DESC');
            } else {
                // Ordenar por defecto si no se selecciona un orden
                $query .= " ORDER BY nombre_producto ASC";
            }

            // Agregar la paginación
            $query .= " LIMIT :inicio, :productos_por_pagina";

            $stmt = $db->prepare($query);

            // Bind de los valores de los filtros
            if (!empty($_GET['search'])) {
                $search = "%" . $_GET['search'] . "%";
                $stmt->bindParam(':search', $search, PDO::PARAM_STR);
            }
            if (!empty($_GET['category'])) {
                $stmt->bindParam(':category', $_GET['category'], PDO::PARAM_INT);
            }
            if (!empty($_GET['min_price'])) {
                $stmt->bindParam(':min_price', $_GET['min_price'], PDO::PARAM_INT);
            }
            if (!empty($_GET['max_price'])) {
                $stmt->bindParam(':max_price', $_GET['max_price'], PDO::PARAM_INT);
            }

            $stmt->bindParam(':inicio', $inicio, PDO::PARAM_INT);
            $stmt->bindParam(':productos_por_pagina', $productos_por_pagina, PDO::PARAM_INT);
            $stmt->execute();



            while ($producto = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo '<div class="product-item">';

                // Imagen del producto con evento JavaScript para abrir el modal
                echo '<img src="../../uploads/productos/' . htmlspecialchars($producto['imagen']) . '" 
                        alt="Producto: ' . htmlspecialchars($producto['nombre_producto']) . '" 
                        class="product-image" 
                        data-id="' . $producto['id_producto'] . '" 
                        style="cursor: pointer; width: 100%; height: 200px; object-fit: scale-down;">';

                // Título del producto con enlace SEO-friendly usando el slug de manera dinámica
                echo '<h5>';
                echo '<a href="detalle_producto.php?slug=' . urlencode($producto['slug']) . '" class="product-link">'
                    . htmlspecialchars($producto['nombre_producto']) . '</a>';
                echo '</h5>';

                if (!empty($producto['precio_oferta'])) {
                    // Mostrar precio con descuento y tipo de promoción
                    echo '<p><strong>Oferta: </strong>Q. ' . number_format($producto['precio_oferta'], 2) . '</p>';
                    echo '<p><span class="badge bg-success">' . htmlspecialchars($producto['tipo_promocion']) . '</span></p>';
                    echo '<p><del>Antes: Q. ' . number_format($producto['precio'], 2) . '</del></p>';
                } else {
                    // Mostrar precio normal si no tiene promoción
                    echo '<p><strong>Precio: </strong>Q. ' . number_format($producto['precio'], 2) . '</p>';
                }

                echo '<p><strong>Stock disponible: </strong>' . htmlspecialchars($producto['stock']) . '</p>';

                if ($loggedIn) {
                    echo '<form class="agregar-carrito-form" action="agregar_al_carrito.php" method="POST">';
                    echo '<input type="hidden" name="id_producto" value="' . $producto['id_producto'] . '">';
                    echo '<input type="number" name="cantidad" class="form-control me-2" value="1" min="1" max="'
                        . htmlspecialchars($producto['stock']) . '" style="max-width: 100px; margin: 10px auto;">';
                    echo '<button type="submit" class="btn btn-primary"><i class="fas fa-cart-plus"></i> Agregar al Carrito</button>';
                    echo '</form>';
                } else {
                    echo '<a href="login_cliente.php" class="btn btn-warning"><i class="fas fa-exclamation-circle"></i> Inicia sesión para agregar al carrito</a>';
                }

                echo '</div>'; // Cerrar el div de product-item
            }

            ?>
        </div>
        <nav aria-label="Page navigation">
            <ul class="pagination justify-content-center">
                <?php
                $query_string = http_build_query($_GET); // Crear una cadena de consulta con los filtros aplicados
                $query_string = preg_replace('/&?pagina=\d+/', '', $query_string); // Eliminar cualquier valor de página existente
                if ($pagina_actual > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="principal_cliente.php?<?php echo $query_string . '&pagina=' . ($pagina_actual - 1); ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                    <li class="page-item <?php echo ($pagina_actual == $i) ? 'active' : ''; ?>">
                        <a class="page-link" href="principal_cliente.php?<?php echo $query_string . '&pagina=' . $i; ?>"><?php echo $i; ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($pagina_actual < $total_paginas): ?>
                    <li class="page-item">
                        <a class="page-link" href="principal_cliente.php?<?php echo $query_string . '&pagina=' . ($pagina_actual + 1); ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>


    </div>

    <!-- Modal -->
    <div class="modal fade" id="detalleProductoModal" tabindex="-1" aria-labelledby="detalleProductoModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detalleProductoModalLabel">Detalles del Producto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Aquí se cargarán dinámicamente los detalles del producto -->
                    <div id="detalleProductoContent">
                        <p>Cargando...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const detalleProductoModal = document.getElementById('detalleProductoModal');

            // Seleccionar todas las imágenes que abren el modal
            document.querySelectorAll('.product-image').forEach(img => {
                img.addEventListener('click', function() {
                    const productoId = this.getAttribute('data-id'); // Obtener ID del producto

                    // Fetch para obtener los detalles del producto
                    fetch(`obtener_detalles_producto.php?id_producto=${productoId}`)
                        .then(response => response.json())
                        .then(data => {
                            const modalBody = detalleProductoModal.querySelector('.modal-body');
                            let content = `
                        <div class="row">
                            <div class="col-md-6">
                                <img src="../../uploads/productos/${data.imagen}" 
                                    alt="${data.nombre_producto}" class="img-fluid">
                            </div>
                            <div class="col-md-6">
                                <h5>${data.nombre_producto}</h5>
                                <p><strong>Precio: </strong>Q. ${data.precio}</p>
                                <p><strong>Stock disponible: </strong>${data.stock}</p>
                                <p><strong>Descripción: </strong>${data.descripcion}</p>
                                <form id="modalAgregarCarritoForm" class="agregar-carrito-form" 
                                      action="agregar_al_carrito.php" method="POST">
                                    <input type="hidden" name="id_producto" value="${data.id_producto}">
                                    <input type="number" name="cantidad" class="form-control me-2" 
                                           value="1" min="1" max="${data.stock}" 
                                           style="max-width: 100px; margin: 10px auto;">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-cart-plus"></i> Agregar al Carrito
                                    </button>
                                </form>
                            </div>
                        </div>
                        <hr>
                        <h5>Comentarios y Calificaciones</h5>
                        <div id="comentarios">
                    `;

                            // Generar comentarios dinámicamente
                            if (data.comentarios.length > 0) {
                                data.comentarios.forEach(comentario => {
                                    content += `
                                <div class="comentario">
                                    <p><strong>${comentario.nombre1} ${comentario.apellido1}</strong> 
                                    - Calificación: ${'★'.repeat(comentario.calificacion)}</p>
                                    <p>${comentario.comentario}</p>
                                    <small>${comentario.fecha_comentario}</small>
                                    ${comentario.respuesta ? `<p><strong>Respuesta:</strong> ${comentario.respuesta}</p>` : ''}
                                    <hr>
                                </div>
                            `;
                                });
                            } else {
                                content += '<p>No hay comentarios aún.</p>';
                            }

                            // Formulario para agregar comentarios
                            content += `
                        </div>
                        <hr>
                        <h5>Deja tu Comentario</h5>
                        <form id="form-comentario" method="POST" action="guardar_comentario.php">
                            <input type="hidden" name="id_producto" value="${data.id_producto}">
                            <div class="mb-3">
                                <label for="comentario" class="form-label">Comentario:</label>
                                <textarea class="form-control" name="comentario" id="comentario" 
                                          rows="3" required></textarea>
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
                    `;

                            modalBody.innerHTML = content;

                            // Agregar funcionalidad de carrito desde el modal
                            const modalForm = document.getElementById('modalAgregarCarritoForm');
                            modalForm.addEventListener('submit', function(e) {
                                e.preventDefault();

                                $.ajax({
                                    url: modalForm.action,
                                    method: modalForm.method,
                                    data: $(modalForm).serialize(),
                                    success: function(response) {
                                        const data = JSON.parse(response);

                                        if (data.status === 'success') {
                                            $("#cart-count").text(function(i, oldval) {
                                                return parseInt(oldval) + parseInt(modalForm.querySelector("input[name='cantidad']").value);
                                            });

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

                                            $('#detalleProductoModal').modal('hide');
                                        } else {
                                            Swal.fire({
                                                icon: 'error',
                                                title: 'Error',
                                                text: data.message,
                                                confirmButtonText: 'Aceptar'
                                            });
                                        }
                                    },
                                    error: function() {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Error',
                                            text: 'Hubo un problema al agregar el producto al carrito.',
                                            confirmButtonText: 'Aceptar'
                                        });
                                    }
                                });
                            });

                            const modalInstance = new bootstrap.Modal(detalleProductoModal);
                            modalInstance.show();
                        })
                        .catch(error => console.error('Error al cargar los detalles del producto:', error));
                });
            });
        });
    </script>



    <script>
        $(".agregar-carrito-form").on("submit", function(e) {
            e.preventDefault(); // Previene el comportamiento predeterminado del formulario

            var form = $(this);
            $.ajax({
                url: 'agregar_al_carrito.php',
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    var data = JSON.parse(response);

                    if (data.status === 'success') {
                        // Actualizar el contador del carrito en el navbar
                        $("#cart-count").text(function(i, oldval) {
                            return parseInt(oldval) + parseInt(form.find("input[name='cantidad']").val());
                        });

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
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message,
                            confirmButtonText: 'Aceptar'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al agregar el producto al carrito. Inténtalo de nuevo.',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });
        });
    </script>

    <!-- Chat con el Administrador -->
    <div class="container mt-5">
        <h3>Chat con el Administrador</h3>

        <!-- Chat box -->
        <div class="chat-box border p-3 mb-3" id="chat-box" style="height: 300px; overflow-y: scroll; background-color: #f8f9fa;">
            <!-- Aquí se cargarán los mensajes -->
        </div>

        <!-- Formulario para enviar mensajes e imágenes -->
        <form id="form-chat" enctype="multipart/form-data">
            <input type="hidden" name="id_cliente" value="<?php echo $_SESSION['id_cliente']; ?>">
            <input type="hidden" name="enviado_por" value="cliente">
            <div class="input-group mb-2">
                <input type="text" id="mensaje" name="mensaje" class="form-control" placeholder="Escribe tu mensaje...">
            </div>
            <div class="input-group mb-2">
                <input type="file" id="imagen" name="imagen" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Enviar</button>
        </form>
    </div>

    <script>
        const chatBox = document.getElementById('chat-box');
        const formChat = document.getElementById('form-chat');
        const mensajeInput = document.getElementById('mensaje');
        const imagenInput = document.getElementById('imagen');

        // Cargar mensajes entre el cliente y el administrador
        function cargarMensajes() {
            fetch('cargar_mensajes_cliente.php')
                .then(response => response.json())
                .then(data => {
                    chatBox.innerHTML = ''; // Limpiar chat
                    let hayNoLeidos = false;
                    data.forEach(mensaje => {
                        const messageDiv = document.createElement('div');
                        messageDiv.classList.add('message', mensaje.enviado_por === 'admin' ? 'admin' : 'cliente');

                        // Mostrar mensaje y, si existe, la imagen
                        let contenido = `<strong>${mensaje.enviado_por === 'admin' ? 'Administrador' : 'Tú'}:</strong> ${mensaje.mensaje}`;

                        if (mensaje.imagen) {
                            contenido += `<br><img src="../../uploads/chat_imagenes/${mensaje.imagen}" alt="Imagen" style="max-width: 100%; max-height: 200px;">`;
                        }

                        messageDiv.innerHTML = contenido;
                        chatBox.appendChild(messageDiv);

                        // Comprobar si hay mensajes no leídos
                        if (mensaje.leido == 0 && mensaje.enviado_por === 'admin') {
                            hayNoLeidos = true;
                        }
                    });

                    chatBox.scrollTop = chatBox.scrollHeight; // Auto-scroll

                    // Mostrar notificación si hay mensajes no leídos
                    if (hayNoLeidos) {
                        document.getElementById('notificacion-nuevos-mensajes').style.display = 'block';
                    } else {
                        document.getElementById('notificacion-nuevos-mensajes').style.display = 'none';
                    }
                })
                .catch(error => {
                    console.error('Error al cargar los mensajes:', error);
                });
        }

        // Enviar un mensaje
        formChat.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(formChat);
            fetch('enviar_mensaje_cliente.php', { // Cambiar a enviar_mensaje_cliente.php
                method: 'POST',
                body: formData
            }).then(() => {
                cargarMensajes();
                mensajeInput.value = ''; // Limpiar campo
                imagenInput.value = ''; // Limpiar campo de imagen
            }).catch(error => {
                console.error('Error al enviar el mensaje:', error);
            });
        });

        // Recargar mensajes automáticamente cada 2 segundos
        setInterval(() => {
            cargarMensajes();
        }, 2000); // 2 segundos
    </script>

</body>

</html>