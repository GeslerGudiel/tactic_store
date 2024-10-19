<?php
session_start();

// Verificar si el usuario ha iniciado sesión como administrador o superadmin
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

// Incluir la conexión a la base de datos
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener la información del administrador
$id_administrador = $_SESSION['usuario_id'];
$query = "SELECT nombre1, nombre2, apellido1, apellido2, telefono1, telefono2, correo FROM administrador WHERE id_administrador = :id_administrador";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_administrador', $id_administrador);
$stmt->execute();
$admin = $stmt->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <h1><i class="fas fa-user-circle"></i> Perfil del Administrador</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><i class="fas fa-user"></i> <?php echo htmlspecialchars($admin['nombre1']) . " " . htmlspecialchars($admin['apellido1']); ?></h5>
                <p class="card-text"><i class="fas fa-envelope"></i> <strong>Correo:</strong> <?php echo htmlspecialchars($admin['correo']); ?></p>
                <p class="card-text"><i class="fas fa-phone"></i> <strong>Teléfono 1:</strong> <?php echo htmlspecialchars($admin['telefono1']); ?></p>
                <p class="card-text"><i class="fas fa-phone"></i> <strong>Teléfono 2:</strong> <?php echo htmlspecialchars($admin['telefono2']); ?></p>
                <p class="card-text"><i class="fas fa-user-tie"></i> <strong>Nombre completo:</strong> <?php echo htmlspecialchars($admin['nombre1']) . " " . htmlspecialchars($admin['nombre2']) . " " . htmlspecialchars($admin['apellido1']) . " " . htmlspecialchars($admin['apellido2']); ?></p>
                <a href="editar_perfil_admin.php" class="btn btn-primary"><i class="fas fa-edit"></i> Editar Perfil</a>
            </div>
        </div>
    </div>
</body>
</html>
