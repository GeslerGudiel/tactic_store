<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

include_once '../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$id_producto = $_POST['id_producto'] ?? null;
$nombre_producto = $_POST['nombre_producto'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';
$costo = $_POST['costo'] ?? 0;
$precio = $_POST['precio'] ?? 0;
$stock = $_POST['stock'] ?? 0;
$estado = $_POST['estado'] ?? 'disponible';
$id_categoria = $_POST['id_categoria'] ?? null;
$imagen_nueva = $_FILES['imagen'] ?? null;

// Verificar que el ID del producto sea válido
if (!$id_producto) {
    echo json_encode(['status' => 'error', 'message' => 'ID de producto no válido']);
    exit;
}

// Validación: Si el stock es 0, forzar estado a 'no disponible'
if ($stock == 0 && $estado === 'disponible') {
    $estado = 'no disponible';
}

// Inicializar variables
$imagen = null;
$queryImagen = '';

try {
    // Verificar si se subió una nueva imagen
    if ($imagen_nueva && $imagen_nueva['error'] === 0) {
        $tipoArchivo = mime_content_type($imagen_nueva['tmp_name']);
        $formatosPermitidos = ['image/jpeg', 'image/png', 'image/gif'];

        // Validar tipo de archivo
        if (!in_array($tipoArchivo, $formatosPermitidos)) {
            echo json_encode(['status' => 'error', 'message' => 'Formato de imagen no permitido']);
            exit;
        }

        $nombre_imagen = time() . "_" . basename($imagen_nueva['name']);
        $ruta_destino = "../../../uploads/productos/" . $nombre_imagen;

        // Mover la imagen al destino
        if (move_uploaded_file($imagen_nueva['tmp_name'], $ruta_destino)) {
            $queryImagen = ", imagen = :imagen";
            $imagen = $nombre_imagen;
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al subir la imagen']);
            exit;
        }
    }

    // Preparar la consulta SQL
    $query = "UPDATE producto 
              SET nombre_producto = :nombre_producto, 
                  descripcion = :descripcion, 
                  costo = :costo, 
                  precio = :precio, 
                  stock = :stock, 
                  estado = :estado, 
                  id_categoria = :id_categoria
                  $queryImagen
              WHERE id_producto = :id_producto";

    $stmt = $db->prepare($query);

    // Asignar los parámetros
    $stmt->bindParam(':nombre_producto', $nombre_producto);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':costo', $costo);
    $stmt->bindParam(':precio', $precio);
    $stmt->bindParam(':stock', $stock);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
    $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);

    if ($imagen) {
        $stmt->bindParam(':imagen', $imagen);
    }

    // Ejecutar la consulta
    if ($stmt->execute()) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Producto actualizado',
            'producto' => [
                'id_producto' => $id_producto,
                'nombre_producto' => $nombre_producto,
                'descripcion' => $descripcion,
                'costo' => $costo,
                'precio' => $precio,
                'stock' => $stock,
                'estado' => $estado,
                'id_categoria' => $id_categoria,
                'imagen' => $imagen ?? ''
            ]
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo actualizar el producto']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'Excepción: ' . $e->getMessage()]);
}
?>
