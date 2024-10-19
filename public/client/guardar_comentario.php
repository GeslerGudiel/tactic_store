<?php
session_start();
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!isset($_SESSION['id_cliente'])) {
    $_SESSION['message'] = [
        'type' => 'warning',
        'text' => 'Debes iniciar sesión para dejar un comentario.'
    ];
    header("Location: login_cliente.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_producto = intval($_POST['id_producto']);
    $id_cliente = $_SESSION['id_cliente'];
    $comentario = htmlspecialchars(strip_tags($_POST['comentario']));
    $calificacion = intval($_POST['calificacion']);

    $query = "INSERT INTO comentario (id_producto, id_cliente, comentario, calificacion, fecha_comentario) 
              VALUES (:id_producto, :id_cliente, :comentario, :calificacion, NOW())";
    
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_producto', $id_producto);
    $stmt->bindParam(':id_cliente', $id_cliente);
    $stmt->bindParam(':comentario', $comentario);
    $stmt->bindParam(':calificacion', $calificacion);

    if ($stmt->execute()) {
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Tu comentario ha sido enviado con éxito.'
        ];
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Hubo un problema al enviar tu comentario. Inténtalo de nuevo.'
        ];
    }

    header("Location: principal_cliente.php");
    exit;
}
?>
