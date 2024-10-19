<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener la lista de emprendedores
$query = "SELECT e.id_emprendedor, e.nombre1, e.apellido1, s.tipo_suscripcion 
          FROM emprendedor e 
          LEFT JOIN suscripcion s ON e.id_suscripcion = s.id_suscripcion";
$stmt = $db->prepare($query);
$stmt->execute();
$emprendedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Emprendedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
<div class="container my-5">
    <h2 class="text-center mb-4"><i class="fas fa-user-tie"></i> Gestión de Emprendedores</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message']['type']; ?>">
            <?php echo $_SESSION['message']['text'];
            unset($_SESSION['message']); ?>
        </div>
    <?php endif; ?>

    <table class="table table-striped table-bordered">
        <thead>
        <tr>
            <th><i class="fas fa-user"></i> Nombre</th>
            <th><i class="fas fa-user"></i> Apellido</th>
            <th><i class="fas fa-tag"></i> Suscripción Actual</th>
            <th><i class="fas fa-tasks"></i> Acciones</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($emprendedores as $emprendedor): ?>
            <tr>
                <td><?php echo htmlspecialchars($emprendedor['nombre1']); ?></td>
                <td><?php echo htmlspecialchars($emprendedor['apellido1']); ?></td>
                <td><?php echo htmlspecialchars($emprendedor['tipo_suscripcion'] ?? 'Sin suscripción'); ?></td>
                <td>
                    <a href="asignar_suscripcion.php?id_emprendedor=<?php echo $emprendedor['id_emprendedor']; ?>" class="btn btn-primary btn-sm" title="Asignar/Modificar Suscripción">
                        <i class="fas fa-edit"></i> Asignar/Modificar Suscripción
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
