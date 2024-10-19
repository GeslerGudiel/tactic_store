<?php
session_start();

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    header("Location: ../../../auth/login.php");
    exit;
}

include_once '../../../src/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $id_emprendedor = $_SESSION['id_emprendedor'];

    // Configurar el rango de fechas al mes actual
    $fechaInicio = date('Y-m-01');
    $fechaFin = date('Y-m-d');

    // Consultas para el análisis de ventas en el mes actual
    $queryTopClientes = "SELECT ce.nombre_cliente, COUNT(vl.id_venta_local) AS total_ventas
                         FROM ventas_locales vl
                         JOIN cliente_emprendedor ce ON vl.id_cliente_emprendedor = ce.id_cliente_emprendedor
                         WHERE vl.id_emprendedor = :id_emprendedor
                         AND vl.fecha_venta BETWEEN :fecha_inicio AND :fecha_fin
                         GROUP BY ce.nombre_cliente
                         ORDER BY total_ventas DESC
                         LIMIT 5";
    $stmtTopClientes = $db->prepare($queryTopClientes);
    $stmtTopClientes->bindParam(':id_emprendedor', $id_emprendedor);
    $stmtTopClientes->bindParam(':fecha_inicio', $fechaInicio);
    $stmtTopClientes->bindParam(':fecha_fin', $fechaFin);
    $stmtTopClientes->execute();
    $topClientes = $stmtTopClientes->fetchAll(PDO::FETCH_ASSOC);

    $queryTopProductos = "SELECT p.nombre_producto, SUM(dv.cantidad) AS total_vendido
                          FROM detalle_venta_local dv
                          JOIN producto p ON dv.id_producto = p.id_producto
                          JOIN ventas_locales vl ON dv.id_venta_local = vl.id_venta_local
                          WHERE vl.id_emprendedor = :id_emprendedor
                          AND vl.fecha_venta BETWEEN :fecha_inicio AND :fecha_fin
                          GROUP BY p.nombre_producto
                          ORDER BY total_vendido DESC
                          LIMIT 5";
    $stmtTopProductos = $db->prepare($queryTopProductos);
    $stmtTopProductos->bindParam(':id_emprendedor', $id_emprendedor);
    $stmtTopProductos->bindParam(':fecha_inicio', $fechaInicio);
    $stmtTopProductos->bindParam(':fecha_fin', $fechaFin);
    $stmtTopProductos->execute();
    $topProductos = $stmtTopProductos->fetchAll(PDO::FETCH_ASSOC);

    // Consulta para obtener el Top 5 de Meses con Mayor Venta
    $queryTopMeses = "SELECT DATE_FORMAT(vl.fecha_venta, '%Y-%m') AS mes, SUM(vl.total) AS total_ventas
                      FROM ventas_locales vl
                      WHERE vl.id_emprendedor = :id_emprendedor
                      GROUP BY mes
                      ORDER BY total_ventas DESC
                      LIMIT 5";
    $stmtTopMeses = $db->prepare($queryTopMeses);
    $stmtTopMeses->bindParam(':id_emprendedor', $id_emprendedor);
    $stmtTopMeses->execute();
    $topMeses = $stmtTopMeses->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<p class='text-danger'>Error al cargar el análisis de ventas. Por favor, inténtelo más tarde.</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Análisis de Ventas Locales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

</head>

<body>
    <div class="container my-4">
        <h2 class="text-center">Análisis de Ventas Locales</h2>

        <form id="filtro-form" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio:</label>
                    <input type="text" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($fechaInicio); ?>">
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin" class="form-label">Fecha Fin:</label>
                    <input type="text" id="fecha_fin" name="fecha_fin" class="form-control" value="<?php echo htmlspecialchars($fechaFin); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="aplicar-filtro" class="btn btn-primary w-100">Filtrar</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="limpiar-filtro" class="btn btn-secondary w-100">Limpiar</button>
                </div>
            </div>
        </form>

        <div class="row">
            <div class="col-md-4">
                <h4>Top 5 Clientes (por cantidad de ventas)</h4>
                <ul id="topClientes" class="list-group">
                    <?php if (!empty($topClientes)): ?>
                        <?php foreach ($topClientes as $cliente): ?>
                            <li class="list-group-item">
                                <?php echo htmlspecialchars($cliente['nombre_cliente']); ?>: <?php echo $cliente['total_ventas']; ?> ventas
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item">No hay datos disponibles.</li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-4">
                <h4>Top 5 Productos Vendidos</h4>
                <ul id="topProductos" class="list-group">
                    <?php if (!empty($topProductos)): ?>
                        <?php foreach ($topProductos as $producto): ?>
                            <li class="list-group-item">
                                <?php echo htmlspecialchars($producto['nombre_producto']); ?>: <?php echo $producto['total_vendido']; ?> unidades
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item">No hay datos disponibles.</li>
                    <?php endif; ?>
                </ul>
            </div>


            <div class="col-md-4">
                <h4>Top 5 Meses con Mayor Venta</h4>
                <ul id="topMeses" class="list-group">
                    <?php if (!empty($topMeses)): ?>
                        <?php foreach ($topMeses as $mes): ?>
                            <li class="list-group-item">
                                <?php echo htmlspecialchars($mes['mes']); ?>: Q<?php echo number_format($mes['total_ventas'], 2); ?>
                            </li>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <li class="list-group-item">No hay datos disponibles.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

    <script>
        $(document).ready(function() {
            // Configurar el calendario para las fechas
            $('#fecha_inicio, #fecha_fin').flatpickr({
                dateFormat: 'Y-m-d'
            });

            // Aplicar filtro sin recargar la página
            $('#aplicar-filtro').click(function() {
                const fechaInicio = $('#fecha_inicio').val();
                const fechaFin = $('#fecha_fin').val();

                $.get('analisis_ventas_locales.php', {
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin
                }, function(data) {
                    const newContent = $(data).find('.container').html();
                    $('#content-area .container').html(newContent);
                }).fail(function() {
                    Swal.fire('Error', 'No se pudo cargar el análisis de ventas.', 'error');
                });
            });

            // Limpiar filtro y mostrar el mes actual
            $('#limpiar-filtro').click(function() {
                const fechaInicio = new Date().toISOString().slice(0, 8) + '01';
                const fechaFin = new Date().toISOString().slice(0, 10);
                $('#fecha_inicio').val(fechaInicio);
                $('#fecha_fin').val(fechaFin);

                $('#aplicar-filtro').click();
            });
        });
    </script>
</body>

</html>