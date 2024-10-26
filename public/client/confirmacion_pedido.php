<?php
session_start();

if (!isset($_SESSION['id_cliente'])) {
    $_SESSION['message'] = [
        'type' => 'warning',
        'text' => 'Debes iniciar sesión para ver la confirmación del pedido.'
    ];
    header("Location: login_cliente.php");
    exit;
}

if (!isset($_SESSION['pedido_confirmado'])) {
    header("Location: principal_cliente.php");
    exit;
}

// Limpiar el indicador de pedido confirmado para que la página no sea accesible directamente nuevamente.
unset($_SESSION['pedido_confirmado']);

include_once '../../src/config/database.php';
$database = new Database();
$db = $database->getConnection();

$id_pedido = $_SESSION['ultimo_pedido_id'] ?? null;

if (!$id_pedido) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'No se encontró el pedido.'
    ];
    header("Location: principal_cliente.php");
    exit;
}

// Obtener la información del pedido
$query_pedido = "SELECT p.id_pedido, p.fecha_pedido, p.estado_pedido, c.nombre1, c.apellido1, p.direccion_envio, p.telefono_contacto, p.id_pedido 
                 FROM pedido p
                 JOIN cliente c ON p.id_cliente = c.id_cliente
                 WHERE p.id_pedido = :id_pedido";
$stmt_pedido = $db->prepare($query_pedido);
$stmt_pedido->bindParam(':id_pedido', $id_pedido);
$stmt_pedido->execute();
$pedido = $stmt_pedido->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Pedido no encontrado.'
    ];
    header("Location: principal_cliente.php");
    exit;
}

// Obtener los detalles del pedido
$query_detalle = "SELECT dp.nombre_producto, dp.cantidad, dp.precio_unitario, dp.subtotal 
                  FROM detalle_pedido dp 
                  WHERE dp.id_pedido = :id_pedido";
$stmt_detalle = $db->prepare($query_detalle);
$stmt_detalle->bindParam(':id_pedido', $id_pedido);
$stmt_detalle->execute();
$detalles = $stmt_detalle->fetchAll(PDO::FETCH_ASSOC);

if (!$detalles) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'No se encontraron detalles para este pedido.'
    ];
    header("Location: principal_cliente.php");
    exit;
}

// Enviar correo de confirmación al cliente
$to = $pedido['correo'];  // Correo del cliente
$subject = "Confirmación de tu Pedido #" . $pedido['id_pedido'];
$headers = "From: tienda@miempresa.com\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n";

// Crear el contenido del correo
$message = "<h1>¡Gracias por tu compra, " . htmlspecialchars($pedido['nombre1'] . " " . $pedido['apellido1']) . "!</h1>";
$message .= "<p>Tu pedido ha sido realizado con éxito. A continuación, te mostramos un resumen de tu pedido:</p>";
$message .= "<h3>Detalles del Pedido</h3>";
$message .= "<p><strong>Número de Pedido:</strong> " . htmlspecialchars($pedido['id_pedido']) . "</p>";
$message .= "<p><strong>Fecha:</strong> " . htmlspecialchars($pedido['fecha_pedido']) . "</p>";
$message .= "<p><strong>Estado:</strong> " . htmlspecialchars($pedido['estado_pedido']) . "</p>";
$message .= "<p><strong>Dirección de Envío:</strong> " . htmlspecialchars($pedido['direccion_envio']) . "</p>";
$message .= "<p><strong>Teléfono de Contacto:</strong> " . htmlspecialchars($pedido['telefono_contacto']) . "</p>";

$message .= "<h3>Productos</h3>";
$message .= "<table border='1' cellpadding='5' cellspacing='0' style='width:100%; border-collapse: collapse;'>";
$message .= "<thead><tr><th>Producto</th><th>Cantidad</th><th>Precio Unitario</th><th>Subtotal</th></tr></thead>";
$message .= "<tbody>";

