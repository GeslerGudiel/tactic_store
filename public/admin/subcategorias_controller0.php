<?php
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Manejo de respuesta
$response = ['status' => 'error', 'message' => 'Error inesperado'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['nombre_subcategoria']) && isset($_POST['id_categoria'])) {
        // Crear nueva subcategoría
        $nombre_subcategoria = htmlspecialchars(strip_tags($_POST['nombre_subcategoria']));
        $descripcion_subcategoria = htmlspecialchars(strip_tags($_POST['descripcion_subcategoria']));
        $id_categoria = (int)$_POST['id_categoria'];

        if (!empty($nombre_subcategoria) && $id_categoria > 0) {
            $query = "INSERT INTO subcategoria (nombre_subcategoria, descripcion_subcategoria, id_categoria) 
                      VALUES (:nombre_subcategoria, :descripcion_subcategoria, :id_categoria)";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':nombre_subcategoria', $nombre_subcategoria);
            $stmt->bindParam(':descripcion_subcategoria', $descripcion_subcategoria);
            $stmt->bindParam(':id_categoria', $id_categoria);

            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Subcategoría creada correctamente';
            } else {
                $response['message'] = 'Error al guardar la subcategoría';
            }
        } else {
            $response['message'] = 'El nombre de la subcategoría y la categoría son obligatorios';
        }
    }
}

// Eliminar una subcategoría
if (isset($_GET['eliminar_subcategoria'])) {
    $id_subcategoria = (int)$_GET['eliminar_subcategoria'];

    $query = "DELETE FROM subcategoria WHERE id_subcategoria = :id_subcategoria";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_subcategoria', $id_subcategoria);

    if ($stmt->execute()) {
        $response['status'] = 'success';
        $response['message'] = 'Subcategoría eliminada correctamente';
    } else {
        $response['message'] = 'Error al eliminar la subcategoría';
    }
}

// Obtener los datos de una subcategoría para editar
if (isset($_GET['obtener_subcategoria'])) {
    $id_subcategoria = (int)$_GET['obtener_subcategoria'];

    $query = "SELECT * FROM subcategoria WHERE id_subcategoria = :id_subcategoria";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_subcategoria', $id_subcategoria);
    $stmt->execute();

    $subcategoria = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($subcategoria) {
        echo json_encode($subcategoria);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Subcategoría no encontrada']);
    }
    exit();
}

// Actualizar una subcategoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_subcategoria'])) {
    $id_subcategoria = (int)$_POST['id_subcategoria'];
    $nombre_subcategoria = htmlspecialchars(strip_tags($_POST['nombre_subcategoria']));
    $descripcion_subcategoria = htmlspecialchars(strip_tags($_POST['descripcion_subcategoria']));
    $id_categoria = (int)$_POST['id_categoria'];

    if (!empty($nombre_subcategoria) && $id_categoria > 0) {
        $query = "UPDATE subcategoria 
                  SET nombre_subcategoria = :nombre_subcategoria, 
                      descripcion_subcategoria = :descripcion_subcategoria, 
                      id_categoria = :id_categoria 
                  WHERE id_subcategoria = :id_subcategoria";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nombre_subcategoria', $nombre_subcategoria);
        $stmt->bindParam(':descripcion_subcategoria', $descripcion_subcategoria);
        $stmt->bindParam(':id_categoria', $id_categoria);
        $stmt->bindParam(':id_subcategoria', $id_subcategoria);

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Subcategoría actualizada correctamente';
        } else {
            $response['message'] = 'Error al actualizar la subcategoría';
        }
    } else {
        $response['message'] = 'El nombre de la subcategoría y la categoría son obligatorios';
    }
    echo json_encode($response);
}




// Devolver la respuesta en formato JSON
echo json_encode($response);
