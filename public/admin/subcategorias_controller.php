<?php
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$response = ['status' => 'error', 'message' => 'Error inesperado'];

try {
    if (isset($_GET['eliminar_subcategoria'])) {
        $id_subcategoria = (int)$_GET['eliminar_subcategoria'];

        $query = "DELETE FROM subcategoria WHERE id_subcategoria = :id_subcategoria";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_subcategoria', $id_subcategoria);

        if ($stmt->execute()) {
            $response['status'] = 'success';
            $response['message'] = 'Subcategoría eliminada correctamente';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Error al eliminar la subcategoría';
        }

        // Devolver la respuesta
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    // Crear o editar subcategoría
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Imprimir valores para depuración en el log del servidor
        error_log("POST Data: " . print_r($_POST, true));

        // Verifica si los valores de id_categoria y nombre_subcategoria existen y no están vacíos
        $nombre_subcategoria = htmlspecialchars(strip_tags($_POST['nombre_subcategoria']));
        $descripcion_subcategoria = htmlspecialchars(strip_tags($_POST['descripcion_subcategoria']));
        $id_categoria = (int)$_POST['id_categoria'];

        // Verifica los valores de id_categoria y nombre_subcategoria
        error_log("Valor de ID Categoría recibido: $id_categoria");
        error_log("Valor de Nombre Subcategoría recibido: $nombre_subcategoria");

        // Verificación extendida
        if (empty($nombre_subcategoria)) {
            error_log("Error: Nombre de subcategoría vacío");
        }

        if ($id_categoria <= 0) {
            error_log("Error: ID de categoría inválido");
        }

        if (!empty($nombre_subcategoria) && $id_categoria > 0) {
            // Se ejecura la lógica de inserción o actualización
        } else {
            $response['message'] = 'El nombre de la subcategoría y la categoría son obligatorios';
            error_log($response['message']);
        }

        if (!empty($nombre_subcategoria) && $id_categoria > 0) {
            if (!empty($_POST['id_subcategoria'])) {
                // Editar subcategoría
                $id_subcategoria = (int)$_POST['id_subcategoria'];
                $query = "UPDATE subcategoria SET nombre_subcategoria = :nombre_subcategoria, descripcion_subcategoria = :descripcion_subcategoria, id_categoria = :id_categoria WHERE id_subcategoria = :id_subcategoria";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':id_subcategoria', $id_subcategoria);
            } else {
                // Crear nueva subcategoría
                $query = "INSERT INTO subcategoria (nombre_subcategoria, descripcion_subcategoria, id_categoria) VALUES (:nombre_subcategoria, :descripcion_subcategoria, :id_categoria)";
                $stmt = $db->prepare($query);
            }

            $stmt->bindParam(':nombre_subcategoria', $nombre_subcategoria);
            $stmt->bindParam(':descripcion_subcategoria', $descripcion_subcategoria);
            $stmt->bindParam(':id_categoria', $id_categoria);

            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = !empty($id_subcategoria) ? 'Subcategoría actualizada correctamente' : 'Subcategoría creada correctamente';
            } else {
                $response['message'] = 'Error al guardar la subcategoría';
            }
        } else {
            $response['message'] = 'El nombre de la subcategoría y la categoría son obligatorios';
        }
    }

    // Cargar una subcategoría para editar
    if (isset($_GET['id_subcategoria'])) {
        $id_subcategoria = (int)$_GET['id_subcategoria'];
        $query = "SELECT * FROM subcategoria WHERE id_subcategoria = :id_subcategoria";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_subcategoria', $id_subcategoria);
        $stmt->execute();
        $subcategoria = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($subcategoria) {
            $response['status'] = 'success';
            $response['data'] = $subcategoria;
        } else {
            $response['message'] = 'Subcategoría no encontrada';
        }
    }
} catch (Exception $e) {
    $response['message'] = 'Error inesperado: ' . $e->getMessage();
}

// Devolver la respuesta como JSON al final
header('Content-Type: application/json');
echo json_encode($response);
