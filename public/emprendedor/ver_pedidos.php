<?php
session_start();
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$id_emprendedor = $_SESSION['id_emprendedor'];

// Verificar el estado del emprendedor
$query = "SELECT id_estado_usuario FROM emprendedor WHERE id_emprendedor = :id_emprendedor";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->execute();
$estado_emprendedor = $stmt->fetch(PDO::FETCH_ASSOC)['id_estado_usuario'];

// Si el estado es "Pendiente de Validación", redirigir a la página del perfil
if ($estado_emprendedor == 3) {
    $_SESSION['message'] = [
        'type' => 'warning',
        'text' => 'Tu cuenta está pendiente de validación por el administrador.'
    ];
    header("Location: dashboard.php");
    exit;
}
// Consulta para obtener los pedidos agrupados por pedido
$query = "
    SELECT 
        p.id_pedido,
        p.fecha_pedido,
        dp.id_producto,
        dp.nombre_producto,
        dp.cantidad,
        dp.precio_unitario,
        dp.subtotal,
        pa.estado_pago,
        p.estado_pedido,
        dp.factura_emprendedor
    FROM 
        detalle_pedido dp
    JOIN 
        pedido p ON dp.id_pedido = p.id_pedido
    JOIN 
        pago pa ON p.id_pedido = pa.id_pedido
    WHERE 
        dp.id_emprendedor = :id_emprendedor
    ORDER BY 
        p.fecha_pedido DESC, p.id_pedido ASC";

$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->execute();

$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si hay un mensaje de sesión
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos de Mis Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        h4 {
            color: #007bff;
            margin-top: 20px;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .table thead th {
            background-color: #343a40;
            color: white;
        }

        .btn-success {
            background-color: #28a745;
            border: none;
        }

        .btn-success:hover {
            background-color: #218838;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h2><i class="fas fa-box"></i> Pedidos de Mis Productos</h2>

        <?php if (isset($message)): ?>
            <script>
                Swal.fire({
                    icon: '<?php echo $message['type']; ?>',
                    title: '<?php echo ucfirst($message['type']); ?>',
                    text: '<?php echo $message['text']; ?>',
                    confirmButtonText: 'Aceptar'
                });
            </script>
        <?php endif; ?>

        <?php if (empty($pedidos)): ?>
            <script>
                Swal.fire({
                    icon: 'info',
                    title: 'Sin pedidos',
                    text: 'No tienes pedidos pendientes.',
                    confirmButtonText: 'Aceptar'
                });
            </script>
        <?php else: ?>
            <div class="scroll-container">
                <?php
                $pedido_actual = null;
                foreach ($pedidos as $pedido):
                    if ($pedido_actual !== $pedido['id_pedido']):
                        if ($pedido_actual !== null):
                            // Cierra la tabla anterior si hay un nuevo pedido
                            echo '</tbody></table>';
                            echo '<form action="subir_factura.php" method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="id_pedido" value="' . htmlspecialchars($pedido_actual) . '">
                                    <div class="mb-3">
                                        <label for="factura" class="form-label"><i class="fas fa-file-invoice"></i> Subir Factura para este Pedido:</label>
                                        <input type="file" class="form-control" id="factura" name="factura" required>
                                    </div>
                                    <button type="submit" class="btn btn-success"><i class="fas fa-upload"></i> Subir Factura</button>
                                </form><hr>';
                        endif;
                        $pedido_actual = $pedido['id_pedido'];
                ?>
                        <h4><i class="fas fa-receipt"></i> Pedido #<?php echo htmlspecialchars($pedido['id_pedido']); ?> - Fecha: <?php echo htmlspecialchars($pedido['fecha_pedido']); ?></h4>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th><i class="fas fa-box"></i> Producto</th>
                                    <th><i class="fas fa-sort-numeric-up-alt"></i> Cantidad</th>
                                    <th><i class="fas fa-dollar-sign"></i> Precio Unitario</th>
                                    <th><i class="fas fa-money-bill-wave"></i> Subtotal</th>
                                    <th><i class="fas fa-credit-card"></i> Estado de Pago</th>
                                    <th><i class="fas fa-file-invoice"></i> Factura</th>
                                    <th><i class="fas fa-shipping-fast"></i> Estado de Pedido</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php endif; ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pedido['nombre_producto']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['cantidad']); ?></td>
                                <td>Q. <?php echo number_format($pedido['precio_unitario'], 2); ?></td>
                                <td>Q. <?php echo number_format($pedido['subtotal'], 2); ?></td>
                                <td><?php echo htmlspecialchars($pedido['estado_pago']); ?></td>
                                <td>
                                    <?php if (!empty($pedido['factura_emprendedor'])): ?>
                                        <a href="../../uploads/facturas_emprendedores/<?php echo htmlspecialchars($pedido['factura_emprendedor']); ?>" target="_blank" class="btn btn-info btn-sm">
                                            <i class="fas fa-file-invoice"></i> Ver Factura
                                        </a>
                                    <?php else: ?>
                                        <span class="text-danger">Factura no subida</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($pedido['estado_pedido']); ?></td>
                            </tr>
                        <?php
                    endforeach;
                    // Cierra la tabla y muestra el formulario para subir la factura para el último pedido
                    echo '</tbody></table>';
                    echo '<form action="subir_factura.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id_pedido" value="' . htmlspecialchars($pedido_actual) . '">
                        <div class="mb-3">
                            <label for="factura" class="form-label"><i class="fas fa-file-invoice"></i> Subir Factura para este Pedido:</label>
                            <input type="file" class="form-control" id="factura" name="factura" required>
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fas fa-upload"></i> Subir Factura</button>
                    </form><hr>';
                        ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
