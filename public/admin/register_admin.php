<?php
session_start();

// Verificar si el usuario tiene rol de superadmin
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'superadmin') {
    // Redirigir si no es superadmin
    header("Location: login_admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container mt-5">
        <h2>Registrar Nuevo Administrador</h2>
        <form method="POST" action="register_process_admin.php">
            <div class="mb-3">
                <label for="nombre1" class="form-label">Primer Nombre</label>
                <input type="text" class="form-control" id="nombre1" name="nombre1" required>
            </div>
            <div class="mb-3">
                <label for="apellido1" class="form-label">Primer Apellido</label>
                <input type="text" class="form-control" id="apellido1" name="apellido1" required>
            </div>
            <div class="mb-3">
                <label for="correo" class="form-label">Correo Electrónico</label>
                <input type="email" class="form-control" id="correo" name="correo" required>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
            </div>
            <button type="submit" class="btn btn-primary">Registrar Administrador</button>
        </form>
    </div>
</body>
</html>
