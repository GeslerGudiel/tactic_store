<?php
session_start();

// Verificar que el usuario es administrador o superadmin
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

// Incluir la conexión a la base de datos
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener todos los emprendedores pendientes de validación
$query = "SELECT * FROM emprendedor WHERE id_estado_usuario = 3";
$stmt = $db->prepare($query);
$stmt->execute();
$emprendedores = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revisión de Emprendedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body>
    <div class="container mt-5">
        <h1><i class="fas fa-user-check"></i> Revisión de Emprendedores</h1>
        <?php if (count($emprendedores) > 0): ?>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th><i class="fas fa-user"></i> Nombre</th>
                        <th><i class="fas fa-envelope"></i> Correo</th>
                        <th><i class="fas fa-info-circle"></i> Estado</th>
                        <th><i class="fas fa-tasks"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emprendedores as $emprendedor): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($emprendedor['nombre1'] . " " . $emprendedor['apellido1']); ?></td>
                            <td><?php echo htmlspecialchars($emprendedor['correo']); ?></td>
                            <td><i class="fas fa-hourglass-start"></i> Pendiente de Validación</td>
                            <td>
                                <!-- Botón para abrir el modal con los detalles del emprendedor -->
                                <button class="btn btn-primary revisar-emprendedor" data-id="<?php echo $emprendedor['id_emprendedor']; ?>">
                                    <i class="fas fa-search"></i> Revisar
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="alert alert-info"><i class="fas fa-info-circle"></i> No hay emprendedores pendientes de validación.</p>
        <?php endif; ?>
    </div>

    <!-- Modal para mostrar los detalles del emprendedor -->
    <div class="modal fade" id="modalEmprendedor" tabindex="-1" aria-labelledby="modalEmprendedorLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEmprendedorLabel">Detalles del Emprendedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="emprendedor-detalles">
                    <!-- Aquí se cargarán los detalles del emprendedor -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery y Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Manejar el clic en el botón de "Revisar" para abrir el modal
        $(document).on('click', '.revisar-emprendedor', function() {
            var id_emprendedor = $(this).data('id');

            // Cargar los detalles del emprendedor
            $.ajax({
                url: 'revisar_detalles_emprendedor.php',
                type: 'GET',
                data: {
                    id: id_emprendedor
                },
                success: function(response) {
                    $('#emprendedor-detalles').html(response);
                    $('#modalEmprendedor').modal('show');
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo cargar los detalles del emprendedor.'
                    });
                }
            });
        });

        // Cerrar el modal y eliminar backdrop
        $('#modalEmprendedor').on('hidden.bs.modal', function() {
            $('.modal-backdrop').remove(); // Elimina el backdrop
        });
    </script>
</body>

</html>