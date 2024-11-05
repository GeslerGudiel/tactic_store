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
    $queryMeses = "SELECT DISTINCT DATE_FORMAT(fecha_registro, '%Y-%m') AS mes 
                   FROM cliente_emprendedor 
                   WHERE id_emprendedor = :id_emprendedor 
                   ORDER BY mes DESC";
    $stmtMeses = $db->prepare($queryMeses);
    $stmtMeses->bindParam(':id_emprendedor', $id_emprendedor);
    $stmtMeses->execute();
    $meses = $stmtMeses->fetchAll(PDO::FETCH_COLUMN);
    
    echo json_encode(['success' => true, 'meses' => $meses]);
    exit;
}

// Obtener clientes en el mes y hora especificados, o todos si no se especifica
$mes = $_GET['mes'] ?? date('Y-m');
$primerDiaMes = date('Y-m-01 00:00:00', strtotime($mes . '-01')); 
$ultimoDiaMes = date('Y-m-t 23:59:59', strtotime($mes . '-01')); // Asegurar límites del mes

$queryClientes = "SELECT id_cliente_emprendedor, nombre_cliente, correo_cliente, telefono_cliente, direccion_cliente, fecha_registro 
                  FROM cliente_emprendedor 
                  WHERE id_emprendedor = :id_emprendedor 
                  AND fecha_registro BETWEEN :primer_dia AND :ultimo_dia
                  ORDER BY nombre_cliente ASC";
$stmtClientes = $db->prepare($queryClientes);
$stmtClientes->bindParam(':id_emprendedor', $id_emprendedor);
$stmtClientes->bindParam(':primer_dia', $primerDiaMes);
$stmtClientes->bindParam(':ultimo_dia', $ultimoDiaMes);
$stmtClientes->execute();
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

// Generar el HTML para cada cliente con la fecha y hora de creación
if (!empty($clientes)) {
    foreach ($clientes as $cliente) {
        echo "<tr>
                <td>" . htmlspecialchars($cliente['id_cliente_emprendedor']) . "</td>
                <td>" . htmlspecialchars($cliente['nombre_cliente']) . "</td>
                <td>" . htmlspecialchars($cliente['correo_cliente']) . "</td>
                <td>" . htmlspecialchars($cliente['telefono_cliente']) . "</td>
                <td>" . htmlspecialchars($cliente['direccion_cliente']) . "</td>
                <td>" . htmlspecialchars(date('d-m-Y H:i:s', strtotime($cliente['fecha_registro']))) . "</td>
                <td>
                    <button class='btn btn-info btn-sm btn-editar' data-id='" . $cliente['id_cliente_emprendedor'] . "'>
                        <i class='fas fa-edit'></i>
                    </button>
                    <button class='btn btn-danger btn-sm btn-eliminar' data-id='" . $cliente['id_cliente_emprendedor'] . "'>
                        <i class='fas fa-trash'></i>
                    </button>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='7' class='text-center'>No hay clientes registrados en este mes.</td></tr>";
}
?>
