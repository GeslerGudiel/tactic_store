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
    $respuesta = htmlspecialchars(strip_tags($_POST['respuesta']));  // Limpiar la respuesta

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
        $id_emprendedor = $emprendedor['id_emprendedor'];
        $nombre_producto = $emprendedor['nombre_producto'];

        // Actualizar la respuesta en el comentario
        $query = "UPDATE comentario SET respuesta = :respuesta WHERE id_comentario = :id_comentario";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':respuesta', $respuesta);
        $stmt->bindParam(':id_comentario', $id_comentario);

        if ($stmt->execute()) {
            // Notificar al emprendedor sobre la actualización de la respuesta
            agregarNotificacion($db, null, $id_emprendedor, "Respuesta de comentario actualizada", "Tu respuesta al comentario del producto {$nombre_producto} ha sido modificada por el administrador.");
            echo json_encode(['success' => 'Respuesta actualizada correctamente.']);
        } else {
            echo json_encode(['error' => 'Error al actualizar la respuesta.']);
        }
    } else {
        echo json_encode(['error' => 'No se pudo encontrar el emprendedor relacionado con este comentario.']);
    }
}
?>
