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

        <!-- Nueva Sección: Gráfica de Ingresos por Categorías -->
        <div class="container mt-5">
            <h2><i class="fas fa-tags"></i> Ingresos por Categoría</h2>
            <canvas id="graficoCategorias"></canvas>
        </div>
    </div>

    <div class="container mt-5">
        <h2><i class="fas fa-calendar-alt"></i> Comparación de Ventas entre Períodos</h2>

        <div class="row mb-3">
            <div class="col-md-3">
                <label for="periodo1_inicio" class="form-label">Inicio Período 1</label>
                <input type="date" id="periodo1_inicio" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="periodo1_fin" class="form-label">Fin Período 1</label>
                <input type="date" id="periodo1_fin" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="periodo2_inicio" class="form-label">Inicio Período 2</label>
                <input type="date" id="periodo2_inicio" class="form-control">
            </div>
            <div class="col-md-3">
                <label for="periodo2_fin" class="form-label">Fin Período 2</label>
                <input type="date" id="periodo2_fin" class="form-control">
            </div>
        </div>

        <canvas id="graficoPeriodos"></canvas>

        <div class="container mt-5">

            <h2><i class="fas fa-chart-bar"></i> Productos Más Vendidos</h2>

            <div id="productosMasVendidosContainer">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Total Vendido</th>
                            <th>Ingresos</th>
                        </tr>
                    </thead>
                    <tbody id="productosMasVendidos">
                        <!-- Aquí se cargarán los productos más vendidos -->
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Productos más vendidos en línea -->
        <div class="container mt-5">
            <h2><i class="fas fa-tags"></i> Productos más vendidos en línea</h2>
            <canvas id="graficoProductosOnline"></canvas>
        </div>

        <!-- Productos más vendidos localmente -->
        <div class="container mt-5">
            <h2><i class="fas fa-tags"></i> Productos más vendidos localmente</h2>
            <canvas id="graficoProductosLocales"></canvas>
        </div>
    </div>

    <!-- Productos más vendidos en de manera general -->
    <div class="container mt-5">
        <h2><i class="fas fa-tags"></i> Productos más vendidos de manera general</h2>
        <canvas id="graficoProductosCombinados"></canvas>
    </div>

    <div class="container mt-5">
        <h2><i class="fas fa-coins"></i> Análisis de Comisiones</h2>

        <div class="row">
            <div class="col-md-6">
                <div class="alert alert-success">
                    <strong>Total Pagado:</strong> Q. <span id="total-pagado">0.00</span>
                </div>
            </div>
            <div class="col-md-6">
                <div class="alert alert-warning">
                    <strong>Total Pendiente:</strong> Q. <span id="total-pendiente">0.00</span>
                </div>
            </div>
        </div>
    </div>
    </div>
    
    <script>
        $(document).ready(function() {
            let graficoComparacion, graficoProductos, graficoCategorias,
                graficoPeriodos, graficoProductosOnline, graficoProductosLocales, graficoProductosCombinados;

            // Función para actualizar la gráfica combinada
            function actualizarGraficoProductosCombinados(data) {
                if (graficoProductosCombinados) {
                    graficoProductosCombinados.destroy(); // Destruir la gráfica anterior
                }

                const etiquetas = data.map(item => item.nombre_producto); // Extraer etiquetas
                const valores = data.map(item => item.cantidad_total); // Extraer valores
                const backgroundColors = valores.map(() => '#' + Math.floor(Math.random() * 16777215).toString(16));

                const ctx = document.getElementById('graficoProductosCombinados').getContext('2d');
                graficoProductosCombinados = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: etiquetas,
                        datasets: [{
                            label: 'Productos Más Vendidos (Combinado)',
                            data: valores,
                            backgroundColor: backgroundColors,
                            borderColor: '#17a673',
                            borderWidth: 1
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

            // Función para cargar los productos más vendidos combinando ambas ventas
            function cargarProductosCombinados() {
                const fechaInicio = $('#fecha_inicio').val();
                const fechaFin = $('#fecha_fin').val();

                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/analisis/obtener_productos_mas_vendidos2.php',
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
                            actualizarGraficoProductosCombinados(response);
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al cargar los productos combinados:', xhr.responseText);
                        Swal.fire('Error', 'Hubo un problema al cargar los productos combinados.', 'error');
                    }
                });
            }

            // Función para cargar las comisiones
            function cargarComisiones() {
                const fechaInicio = $('#fecha_inicio').val();
                const fechaFin = $('#fecha_fin').val();

                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/analisis/obtener_comisiones.php',
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
                            $('#total-pagado').text(parseFloat(response.total_pagado).toFixed(2));
                            $('#total-pendiente').text(parseFloat(response.total_pendiente).toFixed(2));
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar las comisiones:', xhr.responseText);
                        Swal.fire('Error', 'Hubo un problema al cargar las comisiones.', 'error');
                    }
                });
            }

            // Función para cargar los productos más vendidos
            function cargarProductosMasVendidos() {
                const fechaInicio = $('#fecha_inicio').val();
                const fechaFin = $('#fecha_fin').val();

                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/analisis/obtener_productos_mas_vendidos.php',
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
                            let rows = '';
                            response.forEach(producto => {
                                rows += `
                                <tr>
                                    <td>${producto.nombre_producto}</td>
                                    <td>${producto.total_vendido}</td>
                                    <td>Q. ${parseFloat(producto.total_ingresos).toFixed(2)}</td>
                                </tr>`;
                            });
                            $('#productosMasVendidos').html(rows);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar los productos más vendidos:', xhr.responseText);
                        Swal.fire('Error', 'Hubo un problema al cargar los productos.', 'error');
                    }
                });
            }

            function actualizarGraficoPeriodos(data) {
                if (graficoPeriodos) {
                    graficoPeriodos.destroy(); // Destruir la gráfica anterior si existe
                }

                const ctx = $('#graficoPeriodos');
                graficoPeriodos = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Período 1', 'Período 2'],
                        datasets: [{
                            label: 'Ingresos',
                            data: [data.periodo1, data.periodo2],
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

            function cargarDatosPeriodos() {
                const periodo1Inicio = $('#periodo1_inicio').val();
                const periodo1Fin = $('#periodo1_fin').val();
                const periodo2Inicio = $('#periodo2_inicio').val();
                const periodo2Fin = $('#periodo2_fin').val();

                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/analisis/obtener_comparacion_periodos.php',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        periodo1_inicio: periodo1Inicio,
                        periodo1_fin: periodo1Fin,
                        periodo2_inicio: periodo2Inicio,
                        periodo2_fin: periodo2Fin
                    },
                    success: function(response) {
                        if (response.error) {
                            Swal.fire('Error', response.error, 'error');
                        } else {
                            actualizarGraficoPeriodos(response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar los datos:', xhr.responseText);
                        Swal.fire('Error', 'Hubo un problema al cargar los datos.', 'error');
                    }
                });
            }

            function actualizarGraficoCategorias(data) {
                if (graficoCategorias) {
                    graficoCategorias.destroy(); // Destruir gráfica anterior si existe
                }

                const ctx = $('#graficoCategorias');
                const categorias = data.online.map(item => item.nombre_categoria);
                const ingresosOnline = data.online.map(item => item.total_ingresos);
                const ingresosLocal = data.local.map(item => item.total_ingresos);

                graficoCategorias = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: categorias,
                        datasets: [{
                                label: 'Ventas Online',
                                data: ingresosOnline,
                                backgroundColor: '#4e73df'
                            },
                            {
                                label: 'Ventas Locales',
                                data: ingresosLocal,
                                backgroundColor: '#1cc88a'
                            }
                        ]
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

            function cargarDatosCategorias() {
                const fechaInicio = $('#fecha_inicio').val();
                const fechaFin = $('#fecha_fin').val();

                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/analisis/obtener_ingresos_categorias.php',
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
                            actualizarGraficoCategorias(response);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error al cargar los datos:', xhr.responseText);
                        Swal.fire('Error', 'Hubo un problema al cargar los datos.', 'error');
                    }
                });
            }



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

            function actualizarGraficoProductosOnline(data, titulo = 'Productos Más Vendidos') {
                if (graficoProductosOnline) {
                    graficoProductosOnline.destroy(); // Destruir la gráfica anterior
                }

                const etiquetas = data.map(item => item.nombre_producto); // Extraer etiquetas
                const valores = data.map(item => item.cantidad_vendida); // Extraer valores
                const backgroundColors = valores.map(() => '#' + Math.floor(Math.random() * 16777215).toString(16));

                const ctx = document.getElementById('graficoProductosOnline').getContext('2d');
                graficoProductosOnline = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: etiquetas,
                        datasets: [{
                            label: titulo,
                            data: valores,
                            backgroundColor: backgroundColors,
                            borderColor: '#17a673',
                            borderWidth: 1
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

            // Función para cargar los productos más vendidos en línea
            function cargarProductosOnline() {
                const fechaInicio = $('#fecha_inicio').val();
                const fechaFin = $('#fecha_fin').val();

                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/analisis/obtener_productos_vendidos_online.php',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        fecha_inicio: fechaInicio,
                        fecha_fin: fechaFin
                    },
                    success: function(response) {
                        console.log(response); // Verifica que los datos sean correctos

                        if (response.error) {
                            Swal.fire('Error', response.error, 'error');
                        } else {
                            actualizarGraficoProductosOnline(response, 'Productos Vendidos en Línea');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al cargar los productos en línea:', xhr.responseText);
                        Swal.fire('Error', 'Hubo un problema al cargar los productos en línea.', 'error');
                    }
                });
            }


            function actualizarGraficoProductosLocales(data, titulo = 'Productos Más Vendidos') {
                if (graficoProductosLocales) {
                    graficoProductosLocales.destroy(); // Destruir la gráfica anterior
                }

                const etiquetas = data.map(item => item.nombre_producto); // Extraer etiquetas
                const valores = data.map(item => item.cantidad_vendida); // Extraer valores
                const backgroundColors = valores.map(() => '#' + Math.floor(Math.random() * 16777215).toString(16));

                const ctx = document.getElementById('graficoProductosLocales').getContext('2d');
                graficoProductosLocales = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: etiquetas,
                        datasets: [{
                            label: titulo,
                            data: valores,
                            backgroundColor: backgroundColors,
                            borderColor: '#17a673',
                            borderWidth: 1
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

            // Función para cargar los productos más vendidos localmente
            function cargarProductosLocales() {
                const fechaInicio = $('#fecha_inicio').val();
                const fechaFin = $('#fecha_fin').val();

                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/analisis/obtener_productos_vendidos_locales.php',
                    method: 'GET',
                    dataType: 'json',
                    data: {
                        fecha_inicio: fechaInicio,
                        fecha_fin: fechaFin
                    },
                    success: function(response) {
                        console.log(response); // Verificar datos recibidos en consola

                        if (response.error) {
                            Swal.fire('Error', response.error, 'error');
                        } else {
                            actualizarGraficoProductosLocales(response, 'Productos Vendidos Localmente');
                        }
                    },
                    error: function(xhr) {
                        console.error('Error al cargar los productos locales:', xhr.responseText);
                        Swal.fire('Error', 'Hubo un problema al cargar los productos locales.', 'error');
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

            // Función para cargar los productos más vendidos
            function cargarProductosVendidos() {
                const fechaInicio = $('#fecha_inicio').val();
                const fechaFin = $('#fecha_fin').val();

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

            // Cargar datos al cambiar las fechas
            $('#periodo1_inicio, #periodo1_fin, #periodo2_inicio, #periodo2_fin').on('change', cargarDatosPeriodos);

            // Cargar datos al cambiar fechas
            $('#fecha_inicio, #fecha_fin').on('change', cargarDatosCategorias);

            // Cargar datos al cambiar fechas
            $('#fecha_inicio, #fecha_fin').on('change', cargarProductosVendidos);

            // Cargar productos al cambiar las fechas
            $('#fecha_inicio, #fecha_fin').on('change', cargarProductosMasVendidos);

            // Recargar las comisiones al cambiar las fechas
            $('#fecha_inicio, #fecha_fin').on('change', cargarComisiones);

            // Recargar las comisiones al cambiar las fechas
            $('#fecha_inicio, #fecha_fin').on('change', cargarProductosOnline);

            // Recargar las comisiones al cambiar las fechas
            $('#fecha_inicio, #fecha_fin').on('change', cargarProductosLocales);

            // Recargar datos al cambiar las fechas
            $('#fecha_inicio, #fecha_fin').on('change', cargarProductosCombinados);

            // Inicializar las fechas con el mes en curso
            const hoy = new Date();
            const primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
            const ultimoDiaMes = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).toISOString().split('T')[0];

            const hace7Dias = new Date(hoy.setDate(hoy.getDate() - 7)).toISOString().split('T')[0];
            const hoyISO = new Date().toISOString().split('T')[0];

            $('#periodo1_inicio').val(hace7Dias);
            $('#periodo1_fin').val(hoyISO);
            $('#periodo2_inicio').val(hace7Dias);
            $('#periodo2_fin').val(hoyISO);

            $('#fecha_inicio').val(primerDiaMes);
            $('#fecha_fin').val(ultimoDiaMes);

            // Cargar datos al iniciar la página
            cargarComparacionVentas();
            cargarProductosVendidos();
            cargarDatosCategorias();
            cargarDatosPeriodos();
            cargarProductosMasVendidos();
            cargarComisiones();
            cargarProductosLocales();
            cargarProductosOnline();
            cargarProductosCombinados();

            // Recargar datos al cambiar las fechas
            $('#fecha_inicio, #fecha_fin').on('change', function() {
                cargarComparacionVentas();
                cargarProductosVendidos();
                cargarDatosCategorias();
                cargarProductosMasVendidos();
                cargarComisiones();
                cargarProductosLocales();
                cargarProductosOnline();
                cargarProductosCombinados();
            });

            // Cargar datos al cambiar las fechas
            $('#periodo1_inicio, #periodo1_fin, #periodo2_inicio, #periodo2_fin').on('change', cargarDatosPeriodos);

        });
    </script>

</body>

</html>