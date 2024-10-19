<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Ventas</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        h2 {
            text-align: center;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .chart-container {
            width: 100%;
            height: 400px;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h2><i class="fas fa-chart-line"></i> Análisis de Ventas</h2>

        <!-- Filtros -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="canal_ventas" class="form-label">Canal de Ventas</label>
                <select id="canal_ventas" class="form-control">
                    <option value="online">Ventas en Línea</option>
                    <option value="local">Ventas Locales</option>
                </select>
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

        <!-- Gráfica de Análisis -->
        <div class="chart-container">
            <canvas id="graficoVentas"></canvas>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            let canalVentas = 'online';
            let fechaInicio = '';
            let fechaFin = '';
            let graficoVentas = null; // Variable global para almacenar la referencia del gráfico

            // Inicializar fechas con el mes actual
            const hoy = new Date();
            const primerDiaMes = new Date(hoy.getFullYear(), hoy.getMonth(), 1).toISOString().split('T')[0];
            const ultimoDiaMes = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0).toISOString().split('T')[0];
            $('#fecha_inicio').val(primerDiaMes);
            $('#fecha_fin').val(ultimoDiaMes);
            fechaInicio = primerDiaMes;
            fechaFin = ultimoDiaMes;

            // Función para cargar los datos y actualizar la gráfica
            function cargarDatos() {
                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/analisis/obtener_analisis.php',
                    method: 'GET',
                    data: {
                        canal: canalVentas,
                        fecha_inicio: fechaInicio,
                        fecha_fin: fechaFin
                    },
                    dataType: 'json',
                    success: function(response) {
                        actualizarGrafico(response);
                    },
                    error: function(xhr, status, error) {
                        Swal.fire('Error', 'No se pudieron cargar los datos de ventas.', 'error');
                    }
                });
            }

            // Función para actualizar la gráfica con los datos obtenidos
            function actualizarGrafico(datos) {
                const ctx = document.getElementById('graficoVentas').getContext('2d');

                // Destruir el gráfico anterior si existe
                if (graficoVentas !== null) {
                    graficoVentas.destroy();
                }

                // Crear el nuevo gráfico
                graficoVentas = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: datos.labels,
                        datasets: [{
                            label: 'Ingresos',
                            data: datos.ingresos,
                            backgroundColor: 'rgba(75, 192, 192, 0.5)',
                            borderColor: 'rgba(75, 192, 192, 1)',
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

            // Evento para cambiar los filtros
            $('#canal_ventas, #fecha_inicio, #fecha_fin').on('change', function() {
                canalVentas = $('#canal_ventas').val();
                fechaInicio = $('#fecha_inicio').val();
                fechaFin = $('#fecha_fin').val();
                cargarDatos();
            });

            // Cargar los datos iniciales al abrir la página
            cargarDatos();
        });
    </script>

</body>

</html>