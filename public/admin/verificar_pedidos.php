<?php
session_start();
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!isset($_SESSION['id_admin'])) {
    $_SESSION['message'] = [
        'type' => 'warning',
        'text' => 'Debes iniciar sesión como administrador para realizar esta acción.'
    ];
    header("Location: login_admin.php");
    exit;
}

// Seleccionar pedidos que están pendientes y cuya fecha límite ha pasado
$query = "SELECT id_pedido FROM pedido WHERE estado_pedido = 'Pendiente' AND metodo_pago = 'deposito_bancario' AND fecha_limite < NOW()";
$stmt = $db->prepare($query);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$cancelados = 0;

foreach ($pedidos as $pedido) {
    $id_pedido = $pedido['id_pedido'];

    // Cambiar el estado del pedido a "Cancelado"
    $query_update = "UPDATE pedido SET estado_pedido = 'Cancelado' WHERE id_pedido = :id_pedido";
    $stmt_update = $db->prepare($query_update);
    $stmt_update->bindParam(':id_pedido', $id_pedido);
    $stmt_update->execute();

    $cancelados++;
}

// Configurar un mensaje de éxito con el número de pedidos cancelados
$_SESSION['message'] = [
    'type' => 'success',
    'text' => "$cancelados pedidos han sido cancelados debido a que no se confirmó el pago dentro del plazo de 24 horas."
];

header("Location: admin_dashboard.php"); // Redirección
exit;
?>
