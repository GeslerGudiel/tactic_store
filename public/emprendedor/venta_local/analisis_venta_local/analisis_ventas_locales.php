<?php
session_start();

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    header("Location: ../../../auth/login.php");
    exit;
}

include_once '../../../../src/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $id_emprendedor = $_SESSION['id_emprendedor'];

    // Consulta para el Top 5 de Meses con Mayor Venta y Conteo de Ventas
    $queryTopMeses = "SELECT DATE_FORMAT(vl.fecha_venta, '%Y-%m') AS mes, 
                             COUNT(vl.id_venta_local) AS cantidad_ventas, 
                             SUM(vl.total) AS total_ventas
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
</head>

<body>
    <div class="container my-4">
        <h2 class="text-center">Análisis de Ventas Locales</h2>

        <!-- Formulario de Filtro (Siempre Visible) -->
        <form id="filtro-form" class="mb-4">
            <div class="row">
                <div class="col-md-4">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio:</label>
                    <input type="date" id="fecha_inicio" class="form-control" value="<?= date('Y-m-01') ?>">
                </div>
                <div class="col-md-4">
                    <label for="fecha_fin" class="form-label">Fecha Fin:</label>
                    <input type="date" id="fecha_fin" class="form-control" value="<?= date('Y-m-d') ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="aplicar-filtro" class="btn btn-primary w-100">Filtrar</button>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" id="limpiar-filtro" class="btn btn-secondary w-100">Limpiar</button>
                </div>
            </div>
        </form>

        <!-- Área de Resultados Dinámicos -->
        <div id="resultado-analisis">
            <!-- Aquí se cargará el contenido dinámico -->
        </div>

        <!-- Top 5 Meses con Mayor Venta (Siempre Visible) -->
        <div class="mt-5">
            <h4>Top 5 Meses con Mayor Venta</h4>
            <ul class="list-group">
                <?php foreach ($topMeses as $mes): ?>
                    <li class="list-group-item">
                        <?= htmlspecialchars($mes['mes']) ?>: 
                        <?= $mes['cantidad_ventas'] ?> ventas - 
                        Q<?= number_format($mes['total_ventas'], 2) ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="/comercio_electronico/public/emprendedor/venta_local/analisis_venta_local/analisis_ventas_locales.js"></script>
</body>
</html>
