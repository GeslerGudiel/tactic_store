<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$id_pedido = isset($_GET['id_pedido']) ? htmlspecialchars(strip_tags($_GET['id_pedido'])) : die('ID de pedido no especificado.');

// Consulta para obtener la información del pedido
$query_pedido = "SELECT p.*, c.nombre1 AS nombre_cliente, c.apellido1 AS apellido_cliente, c.telefono1, c.telefono2, c.correo 
                 FROM pedido p 
                 JOIN cliente c ON p.id_cliente = c.id_cliente 
                 WHERE p.id_pedido = :id_pedido";
$stmt_pedido = $db->prepare($query_pedido);
$stmt_pedido->bindParam(':id_pedido', $id_pedido);
$stmt_pedido->execute();
$pedido = $stmt_pedido->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    die('Pedido no encontrado.');
}

// Consulta para obtener los detalles del pedido
$query_detalle = "SELECT dp.*, p.nombre_producto, p.precio 
                  FROM detalle_pedido dp 
                  JOIN producto p ON dp.id_producto = p.id_producto 
                  WHERE dp.id_pedido = :id_pedido";
$stmt_detalle = $db->prepare($query_detalle);
$stmt_detalle->bindParam(':id_pedido', $id_pedido);
$stmt_detalle->execute();
$detalles_pedido = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);

// Consulta para obtener la información de pago
$query_pago = "SELECT * FROM pago WHERE id_pedido = :id_pedido";
$stmt_pago = $db->prepare($query_pago);
$stmt_pago->bindParam(':id_pedido', $id_pedido);
$stmt_pago->execute();
$pago = $stmt_pago->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Pedido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5">
        <h1>Detalles del Pedido</h1>

        <!-- Información del Pedido -->
        <div class="card mb-4">
            <div class="card-header">Información del Pedido</div>
            <div class="card-body">
                <p><strong>ID del Pedido:</strong> <?php echo htmlspecialchars($pedido['id_pedido']); ?></p>
                <p><strong>Cliente:</strong> <?php echo htmlspecialchars($pedido['nombre_cliente'] . ' ' . $pedido['apellido_cliente']); ?></p>
                <p><strong>Teléfono de Contacto:</strong> <?php echo htmlspecialchars($pedido['telefono1']) . ' / ' . htmlspecialchars($pedido['telefono2']); ?></p>
                <p><strong>Correo Electrónico:</strong> <?php echo htmlspecialchars($pedido['correo']); ?></p>
                <p><strong>Dirección de Envío:</strong> <?php echo htmlspecialchars($pedido['direccion_envio']); ?></p>
                <p><strong>Fecha del Pedido:</strong> <?php echo htmlspecialchars($pedido['fecha_pedido']); ?></p>
                <p><strong>Estado del Pedido:</strong> <?php echo htmlspecialchars($pedido['estado_pedido']); ?></p>
            </div>
        </div>

        <!-- Detalles del Pedido -->
        <div class="card mb-4">
            <div class="card-header">Productos del Pedido</div>
            <div class="card-body">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detalles_pedido as $detalle): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($detalle['nombre_producto']); ?></td>
                                <td><?php echo htmlspecialchars($detalle['cantidad']); ?></td>
                                <td>Q. <?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                                <td>Q. <?php echo number_format($detalle['subtotal'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Información de Pago -->
        <div class="card mb-4">
            <div class="card-header">Información de Pago</div>
            <div class="card-body">
                <?php if ($pago): ?>
                    <p><strong>Método de Pago:</strong> <?php echo htmlspecialchars($pago['metodo_pago']); ?></p>
                    <p><strong>Monto Pagado:</strong> Q. <?php echo number_format($pago['monto'], 2); ?></p>
                    <p><strong>Fecha de Pago:</strong> <?php echo htmlspecialchars($pago['fecha_pago']); ?></p>
                    <p><strong>Estado del Pago:</strong> <?php echo htmlspecialchars($pago['estado_pago']); ?></p>
                    <?php if ($pago['imagen_comprobante']): ?>
                        <p><strong>Comprobante de Pago:</strong> <a href="../../uploads/comprobantes/<?php echo htmlspecialchars($pago['imagen_comprobante']); ?>" target="_blank">Ver Comprobante</a></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p>No se ha realizado el pago para este pedido.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Formulario para cambiar el estado del pedido -->
        <div class="card mb-4">
            <div class="card-header">Cambiar Estado del Pedido</div>
            <div class="card-body">
                <form action="cambiar_estado_pedido.php" method="POST">
                    <input type="hidden" name="id_pedido" value="<?php echo htmlspecialchars($pedido['id_pedido']); ?>">

                    <!-- Cambiar Estado del Pedido -->
                    <div class="mb-3">
                        <label for="estado_pedido" class="form-label">Estado del Pedido</label>
                        <select id="estado_pedido" name="estado_pedido" class="form-select">
                            <option value="Pendiente" <?php if ($pedido['estado_pedido'] == 'Pendiente') echo 'selected'; ?>>Pendiente</option>
                            <option value="En Proceso" <?php if ($pedido['estado_pedido'] == 'En Proceso') echo 'selected'; ?>>En Proceso</option>
                            <option value="Enviado" <?php if ($pedido['estado_pedido'] == 'Enviado') echo 'selected'; ?>>Enviado</option>
                            <option value="Entregado" <?php if ($pedido['estado_pedido'] == 'Entregado') echo 'selected'; ?>>Entregado</option>
                            <option value="Cancelado" <?php if ($pedido['estado_pedido'] == 'Cancelado') echo 'selected'; ?>>Cancelado</option>
                        </select>
                    </div>

                    <!-- Cambiar Estado del Pago -->
                    <div class="mb-3">
                        <label for="estado_pago" class="form-label">Estado del Pago</label>
                        <select id="estado_pago" name="estado_pago" class="form-select">
                            <option value="Pendiente" <?php if ($pago['estado_pago'] == 'Pendiente') echo 'selected'; ?>>Pendiente</option>
                            <option value="Confirmado" <?php if ($pago['estado_pago'] == 'Confirmado') echo 'selected'; ?>>Confirmado</option>
                            <option value="Rechazado" <?php if ($pago['estado_pago'] == 'Rechazado') echo 'selected'; ?>>Rechazado</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary">Actualizar Estado</button>
                </form>
            </div>
        </div>

        <!-- Volver a la lista de pedidos -->
        <a href="gestion_pedidos.php" class="btn btn-secondary">Volver a la lista de pedidos</a>
    </div>
</body>

</html>
