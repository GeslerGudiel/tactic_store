<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos de Mis Productos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
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
        <h2><i class="fas fa-box"></i> Pedidos de Mis Productos</h2>

        <!-- Filtros de búsqueda -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="numero_pedido" class="form-label">Número de Pedido</label>
                <input type="text" id="numero_pedido" class="form-control" placeholder="Buscar por número de pedido">
            </div>
            <div class="col-md-4">
                <label for="fecha_inicio" class="form-label">Fecha de inicio</label>
                <input type="date" id="fecha_inicio" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="fecha_fin" class="form-label">Fecha de fin</label>
                <input type="date" id="fecha_fin" class="form-control">
            </div>
        </div>

        <div id="pedidos-container">
            <!-- Aquí se cargarán los de manera dinámica -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Variables globales para almacenar filtros
            let fechaInicio = '';
            let fechaFin = '';
            let numeroPedido = '';

            // Función para cargar pedidos de manera dinámica
            function cargarPedidos() {
                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/pedido/obtener_pedidos.php',
                    method: 'GET',
                    data: {
                        fecha_inicio: fechaInicio,
                        fecha_fin: fechaFin,
                        numero_pedido: numeroPedido
                    },
                    success: function(response) {
                        $('#pedidos-container').html(response);
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar los pedidos:', xhr.responseText);
                        Swal.fire('Error', 'Hubo un problema al cargar los pedidos.', 'error');
                    }
                });
            }

            // Función para obtener las fechas de la semana actual
            function obtenerFechasSemanaActual() {
                const hoy = new Date();
                const primerDia = new Date(hoy.setDate(hoy.getDate() - hoy.getDay() + 1));
                const ultimoDia = new Date(hoy.setDate(hoy.getDate() - hoy.getDay() + 7));
                return {
                    inicio: primerDia.toISOString().split('T')[0],
                    fin: ultimoDia.toISOString().split('T')[0]
                };
            }

            // Inicializar los filtros con las fechas de la semana actual
            const fechas = obtenerFechasSemanaActual();
            fechaInicio = fechas.inicio;
            fechaFin = fechas.fin;
            $('#fecha_inicio').val(fechaInicio);
            $('#fecha_fin').val(fechaFin);

            // Cargar los pedidos al iniciar la página
            cargarPedidos();

            // Actualizar las fechas dinámicamente al cambiar los inputs
            $('#fecha_inicio, #fecha_fin').on('change', function() {
                fechaInicio = $('#fecha_inicio').val();
                fechaFin = $('#fecha_fin').val();

                if (fechaInicio && fechaFin) {
                    cargarPedidos(); // Recargar los pedidos automáticamente
                }
            });

            // Búsqueda dinámica por número de pedido
            $('#numero_pedido').on('input', function() {
                numeroPedido = $(this).val();
                cargarPedidos();
            });

            // Manejar el envío del formulario de subir factura
            $(document).on('submit', '.form-subir-factura', function(e) {
                e.preventDefault(); // Controlamos la recarga de la página

                const formData = new FormData(this);

                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/pedido/subir_factura.php',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.status === 'success') {
                                Swal.fire('Éxito', data.message, 'success');
                                // Recargar los pedidos con el filtro actual
                                cargarPedidos();
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        } catch (error) {
                            console.error('Error al parsear la respuesta:', response);
                            Swal.fire('Error', 'Respuesta inválida del servidor.', 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al subir la factura:', xhr.responseText);
                        Swal.fire('Error', 'No se pudo subir la factura.', 'error');
                    }
                });
            });
        });
    </script>

</body>

</html>