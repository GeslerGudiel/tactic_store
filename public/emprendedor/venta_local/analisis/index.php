<?php
session_start();
if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    header("Location: ../auth/login.php");
    exit;
}

include_once '../../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

// Obtener los meses en los que hubo ventas para el emprendedor
$queryMeses = "SELECT DISTINCT DATE_FORMAT(fecha_venta, '%Y-%m') AS mes 
               FROM ventas_locales 
               WHERE id_emprendedor = :id_emprendedor 
               ORDER BY mes DESC";
$stmtMeses = $db->prepare($queryMeses);
$stmtMeses->bindParam(':id_emprendedor', $id_emprendedor);
$stmtMeses->execute();
$mesesConVentas = $stmtMeses->fetchAll(PDO::FETCH_COLUMN);

// Obtener el mes actual como predeterminado
$filtroMes = isset($_GET['mes']) ? $_GET['mes'] : date('Y-m');
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Ventas Locales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container my-4">
        <h2 class="text-center">Análisis de Ventas Locales</h2>

        <!-- Filtros de mes y categoría -->
        <div class="row mb-4">
            <div class="col-md-6">
                <label for="filtroMes" class="form-label">Seleccionar Mes:</label>
                <select id="filtroMes" class="form-select">
                    <?php
                    if (!empty($mesesConVentas)) {
                        foreach ($mesesConVentas as $mes) {
                            $mesTexto = date('F Y', strtotime($mes . '-01'));
                            $selected = ($mes === $filtroMes) ? 'selected' : '';
                            echo "<option value=\"$mes\" $selected>$mesTexto</option>";
                        }
                    } else {
                        echo "<option value=\"\" disabled>No hay ventas registradas</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="filtroCategoria" class="form-label">Seleccionar Categoría:</label>
                <select id="filtroCategoria" class="form-select">
                    <option value="todas">Todas las Categorías</option>
                    <!-- Opciones de categorías generadas dinámicamente -->
                </select>
            </div>
        </div>

        <!-- Tabla de análisis de ventas -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad Vendida</th>
                        <th>Total Vendido (Q)</th>
                    </tr>
                </thead>
                <tbody id="tablaAnalisisVentas">
                    <!-- Datos de ventas se cargarán dinámicamente aquí -->
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            const currentMonth = $('#filtroMes').val(); // Mes más reciente al cargar la página

            // Función para cargar el análisis de ventas
            function cargarAnalisisVentas(mes = currentMonth, categoria = 'todas') {
                $.get('/comercio_electronico/public/emprendedor/venta_local/analisis/obtener_analisis.php', {
                    mes: mes,
                    categoria: categoria
                }, function(data) {
                    $('#tablaAnalisisVentas').html(data);
                }).fail(function(jqXHR, textStatus, errorThrown) {
                    console.error("Error al cargar el análisis de ventas:", textStatus, errorThrown);
                });
            }

            // Función para cargar las categorías en el select de categorías
            function cargarCategorias() {
                $.get('/comercio_electronico/public/emprendedor/venta_local/analisis/obtener_analisis.php', {
                    categorias: true
                }, function(response) {
                    if (response.success) {
                        const selectCategoria = $('#filtroCategoria');
                        selectCategoria.empty();
                        selectCategoria.append(`<option value="todas">Todas las Categorías</option>`);
                        response.categorias.forEach(categoria => {
                            selectCategoria.append(`<option value="${categoria.id_categoria}">${categoria.nombre_categoria}</option>`);
                        });
                    } else {
                        console.error('Error al cargar las categorías:', response.message);
                    }
                }, 'json');
            }

            // Cargar las categorías y el análisis de ventas para el mes más reciente al cargar la página
            cargarCategorias();
            cargarAnalisisVentas(currentMonth);

            // Evento para recargar el análisis de ventas cuando se cambia el mes o la categoría
            $('#filtroMes').change(function() {
                cargarAnalisisVentas($(this).val(), $('#filtroCategoria').val());
            });

            $('#filtroCategoria').change(function() {
                cargarAnalisisVentas($('#filtroMes').val(), $(this).val());
            });
        });
    </script>
</body>

</html>
