<?php
include_once '../../src/config/database.php';

// Conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Respuesta predeterminada
$response = ['status' => 'error', 'message' => 'Error inesperado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nombre_categoria'])) {
        $nombre_categoria = htmlspecialchars(strip_tags($_POST['nombre_categoria']));
        $descripcion_categoria = htmlspecialchars(strip_tags($_POST['descripcion_categoria']));
        $id_categoria = isset($_POST['id_categoria']) ? (int)$_POST['id_categoria'] : 0;

        if (!empty($nombre_categoria)) {
            if ($id_categoria > 0) {
                // Editar categoría existente
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
                $response['status'] = 'success';
                $response['message'] = $id_categoria > 0 ? 'Categoría actualizada correctamente' : 'Categoría creada correctamente';
            } else {
                $response['message'] = 'Error al guardar la categoría';
            }
        } else {
            $response['message'] = 'El nombre de la categoría es obligatorio';
        }
    }
}

// Eliminar una categoría si se pasa por la URL
if (isset($_GET['eliminar_categoria'])) {
    $id_categoria = (int)$_GET['eliminar_categoria'];
    $query = "DELETE FROM categoria WHERE id_categoria = :id_categoria";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_categoria', $id_categoria);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Categoría eliminada correctamente';
    } else {
        $response['message'] = 'Error al eliminar la categoría';
    }
}

// Devolver la respuesta en formato JSON
header('Content-Type: application/json');
echo json_encode($response);
