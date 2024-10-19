<?php
session_start();
include_once '../../src/config/database.php';

if (!isset($_SESSION['id_emprendedor'])) {
    header("Location: ../auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    $id_producto = $_POST['id_producto'];
    $stock = $_POST['stock'];
    $estado = $_POST['estado'];

    $query = "UPDATE producto SET stock = :stock, estado = :estado WHERE id_producto = :id_producto AND id_emprendedor = :id_emprendedor";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':stock', $stock);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id_producto', $id_producto);
    $stmt->bindParam(':id_emprendedor', $_SESSION['id_emprendedor']);

    if ($stmt->execute()) {
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Inventario actualizado con éxito.'
        ];
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Error al actualizar el inventario. Por favor, intenta de nuevo.'
        ];
    }

    header("Location: ver_inventario.php");
    exit;
} else {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Solicitud no válida.'
    ];
    header("Location: ver_inventario.php");
    exit;
}
