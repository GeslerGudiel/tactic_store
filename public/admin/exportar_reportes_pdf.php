<?php
session_start();
include_once '../../src/config/database.php';
include_once '../../src/config/fpdf/fpdf.php';

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

    // Generar el PDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);

    // Encabezados
    $pdf->Cell(0, 10, 'Reporte de Administracion', 0, 1, 'C');
    $pdf->Ln(10);

    // Total de Emprendedores y Clientes
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(50, 10, 'Total de Emprendedores: ' . $total_emprendedores, 0, 1);
    $pdf->Cell(50, 10, 'Total de Clientes: ' . $total_clientes, 0, 1);
    $pdf->Cell(50, 10, 'Total de Suscripciones Activas: ' . $total_suscripciones_activas, 0, 1);
    $pdf->Ln(10);

    // Productos más vendidos
    $pdf->Cell(0, 10, 'Productos Mas Vendidos', 0, 1, 'L');
    $pdf->Cell(90, 10, 'Producto', 1);
    $pdf->Cell(50, 10, 'Total Vendidos', 1);
    $pdf->Ln(10);

    foreach ($productos_mas_vendidos as $producto) {
        $pdf->Cell(90, 10, $producto['nombre_producto'], 1);
        $pdf->Cell(50, 10, $producto['total_vendidos'], 1);
        $pdf->Ln(10);
    }

    $pdf->Output('D', 'reportes_administrador.pdf');
    exit;

} catch (PDOException $e) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Error al generar el archivo PDF: ' . $e->getMessage()
    ];
    header("Location: ver_reportes.php");
    exit;
}
?>
