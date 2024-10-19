<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías y Subcategorías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="container mt-5">
        <h3><i class="fas fa-tag"></i> Gestión de Categorías y Subcategorías</h3>

        <!-- Filtro y búsqueda -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <input type="text" id="buscarCategoria" class="form-control w-50" placeholder="Buscar categorías...">
            <button class="btn btn-success" id="btnNuevaCategoria"><i class="fas fa-plus"></i> Añadir Categoría</button>
        </div>

        <!-- Listado de categorías -->
        <div id="categoriasListContainer"></div>

        <!-- Modal para crear o editar una categoría -->
        <div class="modal fade" id="modalCategoria" tabindex="-1" aria-labelledby="modalCategoriaLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCategoriaLabel">Añadir Categoría</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formCategoria">
                            <input type="hidden" id="id_categoria" name="id_categoria">
                            <div class="mb-3">
                                <label for="nombre_categoria" class="form-label">Nombre de la Categoría</label>
                                <input type="text" class="form-control" id="nombre_categoria" name="nombre_categoria" required>
                            </div>
                            <div class="mb-3">
                                <label for="descripcion_categoria" class="form-label">Descripción de la Categoría</label>
                                <textarea class="form-control" id="descripcion_categoria" name="descripcion_categoria" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para crear o editar una subcategoría -->
        <div class="modal fade" id="modalSubcategoria" tabindex="-1" aria-labelledby="modalSubcategoriaLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalSubcategoriaLabel">Añadir Subcategoría</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="formSubcategoria">
                            <input type="hidden" id="id_subcategoria" name="id_subcategoria">
                            <input type="hidden" id="id_categoria" name="id_categoria"> <!-- Verifica que este campo esté correcto -->
                            <div class="mb-3">
                                <label for="nombre_subcategoria" class="form-label">Nombre de la Subcategoría</label>
                                <input type="text" class="form-control" id="nombre_subcategoria" name="nombre_subcategoria" required> <!-- Verifica que el atributo name esté correcto -->
                            </div>
                            <div class="mb-3">
                                <label for="descripcion_subcategoria" class="form-label">Descripción de la Subcategoría</label>
                                <textarea class="form-control" id="descripcion_subcategoria" name="descripcion_subcategoria" rows="3"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Guardar</button>
                        </form>

                    </div>
                </div>
            </div>
        </div>



    </div>

    <!-- jQuery y Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // Función para cargar las categorías
        function cargarCategorias() {
            $.ajax({
                url: 'categorias_list.php',
                method: 'GET',
                success: function(data) {
                    let categoriasHTML = '';
                    if (data.length > 0) {
                        data.forEach(function(categoria) {
                            categoriasHTML += `
                        <div class="card mb-3">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">${categoria.nombre_categoria}</h5>
                                    <p class="card-text">${categoria.descripcion_categoria || 'Sin descripción'}</p>
                                </div>
                                <div>
                                    <button class="btn btn-info btn-sm editarCategoria" data-id="${categoria.id_categoria}">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button class="btn btn-danger btn-sm eliminarCategoria" data-id="${categoria.id_categoria}">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                    <button class="btn btn-secondary btn-sm mostrarSubcategorias" data-id="${categoria.id_categoria}">
                                        <i class="fas fa-chevron-down"></i> Subcategorías
                                    </button>
                                </div>
                            </div>
                            <div class="subcategoriasContainer" id="subcategorias-${categoria.id_categoria}" style="display: none;"></div>
                        </div>`;
                        });
                    } else {
                        categoriasHTML = '<p>No hay categorías disponibles.</p>';
                    }
                    $('#categoriasListContainer').html(categoriasHTML);
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo cargar las categorías.', 'error');
                }
            });
        }

        // Función para abrir el modal de nueva categoría
        $('#btnNuevaCategoria').on('click', function() {
            $('#modalCategoriaLabel').text('Añadir Categoría');
            $('#formCategoria')[0].reset();
            $('#id_categoria').val('');
            $('#modalCategoria').modal('show');
        });

        // Función para guardar una categoría
        $('#formCategoria').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'categorias_controller.php',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (typeof response === 'string') {
                        response = JSON.parse(response); // Parsear a JSON cuando sea necesario
                    }

                    if (response.status === 'success') {
                        Swal.fire('Éxito', response.message, 'success');
                        $('#modalCategoria').modal('hide');
                        cargarCategorias();
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo guardar la categoría.', 'error');
                }
            });
        });

        // Función para manejar la edición de categoría
        $(document).on('click', '.editarCategoria', function() {
            let idCategoria = $(this).data('id');

            $.ajax({
                url: 'categorias_controller.php',
                method: 'GET',
                data: {
                    obtener_categoria: true, // Parámetro para identificar que se está solicitando datos de una categoría
                    id_categoria: idCategoria
                },
                success: function(response) {
                    // Verificar si el response es un objeto JSON
                    if (typeof response === 'string') {
                        try {
                            response = JSON.parse(response); // Parsear a JSON cuando sea necesario
                        } catch (e) {
                            Swal.fire('Error', 'Respuesta inesperada del servidor.', 'error');
                            return;
                        }
                    }

                    if (response.status === 'success') {
                        // Cargar los datos en el formulario
                        $('#id_categoria').val(response.data.id_categoria);
                        $('#nombre_categoria').val(response.data.nombre_categoria);
                        $('#descripcion_categoria').val(response.data.descripcion_categoria);

                        // Cambiar el título del modal y mostrarlo
                        $('#modalCategoriaLabel').text('Editar Categoría');
                        $('#modalCategoria').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo cargar la categoría.', 'error');
                }
            });
        });


        // Función para eliminar una categoría
        $(document).on('click', '.eliminarCategoria', function() {
            let idCategoria = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `categorias_controller.php?eliminar_categoria=${idCategoria}`,
                        method: 'GET',
                        success: function(response) {
                            response = typeof response === 'string' ? JSON.parse(response) : response;

                            if (response.status === 'success') {
                                Swal.fire('Eliminada', response.message, 'success');
                                cargarCategorias();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'No se pudo eliminar la categoría.', 'error');
                        }
                    });
                }
            });
        });

        // Función para cargar subcategorías
        function cargarSubcategorias(idCategoria, subcategoriasContainer) {
            $.ajax({
                url: 'subcategorias_list.php',
                method: 'GET',
                data: {
                    id_categoria: idCategoria // Se pasa el id_categoria al backend
                },
                success: function(data) {
                    let subcategoriasHTML = '';
                    // Se verifica que los datos sean un array y contengan subcategorías
                    if (Array.isArray(data) && data.length > 0) {
                        data.forEach(function(subcategoria) {
                            subcategoriasHTML += `
                <div class="subcategoria-item">
                    ${subcategoria.nombre_subcategoria}
                    <button class="btn btn-warning btn-sm editarSubcategoria" data-id="${subcategoria.id_subcategoria}">
                        <i class="fas fa-edit"></i> Editar
                    </button>
                    <button class="btn btn-danger btn-sm eliminarSubcategoria" data-id="${subcategoria.id_subcategoria}">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>`;
                        });
                    } else {
                        subcategoriasHTML = '<p>No hay subcategorías para esta categoría.</p>';
                    }

                    // Añadir el botón para añadir subcategoría
                    subcategoriasHTML += `
            <div class="mt-3">
                <button class="btn btn-success btn-sm btnAñadirSubcategoria" data-id="${idCategoria}">
                    <i class="fas fa-plus"></i> Añadir Subcategoría
                </button>
            </div>`;

                    subcategoriasContainer.html(subcategoriasHTML); // Se inserta el HTML generado en el contenedor
                },
                error: function() {
                    Swal.fire('Error', 'No se pudieron cargar las subcategorías.', 'error');
                }
            });
        }

        // Función para abrir el modal de añadir nueva subcategoría
        $(document).on('click', '.btnAñadirSubcategoria', function() {
            let idCategoria = $(this).data('id'); // Obtener el id de la categoría seleccionada
            $('#id_categoria').val(idCategoria); // Establecer el id_categoria en el formulario de subcategoría
            $('#id_subcategoria').val(''); // Limpiar el campo id_subcategoria
            $('#nombre_subcategoria').val(''); // Limpiar el nombre de la subcategoría
            $('#descripcion_subcategoria').val(''); // Limpiar la descripción

            // Cambiar el título del modal y mostrarlo
            $('#modalSubcategoriaLabel').text('Añadir Subcategoría');
            $('#modalSubcategoria').modal('show');
        });

        // Función para mostrar el modal de añadir subcategoría
        $(document).on('click', '.mostrarSubcategorias', function() {
            let idCategoria = $(this).data('id'); // Se obtiene el id de la categoría seleccionada
            $('#id_categoria').val(idCategoria); // Se asegura que el id_categoria se pase correctamente al formulario de subcategoría
            let subcategoriasContainer = $(`#subcategorias-${idCategoria}`);

            if (subcategoriasContainer.is(':visible')) {
                subcategoriasContainer.hide();
            } else {
                cargarSubcategorias(idCategoria, subcategoriasContainer); // Llamamos a la función para cargar las subcategorías
                subcategoriasContainer.show();
            }
        });

        // Función para manejar la edición de subcategoría
        $(document).on('click', '.editarSubcategoria', function() {
            let idSubcategoria = $(this).data('id');

            $.ajax({
                url: 'subcategorias_controller.php',
                method: 'GET',
                data: {
                    id_subcategoria: idSubcategoria
                },
                success: function(response) {
                    if (typeof response === 'string') {
                        try {
                            response = JSON.parse(response);
                        } catch (e) {
                            Swal.fire('Error', 'Respuesta inesperada del servidor.', 'error');
                            return;
                        }
                    }

                    if (response.status === 'success') {
                        // Asegúrate de que el ID de la categoría esté correctamente cargado en el formulario
                        $('#id_subcategoria').val(response.data.id_subcategoria);
                        $('#nombre_subcategoria').val(response.data.nombre_subcategoria);
                        $('#descripcion_subcategoria').val(response.data.descripcion_subcategoria);
                        $('#id_categoria').val(response.data.id_categoria); // Asegúrate de que el ID de la categoría esté cargado correctamente

                        // Verificación visual de id_categoria
                        console.log("ID Categoría cargado para la edición:", response.data.id_categoria);

                        // Cambiar el título del modal y mostrarlo
                        $('#modalSubcategoriaLabel').text('Editar Subcategoría');
                        $('#modalSubcategoria').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo cargar la subcategoría.', 'error');
                }
            });
        });

        // Función para guardar una subcategoría (creación o edición)
        $('#formSubcategoria').on('submit', function(e) {
            e.preventDefault();

            let idCategoria = $('#id_categoria').val();
            let nombreSubcategoria = $('#nombre_subcategoria').val();
            let descripcionSubcategoria = $('#descripcion_subcategoria').val();
            let idSubcategoria = $('#id_subcategoria').val();

            // Validación de campos vacíos
            if (!idCategoria || !nombreSubcategoria) {
                Swal.fire('Error', 'El nombre de la subcategoría y la categoría son obligatorios.', 'error');
                return;
            }

            // Realizar la petición AJAX
            $.ajax({
                url: 'subcategorias_controller.php',
                method: 'POST',
                data: {
                    id_subcategoria: idSubcategoria,
                    nombre_subcategoria: nombreSubcategoria,
                    descripcion_subcategoria: descripcionSubcategoria,
                    id_categoria: idCategoria
                },
                success: function(response) {
                    if (typeof response === 'string') {
                        response = JSON.parse(response);
                    }

                    if (response.status === 'success') {
                        Swal.fire('Éxito', response.message, 'success');
                        $('#modalSubcategoria').modal('hide');
                        cargarSubcategorias(idCategoria, $(`#subcategorias-${idCategoria}`)); // Recargar subcategorías
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo guardar la subcategoría.', 'error');
                }
            });
        });

        $(document).on('click', '.eliminarSubcategoria', function() {
            let idSubcategoria = $(this).data('id');
            Swal.fire({
                title: '¿Estás seguro?',
                text: "¡No podrás revertir esto!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `subcategorias_controller.php?eliminar_subcategoria=${idSubcategoria}`,
                        method: 'GET',
                        success: function(response) {
                            // Asegúrate de que response esté siendo interpretado como JSON
                            if (typeof response === 'string') {
                                response = JSON.parse(response);
                            }

                            if (response.status === 'success') {
                                Swal.fire('Eliminada', response.message, 'success');
                                let idCategoria = $(`#subcategorias-${idSubcategoria}`).closest('.card').find('.mostrarSubcategorias').data('id');
                                cargarSubcategorias(idCategoria, $(`#subcategorias-${idCategoria}`));
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'No se pudo eliminar la subcategoría.', 'error');
                        }
                    });
                }
            });
        });

        // Inicializar la carga de categorías
        cargarCategorias();
    </script>
</body>

</html>