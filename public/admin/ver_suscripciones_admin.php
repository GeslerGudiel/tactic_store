<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener todas las suscripciones
$query = "SELECT * FROM suscripcion";
$stmt = $db->prepare($query);
$stmt->execute();
$suscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Suscripciones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="container my-5">
        <h2 class="text-center mb-4"><i class="fas fa-tags"></i> Gestión de Suscripciones</h2>

        <!-- Mostrar mensaje de éxito o error -->
        <?php if (isset($_SESSION['message'])): ?>
            <div class="alert alert-<?php echo $_SESSION['message']['type']; ?>">
                <?php echo $_SESSION['message']['text']; ?>
                <?php unset($_SESSION['message']); ?>
            </div>
        <?php endif; ?>

        <a href="crear_suscripcion.php" class="btn btn-success mb-3">
            <i class="fas fa-plus-circle"></i> Agregar Nueva Suscripción
        </a>

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th><i class="fas fa-hashtag"></i> ID</th>
                    <th><i class="fas fa-tag"></i> Tipo de Suscripción</th>
                    <th><i class="fas fa-align-left"></i> Descripción</th>
                    <th><i class="fas fa-money-bill-wave"></i> Precio</th>
                    <th><i class="fas fa-clock"></i> Duración (meses)</th>
                    <th><i class="fas fa-info-circle"></i> Estado</th>
                    <th><i class="fas fa-tasks"></i> Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suscripciones as $suscripcion): ?>
                    <tr>
                        <td><?php echo $suscripcion['id_suscripcion']; ?></td>
                        <td><?php echo htmlspecialchars($suscripcion['tipo_suscripcion']); ?></td>
                        <td><?php echo htmlspecialchars($suscripcion['descripcion']); ?></td>
                        <td>Q. <?php echo number_format($suscripcion['costo'], 2); ?></td>
                        <td><?php echo htmlspecialchars($suscripcion['duracion']); ?></td>
                        <td><?php echo htmlspecialchars($suscripcion['estado']); ?></td>
                        <td>
                            <a href="editar_suscripcion.php?id_suscripcion=<?php echo $suscripcion['id_suscripcion']; ?>" class="btn btn-primary btn-sm" title="Editar">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="eliminar_suscripcion.php?id_suscripcion=<?php echo $suscripcion['id_suscripcion']; ?>" class="btn btn-danger btn-sm" title="Eliminar" onclick="return confirm('¿Estás seguro de eliminar esta suscripción?');">
                                <i class="fas fa-trash-alt"></i> Eliminar
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>