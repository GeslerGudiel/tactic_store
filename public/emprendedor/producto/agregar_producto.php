<?php
session_start();
if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
    exit;
}

include_once '../../../src/config/database.php';

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
$descripcion = htmlspecialchars(strip_tags($_POST['descripcion']));
$costo = filter_var($_POST['costo'], FILTER_VALIDATE_FLOAT);
$precio = filter_var($_POST['precio'], FILTER_VALIDATE_FLOAT);
$stock = filter_var($_POST['stock'], FILTER_VALIDATE_INT);
$estado = htmlspecialchars(strip_tags($_POST['estado']));
$id_categoria = filter_var($_POST['id_categoria'], FILTER_VALIDATE_INT);

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
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al subir la imagen']);
        exit;
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Se requiere una imagen válida']);
    exit;
}

// Preparar la consulta SQL para insertar el producto
$query = "INSERT INTO producto 
          (id_negocio, nombre_producto, descripcion, costo, precio, stock, imagen, estado, id_categoria, id_emprendedor) 
          VALUES 
          (:id_negocio, :nombre_producto, :descripcion, :costo, :precio, :stock, :imagen, :estado, :id_categoria, :id_emprendedor)";

$stmt = $db->prepare($query);

$stmt->bindParam(':id_negocio', $id_negocio);
$stmt->bindParam(':nombre_producto', $nombre_producto);
$stmt->bindParam(':descripcion', $descripcion);
$stmt->bindParam(':costo', $costo);
$stmt->bindParam(':precio', $precio);
$stmt->bindParam(':stock', $stock);
$stmt->bindParam(':imagen', $imagen);
$stmt->bindParam(':estado', $estado);
$stmt->bindParam(':id_categoria', $id_categoria);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);

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
