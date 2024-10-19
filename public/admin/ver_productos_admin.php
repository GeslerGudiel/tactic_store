<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos de Emprendedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        h2 {
            color: #343a40;
        }

        .sort-link {
            color: #343a40;
            /* Color de texto neutro */
            font-weight: bold;
            text-decoration: none;
            /* Eliminar subrayado */
        }

        .sort-link:hover {
            color: #007bff;
            /* Color al pasar el ratón */
            text-decoration: none;
            /* Mantener sin subrayado al pasar el ratón */
        }

        .sort-icon {
            margin-left: 5px;
            font-size: 0.8em;
        }

        .form-check-label,
        .form-select {
            font-weight: bold;
        }

        .form-select,
        .input-group {
            border: 1px solid #ced4da;
            background-color: #fff;
        }

        .input-group {
            box-shadow: none;
        }

        .table th,
        .table td {
            vertical-align: middle;
            text-align: center;
        }

        .table img {
            width: 80px;
            height: auto;
            border-radius: 5px;
        }

        .pagination {
            justify-content: center;
        }

        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: auto;
            }

            .input-group {
                flex-direction: column;
            }

            .form-select {
                width: 100%;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="container my-4">
        <h2 class="text-center mb-4"><i class="fas fa-box-open"></i> Productos de Emprendedores</h2>

        <!-- Formulario de búsqueda y filtros -->
        <form id="filtros-form" class="row mb-4 g-3 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <input type="text" name="buscar" id="buscar" class="form-control" placeholder="Buscar productos">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Buscar</button>
                </div>
            </div>

            <div class="col-md-6 mt-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="sin_stock" name="sin_stock" value="1">
                    <label class="form-check-label" for="sin_stock"><i class="fas fa-box"></i> Mostrar solo productos sin stock</label>
                </div>
            </div>

            <div class="col-md-6 mt-3">
                <select name="categoria" id="categoria" class="form-select">
                    <option value="">Todas las categorías</option>
                    <!-- Aquí se cargan las categorías mediante PHP -->
                    <?php
                    include_once '../../src/config/database.php';
                    $database = new Database();
                    $db = $database->getConnection();
                    $categorias = $db->query("SELECT * FROM categoria")->fetchAll(PDO::FETCH_ASSOC);
                    foreach ($categorias as $categoria) {
                        echo "<option value='{$categoria['id_categoria']}'>{$categoria['nombre_categoria']}</option>";
                    }
                    ?>
                </select>
            </div>
        </form>

        <!-- Contenedor para los productos -->
        <div id="productos-list" class="table-responsive">
            <!-- El contenido se cargará con AJAX -->
        </div>

    </div>

    <script>
        // Cargar los productos al iniciar la página
        $(document).ready(function() {
            cargarProductos(1);

            // Función para cargar productos con AJAX
            function cargarProductos(pagina) {
                $.ajax({
                    url: "ver_productos_admin_ajax.php",
                    method: "GET",
                    data: $("#filtros-form").serialize() + "&pagina=" + pagina,
                    success: function(data) {
                        $("#productos-list").html(data);
                    },
                    error: function() {
                        Swal.fire('Error', 'Hubo un error al cargar los productos', 'error');
                    }
                });
            }

            // Al cambiar la página
            $(document).on("click", ".load-page", function(e) {
                e.preventDefault();
                var pagina = $(this).data("page");
                cargarProductos(pagina);
            });

            // Al cambiar los filtros o buscar productos
            $("#filtros-form").on("submit", function(e) {
                e.preventDefault();
                cargarProductos(1); // Se presenta en la página 1 al aplicar un filtro
            });

            // Al hacer clic en un encabezado de la tabla para ordenar
            $(document).on("click", ".sort-link", function(e) {
                e.preventDefault();
                var order_by = $(this).data("order");
                var current_order = $(this).data("sort");
                var new_order = current_order === 'asc' ? 'desc' : 'asc';

                $("#filtros-form").append('<input type="hidden" name="order_by" value="' + order_by + '">');
                $("#filtros-form").append('<input type="hidden" name="order" value="' + new_order + '">');
                cargarProductos(1);
            });
        });
    </script>
</body>

</html>