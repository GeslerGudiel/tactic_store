<?php
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$response = ['status' => 'error', 'message' => 'Error inesperado']; // Respuesta por defecto

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nombre_categoria = htmlspecialchars(strip_tags($_POST['nombre_categoria']));
        $descripcion_categoria = htmlspecialchars(strip_tags($_POST['descripcion_categoria']));

        if (!empty($nombre_categoria)) {
            if (!empty($_POST['id_categoria'])) {
                // Actualizar categoría
                $id_categoria = (int)$_POST['id_categoria'];
                $query = "UPDATE categoria SET nombre_categoria = :nombre_categoria, descripcion_categoria = :descripcion_categoria WHERE id_categoria = :id_categoria";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id_categoria', $id_categoria);
            } else {
                // Crear nueva categoría
                $query = "INSERT INTO categoria (nombre_categoria, descripcion_categoria) VALUES (:nombre_categoria, :descripcion_categoria)";
                $stmt = $db->prepare($query);
            }

            $stmt->bindParam(':nombre_categoria', $nombre_categoria);
            $stmt->bindParam(':descripcion_categoria', $descripcion_categoria);

            if ($stmt->execute()) {
                // Enviar respuesta de éxito
                $response['status'] = 'success';
                $response['message'] = 'Categoría guardada correctamente';
            } else {
                $response['message'] = 'Error al guardar la categoría';
            }
        } else {
            $response['message'] = 'El nombre de la categoría es obligatorio';
        }
    } else if (isset($_GET['eliminar_categoria'])) {
        $id_categoria = (int)$_GET['eliminar_categoria'];

        // Eliminar las subcategorías primero para evitar violaciones de clave foránea
        $query_subcategorias = "DELETE FROM subcategoria WHERE id_categoria = :id_categoria";
        $stmt_subcategorias = $db->prepare($query_subcategorias);
        $stmt_subcategorias->bindParam(':id_categoria', $id_categoria);
        $stmt_subcategorias->execute();

        // Eliminar la categoría
        $query_categoria = "DELETE FROM categoria WHERE id_categoria = :id_categoria";
        $stmt_categoria = $db->prepare($query_categoria);
        $stmt_categoria->bindParam(':id_categoria', $id_categoria);

        if ($stmt_categoria->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Categoría eliminada correctamente';
        } else {
            $response['message'] = 'Error al eliminar la categoría';
        }
    }
} catch (Exception $e) {
    $response['message'] = 'Error inesperado: ' . $e->getMessage(); // Mostrar cualquier error inesperado
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['obtener_categoria']) && isset($_GET['id_categoria'])) {
    $id_categoria = (int)$_GET['id_categoria'];

    // Obtener los datos de la categoría para edición
    $query = "SELECT * FROM categoria WHERE id_categoria = :id_categoria";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_categoria', $id_categoria);

    if ($stmt->execute()) {
        $categoria = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($categoria) {
            // Devolver los datos de la categoría para el frontend
            $response['status'] = 'success';
            $response['data'] = $categoria;
        } else {
            $response['message'] = 'Categoría no encontrada';
        }
    } else {
        $response['message'] = 'Error al obtener la categoría';
    }
}


// Enviar la respuesta en formato JSON
echo json_encode($response);
