<?php
session_start();  
 
include_once '../../src/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $productos_por_pagina = 5;
    $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $offset = ($pagina_actual - 1) * $productos_por_pagina;

    $query = "SELECT p.id_producto, p.nombre_producto, p.descripcion, p.precio, p.imagen, p.stock, c.id_categoria, c.nombre_categoria 
              FROM producto p 
              JOIN categoria c ON p.id_categoria = c.id_categoria
              WHERE p.estado = 'disponible'";

    if (isset($_GET['search']) && !empty($_GET['search'])) {
        $search = htmlspecialchars(strip_tags($_GET['search']));
        $query .= " AND p.nombre_producto LIKE :search";
    }

    if (isset($_GET['category']) && !empty($_GET['category'])) {
        $category = htmlspecialchars(strip_tags($_GET['category']));
        $query .= " AND p.id_categoria = :category";
    }

    if (isset($_GET['min_price']) && is_numeric($_GET['min_price'])) {
        $min_price = $_GET['min_price'];
        $query .= " AND p.precio >= :min_price";
    }

    if (isset($_GET['max_price']) && is_numeric($_GET['max_price'])) {
        $max_price = $_GET['max_price'];
        $query .= " AND p.precio <= :max_price";
    }

    $query .= " LIMIT :offset, :productos_por_pagina";

    $stmt = $db->prepare($query);

    if (isset($search)) {
        $search_param = "%$search%";
        $stmt->bindParam(':search', $search_param);
    }

    if (isset($category)) {
        $stmt->bindParam(':category', $category);
    }

    if (isset($min_price)) {
        $stmt->bindParam(':min_price', $min_price);
    }

    if (isset($max_price)) {
        $stmt->bindParam(':max_price', $max_price);
    }

    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->bindParam(':productos_por_pagina', $productos_por_pagina, PDO::PARAM_INT);
    
    $stmt->execute();

    // Si no hay productos
    if ($stmt->rowCount() == 0) {
        echo '<p>No se encontraron productos disponibles.</p>';
    }

    // Mostrar productos
    $productos_mostrados = 0;
    while ($producto = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo '<div class="product-item">';
        echo '<a href="#" data-id_producto="' . $producto['id_producto'] . '" class="product-link" style="text-decoration: none; color: inherit;">';
        echo '<img src="../../uploads/productos/' . htmlspecialchars($producto['imagen']) . '" alt="' . htmlspecialchars($producto['nombre_producto']) . '">';
        echo '<h5>' . htmlspecialchars($producto['nombre_producto']) . '</h5>';
        echo '<p>Q. ' . number_format($producto['precio'], 2) . '</p>';
        echo '<p><strong>Stock disponible:</strong> ' . htmlspecialchars($producto['stock']) . '</p>';
        echo '<p>' . htmlspecialchars($producto['nombre_categoria']) . '</p>';
        echo '</a>';

        if (isset($_SESSION['cliente_id'])) {
            echo '<form action="agregar_al_carrito.php" method="POST" class="d-flex agregar-carrito-form">';
            echo '<input type="hidden" name="id_producto" value="' . $producto['id_producto'] . '">';
            echo '<input type="number" name="cantidad" class="form-control me-2" value="1" min="1" max="' . htmlspecialchars($producto['stock']) . '" style="max-width: 100px;">';

            if ($producto['stock'] > 0) {
                echo '<button type="submit" class="btn btn-primary">Agregar al Carrito</button>';
            } else {
                echo '<button class="btn btn-secondary" disabled>Sin Stock</button>';
            }
            echo '</form>';
        } else {
            echo '<a href="login_cliente.php" class="btn btn-warning btn-login">Inicia sesión para agregar al carrito</a>';
        }
        echo '</div>';
        $productos_mostrados++;
    }

    // Paginación
    $query_total = "SELECT COUNT(*) FROM producto p WHERE p.estado = 'disponible'";
    if (isset($search)) {
        $query_total .= " AND p.nombre_producto LIKE :search";
    }

    if (isset($category)) {
        $query_total .= " AND p.id_categoria = :category";
    }

    if (isset($min_price)) {
        $query_total .= " AND p.precio >= :min_price";
    }

    if (isset($max_price)) {
        $query_total .= " AND p.precio <= :max_price";
    }

    $stmt_total = $db->prepare($query_total);

    if (isset($search)) {
        $stmt_total->bindParam(':search', $search_param);
    }

    if (isset($category)) {
        $stmt_total->bindParam(':category', $category);
    }

    if (isset($min_price)) {
        $stmt_total->bindParam(':min_price', $min_price);
    }

    if (isset($max_price)) {
        $stmt_total->bindParam(':max_price', $max_price);
    }

    $stmt_total->execute();
    $total_productos = $stmt_total->fetchColumn();
    $total_paginas = ceil($total_productos / $productos_por_pagina);
} catch (PDOException $e) {
    echo "Error al cargar los productos: " . $e->getMessage();
}
?>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $(".product-link").on("click", function(e) {
            e.preventDefault();
            var id_producto = $(this).data("id_producto");

            $.ajax({
                url: 'detalle_producto_modal.php',
                method: 'GET',
                data: { id_producto: id_producto },
                success: function(response) {
                    $("#productModal .modal-body").html(response);
                    $("#productModal").modal("show");
                },
                error: function() {
                    alert("Error al cargar los detalles del producto.");
                }
            });
        });

        $(".btn-login").on("click", function(e) {
            e.stopPropagation();
        });

        $(".agregar-carrito-form").on("submit", function(e) {
            e.preventDefault();

            var form = $(this);
            $.ajax({
                url: 'agregar_al_carrito.php',
                method: 'POST',
                data: form.serialize(),
                success: function(response) {
                    var data = JSON.parse(response);
                    $("#cart-count").text(data.cart_count);

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
    });
</script>
