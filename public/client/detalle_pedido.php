<?php
session_start();
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Verificar si el cliente está conectado
if (!isset($_SESSION['id_cliente'])) {
    $_SESSION['message'] = [
        'type' => 'warning',
        'text' => 'Debes iniciar sesión para ver los detalles del pedido.'
    ];
    header("Location: login_cliente.php");
    exit; 
}

$id_cliente = $_SESSION['id_cliente'];
$id_pedido = $_GET['id_pedido'];

// Obtener los detalles del pedido
$query_pedido = "SELECT p.id_pedido, p.fecha_pedido, p.estado_pedido, p.direccion_envio, p.telefono_contacto, 
                 SUM(d.subtotal) as total_pedido, pg.imagen_comprobante, pg.metodo_pago, pg.id_factura
                 FROM pedido p 
                 JOIN detalle_pedido d ON p.id_pedido = d.id_pedido 
                 LEFT JOIN pago pg ON p.id_pedido = pg.id_pedido
                 WHERE p.id_pedido = :id_pedido AND p.id_cliente = :id_cliente";
$stmt_pedido = $db->prepare($query_pedido);
$stmt_pedido->bindParam(':id_pedido', $id_pedido);
$stmt_pedido->bindParam(':id_cliente', $id_cliente);
$stmt_pedido->execute();
$pedido = $stmt_pedido->fetch(PDO::FETCH_ASSOC);

$query_detalles = "SELECT d.id_producto, d.cantidad, d.precio_unitario, d.subtotal, p.nombre_producto 
                   FROM detalle_pedido d 
                   JOIN producto p ON d.id_producto = p.id_producto 
                   WHERE d.id_pedido = :id_pedido";
$stmt_detalles = $db->prepare($query_detalles);
$stmt_detalles->bindParam(':id_pedido', $id_pedido);
$stmt_detalles->execute();
$detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

// Verificar si hay mensajes en la sesión para mostrar
$message = null;
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Eliminar el mensaje después de mostrarlo
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Pedido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <div class="container mt-4">
        <h1>Detalles del Pedido #<?php echo htmlspecialchars($pedido['id_pedido']); ?></h1>
        <p><strong>Fecha del Pedido:</strong> <?php echo htmlspecialchars($pedido['fecha_pedido']); ?></p>
        <p><strong>Estado del Pedido:</strong> <?php echo htmlspecialchars($pedido['estado_pedido']); ?></p>
        <p><strong>Dirección de Envío:</strong> <?php echo htmlspecialchars($pedido['direccion_envio']); ?></p>
        <p><strong>Teléfono de Contacto:</strong> <?php echo htmlspecialchars($pedido['telefono_contacto']); ?></p>
        <p><strong>Total del Pedido:</strong> Q. <?php echo number_format($pedido['total_pedido'], 2); ?></p>

        <h3>Productos del Pedido</h3>
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
                <?php foreach ($detalles as $detalle): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($detalle['nombre_producto']); ?></td>
                        <td><?php echo htmlspecialchars($detalle['cantidad']); ?></td>
                        <td>Q. <?php echo number_format($detalle['precio_unitario'], 2); ?></td>
                        <td>Q. <?php echo number_format($detalle['subtotal'], 2); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($pedido['estado_pedido'] === 'Pendiente' && $pedido['metodo_pago'] === 'deposito_bancario' && empty($pedido['imagen_comprobante'])): ?>
            <h3>Subir Comprobante de Depósito</h3>
            <form action="subir_comprobante.php" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="imagen_comprobante" class="form-label">Comprobante de Depósito</label>
                    <input class="form-control" type="file" id="imagen_comprobante" name="imagen_comprobante" accept="image/*" required>
                    <input type="hidden" name="id_pedido" value="<?php echo htmlspecialchars($pedido['id_pedido']); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Subir Comprobante</button>
            </form>
        <?php elseif (!empty($pedido['id_factura'])): ?>
            <h3>Factura Disponible</h3>
            <a href="../../uploads/facturas/<?php echo htmlspecialchars($pedido['id_factura']); ?>" class="btn btn-info" target="_blank">Ver Factura</a>
        <?php endif; ?>

        <a href="historial_pedidos.php" class="btn btn-secondary">Volver al Historial de Pedidos</a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <?php if ($message): ?>
        <script>
            Swal.fire({
                icon: '<?php echo $message['type']; ?>',
                title: '<?php echo ucfirst($message['type']); ?>',
                text: '<?php echo $message['text']; ?>',
            });
        </script>
    <?php endif; ?>

</body>

</html>