$total = 0;
foreach ($detalles as $detalle) {
    $message .= "<tr>";
    $message .= "<td>" . htmlspecialchars($detalle['nombre_producto']) . "</td>";
    $message .= "<td>" . htmlspecialchars($detalle['cantidad']) . "</td>";
    $message .= "<td>Q. " . number_format($detalle['precio_unitario'], 2) . "</td>";
    $message .= "<td>Q. " . number_format($detalle['subtotal'], 2) . "</td>";
    $message .= "</tr>";
    $total += $detalle['subtotal'];
}

$message .= "</tbody></table>";
$message .= "<h3>Total: Q. " . number_format($total, 2) . "</h3>";

// Enviar el correo
if (mail($to, $subject, $message, $headers)) {
    $_SESSION['message'] = [
        'type' => 'success',
        'text' => 'Correo de confirmación enviado correctamente.'
    ];
} else {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'No se pudo enviar el correo de confirmación.'
    ];
}
?>

<?php
if (isset($_SESSION['message'])) {
    echo "<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: '" . $_SESSION['message']['type'] . "',
            title: 'Atención',
            text: '" . $_SESSION['message']['text'] . "',
            confirmButtonText: 'Aceptar'
        });
    });
    </script>";
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmación de Pedido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <div class="container mt-5">
        <h1>Gracias por tu compra, <?php echo htmlspecialchars($pedido['nombre1'] . ' ' . $pedido['apellido1']); ?>!</h1>
        <p>Tu pedido ha sido realizado con éxito. A continuación, te mostramos un resumen de tu pedido:</p>

        <h3>Detalles del Pedido</h3>
        <p><strong>Número de Pedido:</strong> <?php echo htmlspecialchars($pedido['id_pedido']); ?></p>
        <p><strong>Fecha:</strong> <?php echo htmlspecialchars($pedido['fecha_pedido']); ?></p>
        <p><strong>Estado:</strong> <?php echo htmlspecialchars($pedido['estado_pedido']); ?></p>
        <p><strong>Dirección de Envío:</strong> <?php echo htmlspecialchars($pedido['direccion_envio']); ?></p>
        <p><strong>Teléfono de Contacto:</strong> <?php echo htmlspecialchars($pedido['telefono_contacto']); ?></p>

        <h3>Productos</h3>
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
                <?php
                $total = 0;
                foreach ($detalles as $detalle) {
                    echo '<tr>';
                    echo '<td>' . htmlspecialchars($detalle['nombre_producto']) . '</td>';
                    echo '<td>' . htmlspecialchars($detalle['cantidad']) . '</td>';
                    echo '<td>Q. ' . number_format($detalle['precio_unitario'], 2) . '</td>';
                    echo '<td>Q. ' . number_format($detalle['subtotal'], 2) . '</td>';
                    echo '</tr>';
                    $total += $detalle['subtotal'];
                }
                ?>
            </tbody>
        </table>

        <h3>Total: Q. <?php echo number_format($total, 2); ?></h3>

        <a href="principal_cliente.php" class="btn btn-primary mt-3">Ir a la tienda</a>
    </div>

    <?php if (isset($message)): ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: '<?php echo $message['type']; ?>',
                    title: '<?php echo $message['type'] === 'success' ? 'Éxito' : 'Atención'; ?>',
                    text: '<?php echo $message['text']; ?>',
                    confirmButtonText: 'Aceptar'
                });
            });
        </script>
    <?php endif; ?>

    <?php if (isset($_SESSION['show_comprobante_alert']) && $_SESSION['show_comprobante_alert']) : ?>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: 'warning',
                    title: 'Recuerda',
                    text: 'Tienes 24 horas para adjuntar el comprobante de pago. ¡No olvides hacerlo para que tu pedido sea procesado!',
                    confirmButtonText: 'Entendido'
                });
            });
        </script>
        <?php
        // Limpiar la alerta de la sesión después de mostrarla
        unset($_SESSION['show_comprobante_alert']);
        ?>
    <?php endif; ?>

</body>

</html>