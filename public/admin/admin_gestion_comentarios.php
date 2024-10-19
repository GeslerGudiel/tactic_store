<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Comentarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .comentario-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4"><i class="fas fa-comments"></i> Gestionar Comentarios de Productos</h1>

        <!-- Formulario de búsqueda y filtros -->
        <form id="filtros-form" class="row mb-4 g-3">
            <!-- Campo de búsqueda y otros filtros -->
            <div class="col-md-6">
                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control">
            </div>
            <div class="col-md-6">
                <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                <input type="date" name="fecha_fin" id="fecha_fin" class="form-control">
            </div>

            <!-- Botón para aplicar filtros -->
            <div class="col-12 mt-3">
                <button class="btn btn-primary w-100" type="submit"><i class="fas fa-search"></i> Filtrar Comentarios</button>
            </div>
        </form>

        <!-- Contenedor para los comentarios -->
        <div id="comentarios-list"></div>

    </div>

    <script>
        $(document).ready(function () {
            cargarComentarios();

            // Función para cargar los comentarios vía AJAX
            function cargarComentarios() {
                $.ajax({
                    url: "admin_cargar_comentarios_ajax.php",
                    method: "GET",
                    data: $("#filtros-form").serialize(),
                    success: function (data) {
                        $("#comentarios-list").html(data);
                    },
                    error: function () {
                        Swal.fire('Error', 'Hubo un error al cargar los comentarios', 'error');
                    }
                });
            }

            // Al enviar el formulario, cargar los comentarios con los filtros
            $("#filtros-form").on("submit", function (e) {
                e.preventDefault();
                cargarComentarios();
            });

            // Al hacer clic en eliminar o responder se captura el evento y se procesa
            $(document).on("submit", ".responder-form", function (e) {
                e.preventDefault();
                var form = $(this);

                $.ajax({
                    url: form.attr('action'),
                    method: "POST",
                    data: form.serialize(),
                    success: function (response) {
                        Swal.fire('Éxito', 'Respuesta actualizada correctamente', 'success');
                        cargarComentarios(); // Recargar la lista después de responder
                    },
                    error: function () {
                        Swal.fire('Error', 'Hubo un error al actualizar la respuesta', 'error');
                    }
                });
            });

            $(document).on("submit", ".eliminar-form", function (e) {
                e.preventDefault();
                var form = $(this);

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "No podrás deshacer esta acción.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: form.attr('action'),
                            method: "POST",
                            data: form.serialize(),
                            success: function (response) {
                                Swal.fire('Eliminado', 'Comentario eliminado correctamente', 'success');
                                cargarComentarios(); // Recargar la lista después de eliminar
                            },
                            error: function () {
                                Swal.fire('Error', 'Hubo un error al eliminar el comentario', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>

</html>
