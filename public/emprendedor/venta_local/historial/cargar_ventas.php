<?php
session_start();
include_once '../../../../src/config/database.php';

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    echo 'Acceso denegado';
    exit;
}

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

// Obtener el mes seleccionado o usar el mes actual por defecto
$filtroMes = isset($_GET['mes']) ? $_GET['mes'] : date('Y-m');
$primerDiaMes = date('Y-m-01 00:00:00', strtotime($filtroMes . '-01'));
$ultimoDiaMes = date('Y-m-t 23:59:59', strtotime($filtroMes . '-01'));

// Consulta para obtener las ventas del mes seleccionado
$queryVentas = "SELECT vl.id_venta_local, vl.fecha_venta, vl.total, ce.nombre_cliente, ce.telefono_cliente 
                FROM ventas_locales vl 
                JOIN cliente_emprendedor ce ON vl.id_cliente_emprendedor = ce.id_cliente_emprendedor 
                WHERE vl.id_emprendedor = :id_emprendedor 
                AND vl.fecha_venta BETWEEN :primer_dia AND :ultimo_dia 
                ORDER BY vl.fecha_venta DESC";
$stmt = $db->prepare($queryVentas);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->bindParam(':primer_dia', $primerDiaMes);
$stmt->bindParam(':ultimo_dia', $ultimoDiaMes);
$stmt->execute();
$ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generar el HTML para cada venta
if (!empty($ventas)) {
    foreach ($ventas as $venta) {
        echo "<tr>
                <td>" . htmlspecialchars($venta['id_venta_local']) . "</td>
                <td>" . htmlspecialchars($venta['fecha_venta']) . "</td>
                <td>" . htmlspecialchars($venta['nombre_cliente']) . "</td>
                <td>" . htmlspecialchars($venta['telefono_cliente']) . "</td>
                <td>Q" . number_format($venta['total'], 2) . "</td>
                <td>
                    <button class='btn btn-info btn-sm btn-detalle-venta' data-id='" . $venta['id_venta_local'] . "'>
                        <i class='fas fa-eye'></i> Ver Detalle
                    </button>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='5' class='text-center'>No hay ventas registradas para este mes.</td></tr>";
}
