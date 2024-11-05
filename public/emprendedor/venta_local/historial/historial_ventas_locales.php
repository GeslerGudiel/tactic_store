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
$primerDiaMes = date('Y-m-01', strtotime($filtroMes . '-01'));
$ultimoDiaMes = date('Y-m-t', strtotime($filtroMes . '-01'));

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Ventas Locales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container my-4">
        <h2 class="text-center">Historial de Ventas Locales</h2>

        <!-- Filtro de mes -->
        <div class="mb-3">
            <label for="filtroMes" class="form-label">Filtrar por mes:</label>
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

        <!-- Tabla para mostrar las ventas -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID Venta</th>
                        <th>Fecha de Venta</th>
                        <th>Cliente</th>
                        <th>Teléfono</th>
                        <th>Total (Q)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaVentas">
                    <!-- Las ventas se cargarán aquí -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para los detalles de la venta -->
    <div class="modal fade" id="detalleVentaModal" tabindex="-1" aria-labelledby="detalleVentaModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="detalleVentaModalLabel">Detalle de la Venta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="detalleVentaContent">
                    <!-- Los detalles de la venta se cargarán aquí de manera dinámica -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Función para cargar las ventas según el mes seleccionado
            function cargarVentas(mes) {
                $.get('/comercio_electronico/public/emprendedor/venta_local/historial/cargar_ventas.php', {
                    mes: mes
                }, function(data) {
                    $('#tablaVentas').html(data);
                }).fail(function() {
                    alert('No se pudo cargar el historial de ventas');
                });
            }

            // Cargar ventas al cargar la página
            const mesSeleccionado = $('#filtroMes').val();
            cargarVentas(mesSeleccionado);

            // Evento para cargar ventas cuando se cambia el filtro de mes
            $('#filtroMes').change(function() {
                const mes = $(this).val();
                cargarVentas(mes);
            });

            // Evento para abrir el modal de detalles de la venta
            $(document).on('click', '.btn-detalle-venta', function() {
                const idVenta = $(this).data('id');

                // Solicitar los detalles de la venta
                $.get('/comercio_electronico/public/emprendedor/venta_local/historial/detalle_venta.php', {
                    id_venta: idVenta
                }, function(data) {
                    $('#detalleVentaContent').html(data); // Insertar los detalles en el modal
                    $('#detalleVentaModal').modal('show'); // Mostrar el modal
                }).fail(function() {
                    Swal.fire('Error', 'No se pudo cargar el detalle de la venta.', 'error');
                });
            });
        });
    </script>
</body>

</html>