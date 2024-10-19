<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Categorías y Subcategorías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container mt-5">
        <h3><i class="fas fa-tag"></i> Gestionar Categorías</h3>

        <!-- Formulario para crear o editar una categoría -->
        <form id="formCategoria" method="POST">
            <input type="hidden" id="id_categoria" name="id_categoria">
            <div class="mb-3">
                <label for="nombre_categoria" class="form-label">Nombre de la Categoría</label>
                <input type="text" class="form-control" id="nombre_categoria" name="nombre_categoria" required>
            </div>
            <div class="mb-3">
                <label for="descripcion_categoria" class="form-label">Descripción de la Categoría</label>
                <textarea class="form-control" id="descripcion_categoria" name="descripcion_categoria" rows="3"></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Guardar Categoría</button>
            <button type="button" id="btnCancelar" class="btn btn-secondary" style="display: none;"><i class="fas fa-times"></i> Cancelar</button>
        </form>

        <!-- Listado de categorías -->
        <div id="categoriaList" class="mt-5">
            <h4><i class="fas fa-folder-open"></i> Categorías</h4>
            <div id="categoriasListContainer"></div>
        </div>
    </div>

    <!-- jQuery y Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Cargar las categorías
        function cargarCategorias() {
            $.ajax({
                url: 'categorias_list.php',
                method: 'GET',
                success: function(data) {
                    let categoriasHTML = '';
                    if (data.length > 0) {
                        data.forEach(function(categoria) {
                            categoriasHTML += `
                                <div class="categoria-item p-2 border-bottom" data-id="${categoria.id_categoria}">
                                    <i class="fas fa-tag"></i> ${categoria.nombre_categoria}
                                    <button class="btn btn-sm btn-warning float-end editar-categoria" data-id="${categoria.id_categoria}" data-nombre="${categoria.nombre_categoria}" data-descripcion="${categoria.descripcion_categoria}">
                                        <i class="fas fa-edit"></i> Editar
                                    </button>
                                    <button class="btn btn-sm btn-danger float-end eliminar-categoria me-2" data-id="${categoria.id_categoria}">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </div>`;
                        });
                    } else {
                        categoriasHTML = '<p>No hay categorías registradas.</p>';
                    }
                    $('#categoriasListContainer').html(categoriasHTML);
                },
                error: function() {
                    Swal.fire('Error', 'No se pudieron cargar las categorías', 'error');
                }
            });
        }

        // Función para cargar los datos de una categoría al formulario
        $(document).on('click', '.editar-categoria', function() {
            const id = $(this).data('id');
            const nombre = $(this).data('nombre');
            const descripcion = $(this).data('descripcion');

            // Llenar el formulario con los datos de la categoría seleccionada
            $('#id_categoria').val(id);
            $('#nombre_categoria').val(nombre);
            $('#descripcion_categoria').val(descripcion);
            $('#btnCancelar').show(); // Mostrar botón de cancelar
        });

        // Función para cancelar la edición de una categoría
        $('#btnCancelar').on('click', function() {
            $('#formCategoria')[0].reset(); // Reiniciar el formulario
            $('#id_categoria').val(''); // Vaciar el campo oculto de ID
            $('#btnCancelar').hide(); // Ocultar botón de cancelar
        });

        // Cuando se envía el formulario de categoría
        $('#formCategoria').on('submit', function(e) {
            e.preventDefault();
            $.ajax({
                url: 'categorias_controller.php',
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        Swal.fire('Éxito', response.message, 'success');
                        cargarCategorias(); // Recargar la lista de categorías
                        $('#formCategoria')[0].reset(); // Reiniciar el formulario
                        $('#id_categoria').val(''); // Vaciar el campo oculto de ID
                        $('#btnCancelar').hide(); // Ocultar botón de cancelar
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo guardar la categoría', 'error');
                }
            });
        });

        // Función para eliminar una categoría
        $(document).on('click', '.eliminar-categoria', function() {
            const id = $(this).data('id');

            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'categorias_controller.php?eliminar_categoria=' + id,
                        method: 'GET',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire('Eliminada', response.message, 'success');
                                cargarCategorias(); // Recargar la lista de categorías
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'No se pudo eliminar la categoría', 'error');
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