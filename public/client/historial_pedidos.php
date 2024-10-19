<?php
session_start();

// Verificar si el cliente está conectado
if (!isset($_SESSION['id_cliente'])) {
    $_SESSION['message'] = [
        'type' => 'warning',
        'text' => 'Debes iniciar sesión para ver tu historial de pedidos.'
    ];
    header("Location: login_cliente.php");
    exit;
}

include_once '../../src/config/database.php';
$database = new Database();
$db = $database->getConnection();

// Obtener el ID del cliente
$id_cliente = $_SESSION['id_cliente'];

// Consultar los pedidos del cliente
$query = "SELECT p.id_pedido, p.fecha_pedido, p.estado_pedido,
                pg.metodo_pago, pg.monto, pg.estado_pago, pg.imagen_comprobante, f.id_factura
          FROM pedido p
          INNER JOIN pago pg ON p.id_pedido = pg.id_pedido
          LEFT JOIN factura f ON f.id_pedido = p.id_pedido
          WHERE p.id_cliente = :id_cliente
          ORDER BY p.fecha_pedido DESC";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_cliente', $id_cliente);
$stmt->execute();

$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Historial de Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>

    <div class="container mt-5">
        <h1>Historial de Pedidos</h1>

        <?php if (count($pedidos) > 0): ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID Pedido</th>
                        <th>Fecha</th>
                        <th>Estado del pedido</th>
                        <th>Método de pago</th>
                        <th>Estado del pago</th>
                        <th>Total</th>
                        <th>Comprobante</th>
                        <th>Factura</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($pedido['id_pedido']); ?></td>
                            <td><?php echo htmlspecialchars($pedido['fecha_pedido']); ?></td>
                            <td><?php echo htmlspecialchars($pedido['estado_pedido']); ?></td>
                            <td><?php echo ucfirst($pedido['metodo_pago']); ?></td>
                            <td><?php echo ucfirst($pedido['estado_pago']); ?></td>
                            <td>Q. <?php echo number_format($pedido['monto'], 2); ?></td>
                            <td>
                                <?php if ($pedido['imagen_comprobante']): ?>
                                    <a href="../../uploads/comprobantes/<?php echo $pedido['imagen_comprobante']; ?>" target="_blank">Ver Comprobante</a>
                                <?php else: ?>
                                    No disponible
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($pedido['id_factura']): ?>
                                    <a href="../../uploads/facturas/factura_<?php echo $pedido['id_pedido']; ?>.pdf" target="_blank">Ver factura</a>
                                <?php else: ?>
                                    No disponible
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="#" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#detallePedidoModal" data-id="<?php echo $pedido['id_pedido']; ?>">Ver Detalles</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <!-- Modal para mostrar detalles del pedido -->
                    <div class="modal fade" id="detallePedidoModal" tabindex="-1" aria-labelledby="detallePedidoModalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="detallePedidoModalLabel">Detalles del Pedido</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <!-- En este espacio se cargarán los detalles del pedido -->
                                    <div id="detallePedidoContent">
                                        <p>Cargando detalles...</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </tbody>
            </table>
        <?php else: ?>
            <p>No has realizado ningún pedido aún.</p>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var detallePedidoModal = document.getElementById('detallePedidoModal');
            detallePedidoModal.addEventListener('show.bs.modal', function(event) {
                var button = event.relatedTarget;
                var idPedido = button.getAttribute('data-id');

                // Realizar una petición AJAX para obtener los detalles del pedido
                var xhr = new XMLHttpRequest();
                xhr.open('GET', 'detalle_pedido.php?id_pedido=' + idPedido, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        document.getElementById('detallePedidoContent').innerHTML = xhr.responseText;
                    } else {
                        document.getElementById('detallePedidoContent').innerHTML = '<p>Error al cargar los detalles del pedido.</p>';
                    }
                };
                xhr.send();
            });
        });
    </script>

    <?php if ($message): ?>
        <script>
            Swal.fire({
                icon: '<?php echo $message['type']; ?>',
                title: '<?php echo ucfirst($message['type']); ?>',
                text: '<?php echo $message['text']; ?>',
            });
        </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>
