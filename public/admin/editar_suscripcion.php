<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

include_once '../../src/config/database.php';
$database = new Database();
$db = $database->getConnection();

$id_suscripcion = isset($_GET['id_suscripcion']) ? $_GET['id_suscripcion'] : die('ID de suscripción no especificado.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_suscripcion = htmlspecialchars(strip_tags($_POST['tipo_suscripcion']));
    $descripcion = htmlspecialchars(strip_tags($_POST['descripcion']));
    $costo = htmlspecialchars(strip_tags($_POST['costo']));
    $duracion = htmlspecialchars(strip_tags($_POST['duracion']));
    $estado = htmlspecialchars(strip_tags($_POST['estado']));

    $query = "UPDATE suscripcion SET tipo_suscripcion = :tipo_suscripcion, descripcion = :descripcion, costo = :costo, duracion = :duracion, estado = :estado 
              WHERE id_suscripcion = :id_suscripcion";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':tipo_suscripcion', $tipo_suscripcion);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':costo', $costo);
    $stmt->bindParam(':duracion', $duracion);
    $stmt->bindParam(':estado', $estado);
    $stmt->bindParam(':id_suscripcion', $id_suscripcion, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Suscripción actualizada correctamente.'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al actualizar la suscripción.'];
    }

    header("Location: ver_suscripciones_admin.php");
    exit;
} else {
    // Obtener la suscripción actual
    $query = "SELECT * FROM suscripcion WHERE id_suscripcion = :id_suscripcion";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_suscripcion', $id_suscripcion, PDO::PARAM_INT);
    $stmt->execute();
    $suscripcion = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!-- Formulario para editar la suscripción -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Suscripción</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2 class="text-center mb-4">Editar Suscripción</h2>
    <form action="editar_suscripcion.php?id_suscripcion=<?php echo $id_suscripcion; ?>" method="POST">
        <div class="mb-3">
            <label for="tipo_suscripcion" class="form-label">Tipo de Suscripción</label>
            <input type="text" class="form-control" id="tipo_suscripcion" name="tipo_suscripcion" value="<?php echo htmlspecialchars($suscripcion['tipo_suscripcion']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" required><?php echo htmlspecialchars($suscripcion['descripcion']); ?></textarea>
        </div>
        <div class="mb-3">
            <label for="costo" class="form-label">Costo</label>
            <input type="number" step="0.01" class="form-control" id="costo" name="costo" value="<?php echo htmlspecialchars($suscripcion['costo']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="duracion" class="form-label">Duración (meses)</label>
            <input type="number" class="form-control" id="duracion" name="duracion" value="<?php echo htmlspecialchars($suscripcion['duracion']); ?>" required>
        </div>
        <div class="mb-3">
            <label for="estado" class="form-label">Estado</label>
            <select class="form-select" id="estado" name="estado" required>
                <option value="activo" <?php if ($suscripcion['estado'] == 'activo') echo 'selected'; ?>>Activo</option>
                <option value="inactivo" <?php if ($suscripcion['estado'] == 'inactivo') echo 'selected'; ?>>Inactivo</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Suscripción</button>
        <a href="ver_suscripciones_admin.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
