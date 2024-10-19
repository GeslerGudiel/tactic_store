<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

include_once '../../src/config/database.php';
include_once '../../src/config/funciones.php';

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = isset($_GET['id_emprendedor']) ? $_GET['id_emprendedor'] : die('ID de emprendedor no especificado.');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_suscripcion = htmlspecialchars(strip_tags($_POST['id_suscripcion']));

    // Asignar la suscripción al emprendedor
    $query = "UPDATE emprendedor SET id_suscripcion = :id_suscripcion WHERE id_emprendedor = :id_emprendedor";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_suscripcion', $id_suscripcion, PDO::PARAM_INT);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION['message'] = ['type' => 'success', 'text' => 'Suscripción asignada correctamente.'];
        // Enviar notificación al emprendedor
        $titulo = "Actualización de Suscripción";
        $mensaje = "Tu suscripción ha sido asignada/modificada. Revisa los detalles en tu panel de control.";
        agregarNotificacion($db, null, $id_emprendedor, $titulo, $mensaje);
    } else {
        $_SESSION['message'] = ['type' => 'error', 'text' => 'Error al asignar la suscripción.'];
    }

    header("Location: ver_emprendedores_suscripcion.php");
    exit;
}

// Obtener las suscripciones disponibles
$query_suscripciones = "SELECT * FROM suscripcion WHERE estado = 'activo'";
$stmt_suscripciones = $db->prepare($query_suscripciones);
$stmt_suscripciones->execute();
$suscripciones = $stmt_suscripciones->fetchAll(PDO::FETCH_ASSOC);

// Obtener el emprendedor actual
$query_emprendedor = "SELECT nombre1, apellido1 FROM emprendedor WHERE id_emprendedor = :id_emprendedor";
$stmt_emprendedor = $db->prepare($query_emprendedor);
$stmt_emprendedor->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
$stmt_emprendedor->execute();
$emprendedor = $stmt_emprendedor->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignar Suscripción</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container my-5">
        <h2 class="text-center mb-4">Asignar Suscripción a <?php echo htmlspecialchars($emprendedor['nombre1'] . ' ' . $emprendedor['apellido1']); ?></h2>

        <form action="asignar_suscripcion.php?id_emprendedor=<?php echo $id_emprendedor; ?>" method="POST">
            <div class="mb-3">
                <label for="id_suscripcion" class="form-label">Seleccionar Suscripción</label>
                <select id="id_suscripcion" name="id_suscripcion" class="form-select" required>
                    <?php foreach ($suscripciones as $suscripcion): ?>
                        <option value="<?php echo $suscripcion['id_suscripcion']; ?>">
                            <?php echo htmlspecialchars($suscripcion['tipo_suscripcion']); ?> - Q. <?php echo number_format($suscripcion['costo'], 2); ?> / <?php echo $suscripcion['duracion']; ?> meses
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Asignar Suscripción</button>
            <a href="ver_emprendedores_suscripcion.php" class="btn btn-secondary">Cancelar</a>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>