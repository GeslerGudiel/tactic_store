<?php
session_start();

// Verificar si el usuario ha iniciado sesión como emprendedor
if (!isset($_SESSION['usuario_rol']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    header("Location: ../auth/login.php");
    exit;
}

// Verificar si el estado es "Pendiente de Validación"
if ($_SESSION['id_estado_usuario'] == 3) {
    header("Location: dashboard_emprendedor_perfil.php"); // Redirigir al dashboard completo si no está en pendiente de validación
    exit;
}

$_SESSION['LAST_ACTIVITY'] = time(); // Actualizamos el timestamp de actividad

include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

// Obtener los datos del perfil del emprendedor
$query = "SELECT nombre1, apellido1, correo FROM emprendedor WHERE id_emprendedor = :id_emprendedor";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->execute();
$emprendedor = $stmt->fetch(PDO::FETCH_ASSOC);

// Obtener notificaciones si es necesario (aunque en este caso no son visibles)
$notificaciones_no_leidas = 0;  // Para mantener el código que ya tienes, aunque no se usarán en este estado

$message = null;

// Verificar si hay un mensaje de sesión
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Emprendedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container mt-5">
        <h1>Perfil del Emprendedor</h1>
        <div class="card">
            <div class="card-body">
                <h5 class="card-title"><?php echo htmlspecialchars($emprendedor['nombre1']) . " " . htmlspecialchars($emprendedor['apellido1']); ?></h5>
                <p class="card-text"><strong>Correo:</strong> <?php echo htmlspecialchars($emprendedor['correo']); ?></p>
                <a href="editar_perfil_emprendedor.php" class="btn btn-primary">Editar Perfil</a>
            </div>
        </div>
    </div>

    <!-- Mensajes de alerta -->
    <?php if ($message): ?>
        <script>
            Swal.fire({
                icon: "success",
                title: "Atención",
                text: "<?php echo htmlspecialchars($message); ?>"
            });
        </script>
    <?php endif; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
