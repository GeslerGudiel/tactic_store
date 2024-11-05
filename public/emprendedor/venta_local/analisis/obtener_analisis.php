<?php
session_start();
include_once '../../../../src/config/database.php';

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

if (isset($_GET['meses']) && $_GET['meses'] === 'true') {
    $queryMeses = "SELECT DISTINCT DATE_FORMAT(CONVERT_TZ(fecha_venta, '+00:00', '-06:00'), '%Y-%m') AS mes
                   FROM ventas_locales
                   WHERE id_emprendedor = :id_emprendedor
                   ORDER BY fecha_venta DESC";
    $stmtMeses = $db->prepare($queryMeses);
    $stmtMeses->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
    $stmtMeses->execute();
    $meses = $stmtMeses->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode(['success' => true, 'meses' => $meses]);
    exit;
}

// Obtener categorías
if (isset($_GET['categorias']) && $_GET['categorias'] === 'true') {
    $queryCategorias = "SELECT id_categoria, nombre_categoria
                        FROM categoria
                        ORDER BY nombre_categoria ASC";
    $stmtCategorias = $db->prepare($queryCategorias);
    $stmtCategorias->execute();
    $categorias = $stmtCategorias->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'categorias' => $categorias]);
    exit;
}

// Obtener análisis de ventas según el mes y la categoría seleccionada
if (isset($_GET['mes']) && !empty($_GET['mes'])) {
    $mes = $_GET['mes'];
    $categoria = $_GET['categoria'] ?? 'todas';
    $primerDiaMes = date('Y-m-01 00:00:00', strtotime($mes . '-01'));
    $ultimoDiaMes = date('Y-m-t 23:59:59', strtotime($mes . '-01'));

    $queryVentas = "SELECT p.nombre_producto, SUM(dv.cantidad) AS cantidad_vendida, 
                           SUM(dv.subtotal) AS total_vendido
                    FROM detalle_venta_local dv
                    JOIN ventas_locales v ON dv.id_venta_local = v.id_venta_local
                    JOIN producto p ON dv.id_producto = p.id_producto
                    WHERE v.id_emprendedor = :id_emprendedor 
                      AND v.fecha_venta BETWEEN :primer_dia AND :ultimo_dia";

    if ($categoria !== 'todas') {
        $queryVentas .= " AND p.id_categoria = :categoria";
    }

    $queryVentas .= " GROUP BY p.nombre_producto ORDER BY total_vendido DESC";

    $stmtVentas = $db->prepare($queryVentas);
    $stmtVentas->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
    $stmtVentas->bindParam(':primer_dia', $primerDiaMes);
    $stmtVentas->bindParam(':ultimo_dia', $ultimoDiaMes);

    if ($categoria !== 'todas') {
        $stmtVentas->bindParam(':categoria', $categoria, PDO::PARAM_INT);
    }

    $stmtVentas->execute();
    $ventas = $stmtVentas->fetchAll(PDO::FETCH_ASSOC);

    // Generar el HTML para la tabla de análisis de ventas
    $html = '';
    if (!empty($ventas)) {
        foreach ($ventas as $venta) {
            $html .= "<tr>
                        <td>" . htmlspecialchars($venta['nombre_producto']) . "</td>
                        <td>" . htmlspecialchars($venta['cantidad_vendida']) . "</td>
                        <td>Q" . htmlspecialchars(number_format($venta['total_vendido'], 2)) . "</td>
                      </tr>";
        }
    } else {
        $html .= "<tr><td colspan='3' class='text-center'>No hay ventas para los filtros seleccionados.</td></tr>";
    }

    echo $html;
    exit;
}
?>
