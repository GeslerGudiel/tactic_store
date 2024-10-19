<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$message = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = htmlspecialchars(strip_tags($_POST['id_producto']));
    $nombre_producto = htmlspecialchars(strip_tags($_POST['nombre_producto']));
    $descripcion = htmlspecialchars(strip_tags($_POST['descripcion']));
    $precio = htmlspecialchars(strip_tags($_POST['precio']));
    $stock = htmlspecialchars(strip_tags($_POST['stock']));
    $estado = htmlspecialchars(strip_tags($_POST['estado']));
    $id_categoria = htmlspecialchars(strip_tags($_POST['id_categoria']));

    // Manejo de la imagen
    $imagen = $_POST['imagen_actual'];
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "../../uploads/productos/";
        $imagen = basename($_FILES["imagen"]["name"]);
        $target_file = $target_dir . $imagen;

        if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file)) {
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => "La imagen " . htmlspecialchars($imagen) . " ha sido subida correctamente."
            ];
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => "Hubo un error subiendo la imagen."
            ];
            header("Location: editar_producto.php?id=$id_producto");
            exit;
        }
    }

    try {
        $query = "UPDATE producto SET nombre_producto = :nombre_producto, descripcion = :descripcion, precio = :precio, stock = :stock, imagen = :imagen, estado = :estado, id_categoria = :id_categoria WHERE id_producto = :id_producto";
        $stmt = $db->prepare($query);

        $stmt->bindParam(':id_producto', $id_producto);
        $stmt->bindParam(':nombre_producto', $nombre_producto);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':precio', $precio);
        $stmt->bindParam(':stock', $stock);
        $stmt->bindParam(':imagen', $imagen);
        $stmt->bindParam(':estado', $estado);
        $stmt->bindParam(':id_categoria', $id_categoria);

        if ($stmt->execute()) {
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Producto actualizado exitosamente.'
            ];
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Error al actualizar el producto.'
            ];
        }

        header("Location: ver_productos_admin.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => "Error: " . $e->getMessage()
        ];
        header("Location: editar_producto.php?id=$id_producto");
        exit;
    }
} else {
    $id_producto = htmlspecialchars(strip_tags($_GET['id_producto']));

    // Obtener el producto actual
    $query = "SELECT * FROM producto WHERE id_producto = :id_producto";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_producto', $id_producto);
    $stmt->execute();

    $producto = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obtener las categorías
    $query = "SELECT * FROM categoria";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $categoria = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container">
        <div class="card mt-5">
            <div class="card-header bg-primary text-white">
                <h2 class="text-center">Editar Producto</h2>
            </div>
            <div class="card-body">
                <form action="editar_producto.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                    <input type="hidden" name="imagen_actual" value="<?php echo $producto['imagen']; ?>">

                    <div class="mb-3">
                        <label for="nombre_producto" class="form-label">Nombre del Producto:</label>
                        <input type="text" id="nombre_producto" name="nombre_producto" class="form-control" value="<?php echo $producto['nombre_producto']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción:</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" required><?php echo $producto['descripcion']; ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="precio" class="form-label">Precio:</label>
                        <input type="text" id="precio" name="precio" class="form-control" value="<?php echo $producto['precio']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock:</label>
                        <input type="number" id="stock" name="stock" class="form-control" value="<?php echo $producto['stock']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="imagen" class="form-label">Imagen del Producto:</label>
                        <input type="file" id="imagen" name="imagen" class="form-control" accept="image/*">
                        <img src="../../uploads/productos/<?php echo $producto['imagen']; ?>" alt="Imagen del Producto" width="100" class="mt-3">
                    </div>

                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado:</label>
                        <select id="estado" name="estado" class="form-select" required>
                            <option value="disponible" <?php if ($producto['estado'] == 'disponible') echo 'selected'; ?>>Disponible</option>
                            <option value="no disponible" <?php if ($producto['estado'] == 'no disponible') echo 'selected'; ?>>No disponible</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="id_categoria" class="form-label">Categoría:</label>
                        <select id="id_categoria" name="id_categoria" class="form-select" required>
                            <?php foreach ($categoria as $categoria): ?>
                                <option value="<?php echo $categoria['id_categoria']; ?>" <?php if ($producto['id_categoria'] == $categoria['id_categoria']) echo 'selected'; ?>>
                                    <?php echo $categoria['nombre_categoria']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Actualizar Producto</button>
                    <a href="ver_productos_admin.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancelar</a>
                </form>
            </div>
        </div>
    </div>

    <!-- Manejar mensajes de sesión con SweetAlert2 -->
    <?php if (isset($_SESSION['message'])): ?>
        <script>
            Swal.fire({
                icon: "<?php echo $_SESSION['message']['type']; ?>",
                title: "<?php echo ucfirst($_SESSION['message']['type']); ?>",
                text: "<?php echo $_SESSION['message']['text']; ?>"
            });
            <?php unset($_SESSION['message']); ?>
        </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>
</html>
