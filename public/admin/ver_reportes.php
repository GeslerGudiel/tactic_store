<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener la cantidad de emprendedores
$query_emprendedores = "SELECT COUNT(*) AS total_emprendedores FROM emprendedor";
$stmt_emprendedores = $db->prepare($query_emprendedores);
$stmt_emprendedores->execute();
$total_emprendedores = $stmt_emprendedores->fetch(PDO::FETCH_ASSOC)['total_emprendedores'];

// Obtener la cantidad de clientes
$query_clientes = "SELECT COUNT(*) AS total_clientes FROM cliente";
$stmt_clientes = $db->prepare($query_clientes);
$stmt_clientes->execute();
$total_clientes = $stmt_clientes->fetch(PDO::FETCH_ASSOC)['total_clientes'];

// Obtener productos más vendidos
$query_productos = "SELECT p.nombre_producto, COUNT(dp.id_producto) AS total_vendidos 
                    FROM producto p
                    JOIN detalle_pedido dp ON p.id_producto = dp.id_producto
                    GROUP BY p.nombre_producto
                    ORDER BY total_vendidos DESC
                    LIMIT 5";
$stmt_productos = $db->prepare($query_productos);
$stmt_productos->execute();
$productos_mas_vendidos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

// Obtener la cantidad de suscripciones activas
$query_suscripciones = "SELECT COUNT(*) AS total_suscripciones FROM suscripcion WHERE estado = 'activo'";
$stmt_suscripciones = $db->prepare($query_suscripciones);
$stmt_suscripciones->execute();
$total_suscripciones_activas = $stmt_suscripciones->fetch(PDO::FETCH_ASSOC)['total_suscripciones'];

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="container my-5">
        <h2 class="text-center mb-4"><i class="fas fa-chart-bar"></i> Reportes del Administrador</h2>

        <!-- Botón para descargar el reporte en CSV -->
        <div class="text-center mb-4">
            <a href="exportar_reportes_csv.php" class="btn btn-success">
                <i class="fas fa-file-csv"></i> Descargar Reporte en CSV
            </a>
        </div>

        <div class="text-center mb-4">
            <a href="exportar_reportes_pdf.php" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Descargar Reporte en PDF
            </a>
        </div>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h4><i class="fas fa-user-tie"></i> Total de Emprendedores</h4>
                        <p class="display-4"><?php echo $total_emprendedores; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card text-center">
                    <div class="card-body">
                        <h4><i class="fas fa-user"></i> Total de Clientes</h4>
                        <p class="display-4"><?php echo $total_clientes; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5><i class="fas fa-chart-line"></i> Productos más vendidos</h5>
            </div>
            <div class="card-body">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th><i class="fas fa-box"></i> Producto</th>
                            <th><i class="fas fa-shopping-basket"></i> Total Vendidos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos_mas_vendidos as $producto): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                                <td><?php echo htmlspecialchars($producto['total_vendidos']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card text-center">
            <div class="card-body">
                <h4><i class="fas fa-tags"></i> Total de Suscripciones Activas</h4>
                <p class="display-4"><?php echo $total_suscripciones_activas; ?></p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
