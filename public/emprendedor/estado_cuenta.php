<?php
session_start();
include_once '../../src/config/database.php';

if (!isset($_SESSION['id_emprendedor'])) {
    header("Location: ../auth/login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT p.id_pedido, SUM(c.monto_comision) AS total_comision, p.fecha_pedido, 
                 MAX(c.estado_comision) AS estado_comision, MAX(c.fecha_pago) AS fecha_pago, 
                 MAX(c.comprobante_pago) AS comprobante_pago
          FROM comision c
          INNER JOIN pedido p ON c.id_pedido = p.id_pedido
          WHERE c.id_emprendedor = :id_emprendedor
          GROUP BY p.id_pedido
          ORDER BY p.fecha_pedido DESC";

$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $_SESSION['id_emprendedor']);
$stmt->execute();
$comisiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular totales
$total_comisiones = 0;
$total_pagadas = 0;
$total_pendientes = 0;

foreach ($comisiones as $comision) {
    $total_comisiones += $comision['total_comision'];  // Total de todas las comisiones

    // Si la comisión está pagada, suma al total pagado, de lo contrario, suma al total pendiente
    if ($comision['estado_comision'] === 'Pagada') {
        $total_pagadas += $comision['total_comision'];
    } else {
        $total_pendientes += $comision['total_comision'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Estado de Cuenta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .summary-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .summary-box h3 {
            margin-bottom: 10px;
            font-size: 1.5rem;
        }

        .summary-box p {
            margin: 0;
            font-size: 1.2rem;
        }

        .summary-box i {
            margin-right: 10px;
        }

        table thead th {
            background-color: #343a40;
            color: white;
            text-align: center;
        }

        table tbody td {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h1><i class="fas fa-file-invoice-dollar"></i> Estado de Cuenta</h1>

        <!-- Resumen de totales -->
        <div class="summary-box">
            <h3><i class="fas fa-coins"></i> Resumen de Comisiones</h3>
            <p><strong>Total Comisiones:</strong> Q. <?php echo number_format($total_comisiones, 2); ?></p>
            <p><strong>Total Pagadas:</strong> Q. <?php echo number_format($total_pagadas, 2); ?></p>
            <p><strong>Total Pendientes:</strong> Q. <?php echo number_format($total_pendientes, 2); ?></p>
        </div>

        <!-- Tabla de comisiones -->
        <?php if (count($comisiones) > 0): ?>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Monto Comisión</th>
                        <th>Fecha Pedido</th>
                        <th>Estado Comisión</th>
                        <th>Fecha Pago</th>
                        <th>Comprobante de Pago</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($comisiones as $comision): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($comision['id_pedido']); ?></td>
                            <td>Q. <?php echo number_format($comision['total_comision'], 2); ?></td>
                            <td><?php echo htmlspecialchars($comision['fecha_pedido']); ?></td>
                            <td><?php echo htmlspecialchars($comision['estado_comision']); ?></td>
                            <td><?php echo htmlspecialchars($comision['fecha_pago'] ?: 'Pendiente'); ?></td>
                            <td>
                                <?php if (!empty($comision['comprobante_pago'])): ?>
                                    <a href="../../uploads/comprobantes_comision/<?php echo htmlspecialchars($comision['comprobante_pago']); ?>" target="_blank">Ver Comprobante</a>
                                <?php else: ?>
                                    <span class="text-danger">No disponible</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <script>
                Swal.fire({
                    icon: 'info',
                    title: 'Sin Comisiones',
                    text: 'No hay comisiones para mostrar en tu estado de cuenta.',
                    confirmButtonText: 'Aceptar'
                });
            </script>
        <?php endif; ?>
    </div>

    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
