<?php
session_start();
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Verificar si el cliente está conectado
if (!isset($_SESSION['id_cliente'])) {
    $_SESSION['message'] = [
        'type' => 'warning', 
        'text' => 'Debes iniciar sesión para acceder a tu perfil.'
    ];
    header("Location: login_cliente.php");
    exit;
}

// Obtener los datos del cliente
$id_cliente = $_SESSION['id_cliente'];
$query = $query = "SELECT c.nit, c.nombre1, c.nombre2, c.nombre3, c.apellido1, c.apellido2, c.correo, c.telefono1, c.telefono2, c.fecha_creacion, e.nombre_estado 
          FROM cliente c
          INNER JOIN estado_usuario e ON c.id_estado_usuario = e.id_estado_usuario
          WHERE id_cliente = :id_cliente";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_cliente', $id_cliente);
$stmt->execute();
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Hubo un problema al obtener los datos de tu perfil.'
    ];
    header("Location: principal_cliente.php");
    exit;
}

// Verificar si hay mensajes en la sesión para mostrar
$message = null;
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']); // Eliminar el mensaje después de mostrarlo
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"> <!-- Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="principal_cliente.php">Tienda Virtual</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="principal_cliente.php"><i class="fas fa-home"></i> Inicio</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="carrito.php">
                            <i class="fas fa-shopping-cart"></i> Carrito
                            <span class="badge bg-danger">
                                <?php echo isset($_SESSION['carrito']) ? array_sum(array_column($_SESSION['carrito'], 'cantidad')) : 0; ?>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout_cliente.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <h1 class="mb-4"><i class="fas fa-user"></i> Mi Perfil</h1>
        <div class="card">
            <div class="card-body">
                <table class="table table-hover">
                    <tr>
                        <th><i class="fa-solid fa-user-gear"></i></i> Estado de usuario:</th>
                        <td><?php echo htmlspecialchars($cliente['nombre_estado']); ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-envelope"></i> Correo Electrónico:</th>
                        <td><?php echo htmlspecialchars($cliente['correo']); ?></td>
                    </tr>
                    <tr>
                        <th><i class="fa-solid fa-file-contract"></i></i> NIT:</th>
                        <td><?php echo htmlspecialchars($cliente['nit']); ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-user"></i> Primer Nombre:</th>
                        <td><?php echo htmlspecialchars($cliente['nombre1']); ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-user"></i> Segundo Nombre:</th>
                        <td><?php echo htmlspecialchars($cliente['nombre2']); ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-user"></i> Tercer Nombre:</th>
                        <td><?php echo htmlspecialchars($cliente['nombre3']); ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-user"></i> Primer Apellido:</th>
                        <td><?php echo htmlspecialchars($cliente['apellido1']); ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-user"></i> Segundo Apellido:</th>
                        <td><?php echo htmlspecialchars($cliente['apellido2']); ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-phone"></i> Teléfono 1:</th>
                        <td><?php echo htmlspecialchars($cliente['telefono1']); ?></td>
                    </tr>
                    <tr>
                        <th><i class="fas fa-phone"></i> Teléfono 2:</th>
                        <td><?php echo htmlspecialchars($cliente['telefono2']); ?></td>
                    </tr>
                    <tr>
                        <th><i class="fa-regular fa-calendar-check"></i> Fecha de creación:</th>
                        <td><?php echo htmlspecialchars($cliente['fecha_creacion']); ?></td>
                    </tr>
                </table>
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editarPerfilModal">
                    <i class="fas fa-edit"></i> Editar Perfil
                </button>
            </div>
        </div>
    </div>

    <!-- Modal de Edición de Perfil -->
    <div class="modal fade" id="editarPerfilModal" tabindex="-1" aria-labelledby="editarPerfilModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editarPerfilModalLabel"><i class="fas fa-user-edit"></i> Editar Perfil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editarPerfilForm">
                        <!-- Campos del formulario parecido al código anterior -->
                        <div class="mb-3">
                            <label for="nombre1" class="form-label">Primer Nombre</label>
                            <input type="text" class="form-control" id="nombre1" name="nombre1" value="<?php echo htmlspecialchars($cliente['nombre1']); ?>" required>
                            <label for="nombre2" class="form-label">Segundo Nombre</label>
                            <input type="text" class="form-control" id="nombre2" name="nombre2" value="<?php echo htmlspecialchars($cliente['nombre2']); ?>" required>
                            <label for="nombre3" class="form-label">Tercer Nombre</label>
                            <input type="text" class="form-control" id="nombre3" name="nombre3" value="<?php echo htmlspecialchars($cliente['nombre3']); ?>">
                            <label for="apellido1" class="form-label">Primer Apellido</label>
                            <input type="text" class="form-control" id="apellido1" name="apellido1" value="<?php echo htmlspecialchars($cliente['apellido1']); ?>" required>
                            <label for="apellido2" class="form-label">Segundo Apellido</label>
                            <input type="text" class="form-control" id="apellido2" name="apellido2" value="<?php echo htmlspecialchars($cliente['apellido2']); ?>" required>
                            <label for="telefono1" class="form-label">Telefono 1</label>
                            <input type="text" class="form-control" id="telefono1" name="telefono1" value="<?php echo htmlspecialchars($cliente['telefono1']); ?>" required>
                            <label for="telefono2" class="form-label">Teléfono 2</label>
                            <input type="text" class="form-control" id="telefono2" name="telefono2" value="<?php echo htmlspecialchars($cliente['telefono2']); ?>">
                        </div>
                        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php if ($message): ?>
        <script>
            Swal.fire({
                icon: '<?php echo $message['type']; ?>',
                title: '<?php echo ucfirst($message['type']); ?>',
                text: '<?php echo $message['text']; ?>',
            });
        </script>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/js/all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        // Envío del formulario con AJAX
        $('#editarPerfilForm').on('submit', function(e) {
            e.preventDefault();
            var formData = $(this).serialize();

            $.ajax({
                type: 'POST',
                url: 'editar_perfil_cliente.php',
                data: formData,
                success: function(response) {
                    var data = JSON.parse(response);
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: data.message,
                        }).then(function() {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: data.message,
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al procesar tu solicitud.',
                    });
                }
            });
        });
    </script>
</body>

</html>