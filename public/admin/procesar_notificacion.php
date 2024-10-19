<?php
session_start();

// Verificar si el usuario tiene rol de administrador o superadmin
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
    exit;
}

include_once '../../src/config/database.php';
include_once '../../src/config/funciones.php';

$database = new Database();
$db = $database->getConnection();

$titulo = htmlspecialchars(strip_tags($_POST['titulo']));
$mensaje = htmlspecialchars(strip_tags($_POST['mensaje']));
$destinatario = htmlspecialchars(strip_tags($_POST['destinatario']));

try {
    if ($destinatario === 'todos_clientes') {
        // Enviar notificación a todos los clientes
        $query_clientes = "SELECT id_cliente FROM cliente";
        $stmt = $db->prepare($query_clientes);
        $stmt->execute();
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($clientes as $cliente) {
            agregarNotificacion($db, $cliente['id_cliente'], null, $titulo, $mensaje);
        }
    } elseif ($destinatario === 'todos_emprendedores') {
        // Enviar notificación a todos los emprendedores
        $query_emprendedores = "SELECT id_emprendedor FROM emprendedor";
        $stmt = $db->prepare($query_emprendedores);
        $stmt->execute();
        $emprendedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($emprendedores as $emprendedor) {
            agregarNotificacion($db, null, $emprendedor['id_emprendedor'], $titulo, $mensaje);
        }
    } elseif ($destinatario === 'cliente' && !empty($_POST['id_cliente'])) {
        // Enviar notificación a un cliente específico
        $id_cliente = htmlspecialchars(strip_tags($_POST['id_cliente']));
        agregarNotificacion($db, $id_cliente, null, $titulo, $mensaje);
    } elseif ($destinatario === 'emprendedor' && !empty($_POST['id_emprendedor'])) {
        // Enviar notificación a un emprendedor específico
        $id_emprendedor = htmlspecialchars(strip_tags($_POST['id_emprendedor']));
        agregarNotificacion($db, null, $id_emprendedor, $titulo, $mensaje);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Debe seleccionar un destinatario válido.']);
        exit;
    }

    echo json_encode(['status' => 'success', 'message' => 'Notificación enviada correctamente.']);
    exit;
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al enviar la notificación: ' . $e->getMessage()]);
    exit;
}
