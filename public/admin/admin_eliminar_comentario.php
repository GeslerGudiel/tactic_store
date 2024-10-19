<?php
session_start();
include_once '../../src/config/funciones.php';
include_once '../../src/config/database.php';

// Verificar si el usuario es administrador o superadmin
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_comentario = $_POST['id_comentario'];

    $database = new Database();
    $db = $database->getConnection();

    // Obtener el id_emprendedor del comentario
    $query_emprendedor = "SELECT p.id_emprendedor, p.nombre_producto
                          FROM comentario c
                          INNER JOIN producto p ON c.id_producto = p.id_producto
                          WHERE c.id_comentario = :id_comentario";
    $stmt_emprendedor = $db->prepare($query_emprendedor);
    $stmt_emprendedor->bindParam(':id_comentario', $id_comentario);
    $stmt_emprendedor->execute();
    $emprendedor = $stmt_emprendedor->fetch(PDO::FETCH_ASSOC);

    if ($emprendedor) {
        $nombre_producto = $emprendedor['nombre_producto'];

        // Eliminar el comentario
        $query = "DELETE FROM comentario WHERE id_comentario = :id_comentario";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_comentario', $id_comentario);

        if ($stmt->execute()) {
            agregarNotificacion($db, null, $emprendedor['id_emprendedor'], "Comentario eliminado", "El comentario en el producto {$nombre_producto} ha sido eliminado por el administrador.");
            echo json_encode(['success' => 'Comentario eliminado correctamente.']);
        } else {
            echo json_encode(['error' => 'Error al eliminar el comentario.']);
        }
    } else {
        echo json_encode(['error' => 'No se encontró el comentario o el producto asociado.']);
    }
}
?>
