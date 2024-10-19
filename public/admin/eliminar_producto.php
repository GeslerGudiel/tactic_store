<?php
session_start();

// Verificar si el usuario es administrador
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

include_once '../../src/config/database.php';

$id_producto = isset($_GET['id_producto']) ? $_GET['id_producto'] : die('ID de producto no especificado.');

try {
    $database = new Database();
    $db = $database->getConnection();

    // Eliminar el producto
    $query = "DELETE FROM producto WHERE id_producto = :id_producto";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'El producto ha sido eliminado correctamente.'
        ];
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Hubo un error al eliminar el producto.'
        ];
    }

    header("Location: ver_productos_admin.php");
    exit;
} catch (PDOException $e) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Error en la base de datos: ' . $e->getMessage()
    ];
    header("Location: ver_productos_admin.php");
    exit;
}
?>
