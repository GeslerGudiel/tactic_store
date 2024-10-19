<?php
session_start();

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    header("Location: ../auth/login.php");
    exit;
}

include_once '../../../src/config/database.php';

try {
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

    // Obtener el filtro de mes del request o usar el mes actual por defecto
    $filtroMes = isset($_GET['mes']) ? $_GET['mes'] : date('Y-m');
    $primerDiaMes = $filtroMes . '-01';
    $ultimoDiaMes = date('Y-m-t', strtotime($primerDiaMes));

    // Modificar la consulta para filtrar por el rango de fechas del mes seleccionado
    $query = "SELECT vl.id_venta_local, vl.fecha_venta, vl.total, ce.nombre_cliente 
              FROM ventas_locales vl 
              JOIN cliente_emprendedor ce ON vl.id_cliente_emprendedor = ce.id_cliente_emprendedor 
              WHERE vl.id_emprendedor = :id_emprendedor 
              AND vl.fecha_venta BETWEEN :primer_dia AND :ultimo_dia 
              ORDER BY vl.fecha_venta DESC";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor);
    $stmt->bindParam(':primer_dia', $primerDiaMes);
    $stmt->bindParam(':ultimo_dia', $ultimoDiaMes);
    $stmt->execute();
    $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener el total de ventas del mes filtrado
    $queryTotalVentas = "SELECT SUM(total) AS total_mes 
                        FROM ventas_locales 
                        WHERE id_emprendedor = :id_emprendedor 
                        AND fecha_venta BETWEEN :primer_dia AND :ultimo_dia";
    $stmtTotalVentas = $db->prepare($queryTotalVentas);
    $stmtTotalVentas->bindParam(':id_emprendedor', $id_emprendedor);
    $stmtTotalVentas->bindParam(':primer_dia', $primerDiaMes);
    $stmtTotalVentas->bindParam(':ultimo_dia', $ultimoDiaMes);
    $stmtTotalVentas->execute();
    $totalVentasMes = $stmtTotalVentas->fetchColumn();
} catch (Exception $e) {
    echo "<p class='text-danger'>Error al cargar el historial de ventas. Por favor, inténtelo más tarde.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial de Ventas Locales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="container my-4">
        <h2 class="text-center">Historial de Ventas Locales</h2>

        <div class="alert alert-info text-center">
            <strong>Total de Ventas del Mes: Q<?php echo number_format($totalVentasMes, 2); ?></strong>
        </div>

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
        <div class="table-responsive" id="tablaVentas">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID Venta</th>
                        <th>Fecha de Venta</th>
                        <th>Cliente</th>
                        <th>Total (Q)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($ventas)): ?>
                        <?php foreach ($ventas as $venta): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($venta['id_venta_local']); ?></td>
                                <td><?php echo htmlspecialchars($venta['fecha_venta']); ?></td>
                                <td><?php echo htmlspecialchars($venta['nombre_cliente']); ?></td>
                                <td><?php echo number_format($venta['total'], 2); ?></td>
                                <td>
                                    <button class="btn btn-info btn-sm btn-detalle-venta" data-id="<?php echo $venta['id_venta_local']; ?>">
                                        <i class="fas fa-eye"></i> Ver Detalle
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No hay ventas registradas.</td>
                        </tr>
                    <?php endif; ?>
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
                <div class="modal-body">
                    <div id="detalleVentaContent">
                        <!-- Los detalles se cargarán aquí de manera dinámica -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {

            // Manejar el cambio del filtro de mes usando AJAX
            $('#filtroMes').change(function() {
                const mesSeleccionado = $(this).val();

                $.get('/comercio_electronico/public/emprendedor/venta_local/historial_ventas_locales.php', {
                    mes: mesSeleccionado
                }, function(data) {
                    // Reemplazar el contenido de la tabla con los datos obtenidos
                    const newContent = $(data).find('#tablaVentas').html();
                    $('#tablaVentas').html(newContent);

                    // Reemplazar el total de ventas del mes
                    const newTotalContent = $(data).find('.alert-info').html();
                    $('.alert-info').html(newTotalContent);
                }).fail(function() {
                    Swal.fire('Error', 'No se pudo cargar el historial de ventas.', 'error');
                });
            });

            // Función para cargar los detalles de la venta en el modal
            $(document).on('click', '.btn-detalle-venta', function() {
                const idVenta = $(this).data('id');

                // Hacer la solicitud al servidor para obtener los detalles de la venta
                $.get('/comercio_electronico/public/emprendedor/venta_local/php/detalle_venta_local.php', {
                    id_venta: idVenta
                }, function(data) {
                    // Mostrar los detalles en el contenido del modal
                    $('#detalleVentaContent').html(data);
                    // Mostrar el modal
                    $('#detalleVentaModal').modal('show');
                }).fail(function() {
                    Swal.fire('Error', 'No se pudo cargar el detalle de la venta.', 'error');
                });
            });
        });
    </script>
</body>

</html>