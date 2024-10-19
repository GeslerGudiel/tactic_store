<?php
session_start();

// Verificar si el usuario tiene rol de administrador o superadmin
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

// Incluir la conexión a la base de datos
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Consulta para obtener todos los usuarios
$query = "SELECT id_cliente, nombre1, apellido1, correo, telefono1 FROM cliente";
$stmt = $db->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Gestión de Usuarios</h1>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td><?php echo $usuario['id_cliente']; ?></td>
                        <td><?php echo htmlspecialchars($usuario['nombre1']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['apellido1']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['correo']); ?></td>
                        <td><?php echo htmlspecialchars($usuario['telefono1']); ?></td>
                        <td>
                            <!-- Agregar enlaces para editar o eliminar usuario -->
                            <a href="editar_usuario.php?id=<?php echo $usuario['id_cliente']; ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="eliminar_usuario.php?id=<?php echo $usuario['id_cliente']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de eliminar este usuario?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
