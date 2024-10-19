<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentarios de Productos</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        .comentario-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .table thead th {
            background-color: #343a40;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h2>Comentarios de tus Productos</h2>

        <!-- Filtros de Fecha -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                <input type="date" id="fecha_inicio" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="fecha_fin" class="form-label">Fecha Fin</label>
                <input type="date" id="fecha_fin" class="form-control">
            </div>
        </div>

        <div id="comentarios-container">
            <!-- Aquí se cargarán los comentarios mediante AJAX -->
        </div>
    </div>

    <script>
        $(document).ready(function () {
            let fechaInicio = '';
            let fechaFin = '';

            // Obtener fechas del mes en curso
            function obtenerFechasMesActual() {
                const fechaActual = new Date();
                const primerDia = new Date(fechaActual.getFullYear(), fechaActual.getMonth(), 1);
                const ultimoDia = new Date(fechaActual.getFullYear(), fechaActual.getMonth() + 1, 0);

                return {
                    inicio: primerDia.toISOString().split('T')[0],
                    fin: ultimoDia.toISOString().split('T')[0]
                };
            }

            // Inicializar las fechas al mes en curso
            const fechas = obtenerFechasMesActual();
            fechaInicio = fechas.inicio;
            fechaFin = fechas.fin;
            $('#fecha_inicio').val(fechaInicio);
            $('#fecha_fin').val(fechaFin);

            // Cargar comentarios con AJAX
            function cargarComentarios() {
                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/comentario/obtener_comentarios.php',
                    method: 'GET',
                    data: {
                        fecha_inicio: fechaInicio,
                        fecha_fin: fechaFin
                    },
                    success: function (response) {
                        $('#comentarios-container').html(response);
                    },
                    error: function (xhr, status, error) {
                        console.error('Error al cargar comentarios:', xhr.responseText);
                        Swal.fire('Error', 'No se pudieron cargar los comentarios.', 'error');
                    }
                });
            }

            // Cargar los comentarios al iniciar la página
            cargarComentarios();

            // Detectar cambios en las fechas e invocar cargarComentarios automáticamente
            $('#fecha_inicio, #fecha_fin').on('change', function () {
                fechaInicio = $('#fecha_inicio').val();
                fechaFin = $('#fecha_fin').val();

                if (fechaInicio && fechaFin) {
                    cargarComentarios(); // Recargar los comentarios automáticamente
                }
            });
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
