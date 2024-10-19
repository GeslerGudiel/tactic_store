<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

include_once '../../src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_suscripcion = htmlspecialchars(strip_tags($_POST['tipo_suscripcion']));
    $descripcion = htmlspecialchars(strip_tags($_POST['descripcion']));
    $costo = htmlspecialchars(strip_tags($_POST['precio']));
    $duracion = htmlspecialchars(strip_tags($_POST['duracion']));
    $estado = htmlspecialchars(strip_tags($_POST['estado']));

    $database = new Database();
    $db = $database->getConnection();

    $query = "INSERT INTO suscripcion (tipo_suscripcion, descripcion, costo, duracion, estado) 
              VALUES (:tipo_suscripcion, :descripcion, :costo, :duracion, :estado)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':tipo_suscripcion', $tipo_suscripcion);
    $stmt->bindParam(':descripcion', $descripcion);
    $stmt->bindParam(':costo', $costo);
    $stmt->bindParam(':duracion', $duracion);
    $stmt->bindParam(':estado', $estado);

    if ($stmt->execute()) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Suscripción creada exitosamente.'];
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al crear la suscripción.'];
    }

    header("Location: ver_suscripciones_admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Suscripción</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container my-5">
    <h2 class="text-center mb-4">Crear Suscripción</h2>
    <form action="crear_suscripcion.php" method="POST">
        <div class="mb-3">
            <label for="tipo_suscripcion" class="form-label">Tipo de Suscripción</label>
            <input type="text" class="form-control" id="tipo_suscripcion" name="tipo_suscripcion" required>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción</label>
            <textarea class="form-control" id="descripcion" name="descripcion" required></textarea>
        </div>
        <div class="mb-3">
            <label for="costo" class="form-label">Costo</label>
            <input type="number" step="0.01" class="form-control" id="costo" name="costo" required>
        </div>
        <div class="mb-3">
            <label for="duracion" class="form-label">Duración (meses)</label>
            <input type="number" class="form-control" id="duracion" name="duracion" required>
        </div>
        <div class="mb-3">
            <label for="estado" class="form-label">Estado</label>
            <select class="form-select" id="estado" name="estado" required>
                <option value="activo">Activo</option>
                <option value="inactivo">Inactivo</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Crear Suscripción</button>
        <a href="ver_suscripciones_admin.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
