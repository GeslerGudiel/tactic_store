<?php
session_start();
include_once '../../src/config/database.php';

// Verificar si el usuario es administrador o superadmin
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Manejo de mensajes
$message = '';

// Obtener todas las categorías para seleccionar en el formulario
$query_categorias = "SELECT * FROM categoria";
$stmt_categorias = $db->prepare($query_categorias);
$stmt_categorias->execute();
$categorias = $stmt_categorias->fetchAll(PDO::FETCH_ASSOC);

// Verificar si se envió el formulario para agregar o editar una subcategoría
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_subcategoria = htmlspecialchars(strip_tags($_POST['nombre_subcategoria']));
    $descripcion_subcategoria = htmlspecialchars(strip_tags($_POST['descripcion_subcategoria']));
    $id_categoria = (int)$_POST['id_categoria'];
    $id_subcategoria = isset($_POST['id_subcategoria']) ? (int)$_POST['id_subcategoria'] : 0;

    if (!empty($nombre_subcategoria) && $id_categoria > 0) {
        if ($id_subcategoria > 0) {
            // Editar subcategoría existente
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
            $message = $id_subcategoria > 0 ? 'Subcategoría actualizada correctamente' : 'Subcategoría creada correctamente';
        } else {
            $message = 'Error al guardar la subcategoría';
        }
    } else {
        $message = 'El nombre de la subcategoría y la categoría son obligatorios';
    }
}

// Eliminar subcategoría
if (isset($_GET['eliminar'])) {
    $id_subcategoria = (int)$_GET['eliminar'];

    // Eliminar subcategoría por ID
    $query = "DELETE FROM subcategoria WHERE id_subcategoria = :id_subcategoria";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_subcategoria', $id_subcategoria);

    if ($stmt->execute()) {
        $message = 'Subcategoría eliminada correctamente';
    } else {
        $message = 'Error al eliminar la subcategoría';
    }
}

// Obtener las subcategorías existentes
$query_subcategorias = "SELECT subcategoria.*, categoria.nombre_categoria FROM subcategoria INNER JOIN categoria ON subcategoria.id_categoria = categoria.id_categoria";
$stmt_subcategorias = $db->prepare($query_subcategorias);
$stmt_subcategorias->execute();
$subcategorias = $stmt_subcategorias->fetchAll(PDO::FETCH_ASSOC);

// Obtener subcategoría para edición si está presente en la URL
$subcategoria_a_editar = null;
if (isset($_GET['editar'])) {
    $id_subcategoria_editar = (int)$_GET['editar'];
    $query_editar = "SELECT * FROM subcategoria WHERE id_subcategoria = :id_subcategoria";
    $stmt_editar = $db->prepare($query_editar);
    $stmt_editar->bindParam(':id_subcategoria', $id_subcategoria_editar);
    $stmt_editar->execute();
    $subcategoria_a_editar = $stmt_editar->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Subcategorías</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h3><?php echo isset($subcategoria_a_editar) ? 'Editar Subcategoría' : 'Crear Subcategoría'; ?></h3>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info">
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Formulario para crear o editar una subcategoría -->
        <form action="gestionar_subcategorias.php" method="POST">
            <input type="hidden" name="id_subcategoria" value="<?php echo isset($subcategoria_a_editar) ? $subcategoria_a_editar['id_subcategoria'] : ''; ?>">
            <div class="mb-3">
                <label for="id_categoria" class="form-label">Categoría</label>
                <select class="form-select" id="id_categoria" name="id_categoria" required>
                    <option value="">Selecciona una categoría</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo $categoria['id_categoria']; ?>" <?php echo (isset($subcategoria_a_editar) && $subcategoria_a_editar['id_categoria'] == $categoria['id_categoria']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="nombre_subcategoria" class="form-label">Nombre de la Subcategoría</label>
                <input type="text" class="form-control" id="nombre_subcategoria" name="nombre_subcategoria" value="<?php echo isset($subcategoria_a_editar) ? htmlspecialchars($subcategoria_a_editar['nombre_subcategoria']) : ''; ?>" required>
            </div>
            <div class="mb-3">
                <label for="descripcion_subcategoria" class="form-label">Descripción de la Subcategoría</label>
                <textarea class="form-control" id="descripcion_subcategoria" name="descripcion_subcategoria" rows="3"><?php echo isset($subcategoria_a_editar) ? htmlspecialchars($subcategoria_a_editar['descripcion_subcategoria']) : ''; ?></textarea>
            </div>
            <button type="submit" class="btn btn-primary"><?php echo isset($subcategoria_a_editar) ? 'Actualizar Subcategoría' : 'Crear Subcategoría'; ?></button>
            <?php if (isset($subcategoria_a_editar)): ?>
                <a href="gestionar_subcategorias.php" class="btn btn-secondary">Cancelar</a>
            <?php endif; ?>
        </form>

        <!-- Listado de subcategorías existentes -->
        <h3 class="mt-4">Subcategorías Existentes</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Subcategoría</th>
                    <th>Categoría</th>
                    <th>Descripción</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subcategorias as $subcategoria): ?>
                    <tr>
                        <td><?php echo $subcategoria['id_subcategoria']; ?></td>
                        <td><?php echo htmlspecialchars($subcategoria['nombre_subcategoria']); ?></td>
                        <td><?php echo htmlspecialchars($subcategoria['nombre_categoria']); ?></td>
                        <td><?php echo htmlspecialchars($subcategoria['descripcion_subcategoria']); ?></td>
                        <td>
                            <a href="gestionar_subcategorias.php?editar=<?php echo $subcategoria['id_subcategoria']; ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="gestionar_subcategorias.php?eliminar=<?php echo $subcategoria['id_subcategoria']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar esta subcategoría?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
