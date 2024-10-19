<?php
//session_start();
include_once '../../src/config/database.php';

// Verificar que el emprendedor esté autenticado
if (!isset($_SESSION['id_emprendedor'])) {
    header("Location: ../auth/login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Resumen del Emprendedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container mt-4">
        <div class="banner">
            <h2>Bienvenido, <?php echo $_SESSION['nombre1'] . ' ' . $_SESSION['apellido1']; ?></h2>

        </div>

        <h2><i class="fas fa-chart-line"></i> Este es tu resumen</h2>

        <!-- Resumen -->
        <div class="row text-center">
            <div class="col-md-3">
                <div class="card text-white bg-success mb-3">
                    <div class="card-body">
                        <h4 class="card-title">Ventas Totales</h4>
                        <p class="card-text"><i class="fas fa-dollar-sign"></i> Q. <span id="ventas_totales">0.00</span></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-info mb-3">
                    <div class="card-body">
                        <h4 class="card-title">Productos Vendidos</h4>
                        <p class="card-text"><i class="fas fa-box"></i> <span id="productos_vendidos">0</span></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-warning mb-3">
                    <div class="card-body">
                        <h4 class="card-title">Ganancia Neta</h4>
                        <p class="card-text"><i class="fas fa-coins"></i> Q. <span id="ganancia_neta">0.00</span></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-white bg-danger mb-3">
                    <div class="card-body">
                        <h4 class="card-title">Productos Bajos en Stock</h4>
                        <p class="card-text"><i class="fas fa-exclamation-triangle"></i> <span id="productos_bajos_stock">0</span></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gráficos de ventas y productos más vendidos -->
        <div class="row">
            <div class="col-md-6">
                <canvas id="ventasPorMes"></canvas>
            </div>
            <div class="col-md-6">
                <canvas id="productosMasVendidos"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Función para cargar los datos del resumen de forma dinámica
        function cargarResumen() {
            $.ajax({
                url: 'resumen_dinamico.php',
                method: 'GET',
                success: function(response) {
                    const data = JSON.parse(response);

                    // Actualizar los valores en la interfaz
                    $('#ventas_totales').text(data.ventas_totales);
                    $('#productos_vendidos').text(data.productos_vendidos);
                    $('#ganancia_neta').text(data.ganancia_neta);
                    $('#productos_bajos_stock').text(data.productos_bajos_stock);
                },
                error: function(error) {
                    console.log('Error al cargar el resumen:', error);
                }
            });
        }

        // Llamar a la función de cargar resumen al cargar la página
        $(document).ready(function() {
            cargarResumen();
        });
    </script>
</body>

</html>