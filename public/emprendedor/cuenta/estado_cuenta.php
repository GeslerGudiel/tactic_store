<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Cuenta</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <style>
        .summary-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        table thead th {
            background-color: #343a40;
            color: white;
            text-align: center;
        }

        table tbody td {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h1><i class="fas fa-file-invoice-dollar"></i> Estado de Cuenta</h1>

        <!-- Filtros -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="numero_pedido" class="form-label">Número de Pedido</label>
                <input type="text" id="numero_pedido" class="form-control" placeholder="Buscar por número de pedido">
            </div>
            <div class="col-md-4">
                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                <input type="date" id="fecha_inicio" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                <input type="date" id="fecha_fin" class="form-control">
            </div>
        </div>

        <!-- Resumen de Comisiones -->
        <div id="summary-box" class="summary-box">
            <h3><i class="fas fa-coins"></i> Resumen de Comisiones</h3>
            <p><strong>Total Comisiones:</strong> Q. <span id="total-comisiones">0.00</span></p>
            <p><strong>Total Pagadas:</strong> Q. <span id="total-pagadas">0.00</span></p>
            <p><strong>Total Pendientes:</strong> Q. <span id="total-pendientes">0.00</span></p>
            <p><strong>Total Productos Vendidos:</strong> Q. <span id="total-productos">0.00</span></p>
        </div>

        <!-- Tabla de Comisiones -->
        <table class="table table-bordered mt-3" id="comisiones-table">
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Fecha Pedido</th>
                    <th>Monto Pedido</th>
                    <th>Monto Comisión</th>
                    <th>Estado Comisión</th>
                    <th>Fecha Pago</th>
                    <th>Comprobante de Pago</th>
                </tr>
            </thead>
            <tbody>
                <!-- Aquí se cargarán las comisiones de manera dinámica -->
            </tbody>
        </table>
    </div>

    <script>
        $(document).ready(function () {
            // Obtener fechas de la semana actual
            function obtenerFechasSemanaActual() {
                const hoy = new Date();
                const primerDia = new Date(hoy.setDate(hoy.getDate() - hoy.getDay() + 1));
                const ultimoDia = new Date(hoy.setDate(hoy.getDate() - hoy.getDay() + 7));
                return {
                    inicio: primerDia.toISOString().split('T')[0],
                    fin: ultimoDia.toISOString().split('T')[0]
                };
            }

            // Inicializar fechas en los campos de filtro
            const fechas = obtenerFechasSemanaActual();
            $('#fecha_inicio').val(fechas.inicio);
            $('#fecha_fin').val(fechas.fin);

            // Función para cargar el estado de cuenta
            function cargarEstadoCuenta() {
                const numeroPedido = $('#numero_pedido').val();
                const fechaInicio = $('#fecha_inicio').val();
                const fechaFin = $('#fecha_fin').val();

                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/cuenta/obtener_estado_cuenta.php',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        numero_pedido: numeroPedido,
                        fecha_inicio: fechaInicio,
                        fecha_fin: fechaFin
                    },
                    success: function (response) {
                        if (response.error) {
                            Swal.fire('Error', response.error, 'error');
                            return;
                        }

                        let totalComisiones = 0;
                        let totalPagadas = 0;
                        let totalPendientes = 0;
                        let totalProductos = 0;
                        let rows = '';

                        response.forEach(comision => {
                            totalComisiones += parseFloat(comision.total_comision);
                            totalProductos += parseFloat(comision.total_productos_vendidos);

                            if (comision.estado_comision === 'Pagada') {
                                totalPagadas += parseFloat(comision.total_comision);
                            } else {
                                totalPendientes += parseFloat(comision.total_comision);
                            }

                            rows += `
                                <tr>
                                    <td>${comision.id_pedido}</td>
                                    <td>${comision.fecha_pedido}</td>
                                    <td>Q. ${parseFloat(comision.total_productos_vendidos).toFixed(2)}</td>
                                    <td>Q. ${parseFloat(comision.total_comision).toFixed(2)}</td>
                                    <td>${comision.estado_comision}</td>
                                    <td>${comision.fecha_pago || 'Pendiente'}</td>
                                    <td>
                                        ${comision.comprobante_pago 
                                            ? `<a href="../../uploads/comprobantes_comision/${comision.comprobante_pago}" target="_blank">Ver Comprobante</a>` 
                                            : '<span class="text-danger">No disponible</span>'}
                                    </td>
                                </tr>
                            `;
                        });

                        $('#total-comisiones').text(totalComisiones.toFixed(2));
                        $('#total-pagadas').text(totalPagadas.toFixed(2));
                        $('#total-pendientes').text(totalPendientes.toFixed(2));
                        $('#total-productos').text(totalProductos.toFixed(2));
                        $('#comisiones-table tbody').html(rows);
                    },
                    error: function (xhr, status, error) {
                        console.error('Error al cargar el estado de cuenta:', xhr.responseText);
                        Swal.fire('Error', 'Hubo un problema al cargar el estado de cuenta.', 'error');
                    }
                });
            }

            // Cargar el estado de cuenta al iniciar la página
            cargarEstadoCuenta();

            // Filtrar al cambiar el número de pedido o las fechas
            $('#numero_pedido, #fecha_inicio, #fecha_fin').on('input change', function () {
                cargarEstadoCuenta();
            });
        });
    </script>
</body>

</html>
