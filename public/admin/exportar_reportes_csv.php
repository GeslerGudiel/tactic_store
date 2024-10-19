<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Consultar datos para el reporte
$query_emprendedores = "SELECT COUNT(*) AS total_emprendedores FROM emprendedor";
$query_clientes = "SELECT COUNT(*) AS total_clientes FROM cliente";
$query_productos = "SELECT p.nombre_producto, COUNT(dp.id_producto) AS total_vendidos 
                    FROM producto p
                    JOIN detalle_pedido dp ON p.id_producto = dp.id_producto
                    GROUP BY p.nombre_producto
                    ORDER BY total_vendidos DESC";
$query_suscripciones = "SELECT COUNT(*) AS total_suscripciones FROM suscripcion WHERE estado = 'activo'";

try {
    $stmt_emprendedores = $db->prepare($query_emprendedores);
    $stmt_emprendedores->execute();
    $total_emprendedores = $stmt_emprendedores->fetch(PDO::FETCH_ASSOC)['total_emprendedores'];

    $stmt_clientes = $db->prepare($query_clientes);
    $stmt_clientes->execute();
    $total_clientes = $stmt_clientes->fetch(PDO::FETCH_ASSOC)['total_clientes'];

    $stmt_productos = $db->prepare($query_productos);
    $stmt_productos->execute();
    $productos_mas_vendidos = $stmt_productos->fetchAll(PDO::FETCH_ASSOC);

    $stmt_suscripciones = $db->prepare($query_suscripciones);
    $stmt_suscripciones->execute();
    $total_suscripciones_activas = $stmt_suscripciones->fetch(PDO::FETCH_ASSOC)['total_suscripciones'];

    // Crear el archivo CSV
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="reportes_administrador.csv"');

    $output = fopen('php://output', 'w');
    // Encabezados del archivo CSV
    fputcsv($output, ['Reporte de Administración']);
    fputcsv($output, ['Total de Emprendedores', $total_emprendedores]);
    fputcsv($output, ['Total de Clientes', $total_clientes]);
    fputcsv($output, ['Total de Suscripciones Activas', $total_suscripciones_activas]);
    fputcsv($output, []);

    // Datos de productos más vendidos
    fputcsv($output, ['Productos Más Vendidos']);
    fputcsv($output, ['Producto', 'Total Vendidos']);
    foreach ($productos_mas_vendidos as $producto) {
        fputcsv($output, [$producto['nombre_producto'], $producto['total_vendidos']]);
    }

    fclose($output);
    exit;

} catch (PDOException $e) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Error al generar el archivo CSV: ' . $e->getMessage()
    ];
    header("Location: ver_reportes.php");
    exit;
}
?>
