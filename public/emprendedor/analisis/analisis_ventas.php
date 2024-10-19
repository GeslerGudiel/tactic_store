<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Ventas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .chart-container {
            margin-top: 30px;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h2><i class="fas fa-chart-line"></i> Análisis de Ventas</h2>

        <!-- Filtros por fecha -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                <input type="date" id="fecha_inicio" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                <input type="date" id="fecha_fin" class="form-control">
            </div>
        </div>

        <!-- Gráfico de Comparación de Ventas -->
        <div class="chart-container">
            <canvas id="graficoComparacion"></canvas>
        </div>

        <!-- Gráfico de Productos Más Vendidos -->
        <div class="chart-container">
            <h3 class="text-center mt-5">Productos Más Vendidos</h3>
            <canvas id="graficoProductosVendidos"></canvas>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let graficoComparacion, graficoProductos;

            // Función para actualizar la gráfica de comparación
            function actualizarGraficoComparacion(data) {
                if (graficoComparacion) {
                    graficoComparacion.destroy();
                }
                const ctx = $('#graficoComparacion');
                graficoComparacion = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Ventas Online', 'Ventas Locales'],
                        datasets: [{
                            label: 'Ingresos',
                            data: [data.total_online, data.total_local],
                            backgroundColor: ['#4e73df', '#1cc88a']
                        }]
                    },
                    options: {
                        responsive: true,
                        scales: {
                            y: {
                                beginAtZero: true
                            }
                        }
                    }
                });
            }

            // Función para actualizar la gráfica de productos más vendidos
            function actualizarGraficoProductos(productos) {
                if (graficoProductos) {
                    graficoProductos.destroy();
                }

                const nombres = productos.map(p => p.nombre_producto);
                const cantidades = productos.map(p => p.cantidad_vendida);

                const ctx = $('#graficoProductosVendidos');
                graficoProductos = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: nombres,
                        datasets: [{
                            label: 'Cantidad Vendida',
                            data: cantidades,
                            backgroundColor: '#36A2EB'
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            },
                            title: {
                                display: true,
                                text: 'Top 10 Productos Más Vendidos'
                            }
                        }
                    }
                });
            }

            // Función para cargar la comparación de ventas
            function cargarComparacionVentas() {
                const fechaInicio = $('#fecha_inicio').val();
                const fechaFin = $('#fecha_fin').val();

                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/analisis/obtener_comparacion_ventas.php',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        fecha_inicio: fechaInicio,
                        fecha_fin: fechaFin
                    },
                    success: function(response) {
                        if (response.error) {
                            Swal.fire('Error', response.error, 'error');
                        } else {
                            actualizarGraficoComparacion(response);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al cargar la comparación de ventas:', xhr.responseText);
                        Swal.fire('Error', 'Hubo un problema al cargar los datos.', 'error');
                    }
                });
            }

            // Función para cargar los productos más vendidos
            function cargarProductosVendidos() {
                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/analisis/obtener_productos_vendidos.php',
                    method: 'GET',
                    dataType: 'json',
                    success: function(response) {
                        if (response.error) {
                            Swal.fire('Error', response.error, 'error');
                        } else {
                            actualizarGraficoProductos(response);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al cargar los productos vendidos:', xhr.responseText);
                        Swal.fire('Error', 'Hubo un problema al cargar los productos.', 'error');
                    }
                });
            }

            // Inicializar las fechas con el mes en curso
            const hoy = new Date();
            const primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
            const ultimoDiaMes = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).toISOString().split('T')[0];

            $('#fecha_inicio').val(primerDiaMes);
            $('#fecha_fin').val(ultimoDiaMes);

            // Cargar datos al iniciar la página
            cargarComparacionVentas();
            cargarProductosVendidos();

            // Recargar datos al cambiar las fechas
            $('#fecha_inicio, #fecha_fin').on('change', function() {
                cargarComparacionVentas();
                cargarProductosVendidos();
            });
        });
    </script>

</body>

</html>