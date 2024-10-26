<?php
session_start();
if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
    exit;
}

include_once '../../../src/config/database.php';

// Función para generar un slug del nombre del producto
function generarSlug($cadena) {
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $cadena)));
    return $slug;
}

$database = new Database();
$db = $database->getConnection();

$id_emprendedor = $_SESSION['id_emprendedor'];
$id_negocio = $_SESSION['id_negocio'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Método no permitido']);
    exit;
}

// Validar y sanitizar los datos del producto
$nombre_producto = htmlspecialchars(strip_tags($_POST['nombre_producto']));
$slug = generarSlug($nombre_producto);  // Generar slug para URL amigable
$descripcion = htmlspecialchars(strip_tags($_POST['descripcion']));
$costo = filter_var($_POST['costo'], FILTER_VALIDATE_FLOAT);
$precio = filter_var($_POST['precio'], FILTER_VALIDATE_FLOAT);
$stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
$estado = htmlspecialchars(strip_tags($_POST['estado']));
$id_categoria = filter_var($_POST['id_categoria'], FILTER_VALIDATE_INT);

// Metadatos adicionales (opcional para SEO)
$meta_title = isset($_POST['meta_title']) ? htmlspecialchars(strip_tags($_POST['meta_title'])) : $nombre_producto;
$meta_description = isset($_POST['meta_description']) ? htmlspecialchars(strip_tags($_POST['meta_description'])) : substr($descripcion, 0, 160);

// Si el stock es 0, forzar el estado a "No disponible"
if ($stock == 0) {
    $estado = 'no disponible';
}

// Verificar si ya existe un producto con el mismo nombre para el mismo emprendedor
$query = "SELECT COUNT(*) FROM producto 
          WHERE nombre_producto = :nombre_producto AND id_emprendedor = :id_emprendedor";
$stmt = $db->prepare($query);
$stmt->bindParam(':nombre_producto', $nombre_producto);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->execute();
$existeProducto = $stmt->fetchColumn();

if ($existeProducto > 0) {
    echo json_encode(['status' => 'error', 'message' => 'Ya tienes un producto con este nombre']);
    exit;
}

// Verificar si el archivo de imagen es válido
$imagen = '';
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $nombre_imagen = time() . '_' . basename($_FILES['imagen']['name']);
    $ruta_destino = "../../../uploads/productos/" . $nombre_imagen;

    // Mover la imagen al directorio de destino
    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino)) {
        $imagen = $nombre_imagen;
        $imagen_alt = htmlspecialchars(strip_tags($_POST['imagen_alt'] ?? $nombre_producto));
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al subir la imagen']);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Se requiere una imagen válida']);
    exit;
}

// Preparar la consulta SQL para insertar el producto con datos SEO
$query = "INSERT INTO producto 
          (id_negocio, nombre_producto, slug, descripcion, costo, precio, stock, imagen, imagen_alt, estado, id_categoria, id_emprendedor, meta_title, meta_description) 
          VALUES 
          (:id_negocio, :nombre_producto, :slug, :descripcion, :costo, :precio, :stock, :imagen, :imagen_alt, :estado, :id_categoria, :id_emprendedor, :meta_title, :meta_description)";

$stmt = $db->prepare($query);

$stmt->bindParam(':id_negocio', $id_negocio);
$stmt->bindParam(':nombre_producto', $nombre_producto);
$stmt->bindParam(':slug', $slug);
$stmt->bindParam(':descripcion', $descripcion);
$stmt->bindParam(':costo', $costo);
$stmt->bindParam(':precio', $precio);
$stmt->bindParam(':stock', $stock);
$stmt->bindParam(':imagen', $imagen);
$stmt->bindParam(':imagen_alt', $imagen_alt);
$stmt->bindParam(':estado', $estado);
$stmt->bindParam(':id_categoria', $id_categoria);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->bindParam(':meta_title', $meta_title);
$stmt->bindParam(':meta_description', $meta_description);

// Ejecutar la consulta e informar el resultado
try {
    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Producto agregado exitosamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No se pudo agregar el producto']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
}
?>
