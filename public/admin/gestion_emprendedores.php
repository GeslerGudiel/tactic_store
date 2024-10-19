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

// Consulta para obtener todos los emprendedores y sus estados
$query = "SELECT e.id_emprendedor, e.nombre1, e.apellido1, e.correo, e.telefono1, e.id_estado_usuario, eu.nombre_estado 
          FROM emprendedor e 
          JOIN estado_usuario eu ON e.id_estado_usuario = eu.id_estado_usuario";
$stmt = $db->prepare($query);
$stmt->execute();
$emprendedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Función para actualizar el estado de un emprendedor
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_emprendedor']) && isset($_POST['nuevo_estado'])) {
    $id_emprendedor = $_POST['id_emprendedor'];
    $nuevo_estado = $_POST['nuevo_estado'];

    // Actualizar el estado del emprendedor
    $update_query = "UPDATE emprendedor SET id_estado_usuario = :nuevo_estado WHERE id_emprendedor = :id_emprendedor";
    $update_stmt = $db->prepare($update_query);
    $update_stmt->bindParam(':nuevo_estado', $nuevo_estado);
    $update_stmt->bindParam(':id_emprendedor', $id_emprendedor);

    if ($update_stmt->execute()) {
        // Devolver una respuesta en JSON para manejar con AJAX
        echo json_encode(['status' => 'success', 'message' => 'Estado actualizado exitosamente.']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el estado.']);
    }

    exit; // IDetener la ejecución y no cargar el HTML.
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Emprendedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .btn-custom {
            background-color: #1abc9c;
            color: white;
        }

        .btn-custom:hover {
            background-color: #16a085;
            color: white;
        }

        .table-responsive {
            overflow-x: auto;
        }

        @media (max-width: 768px) {
            .filter-container {
                flex-direction: column;
            }

            .filter-container .col-md-4 {
                margin-bottom: 1rem;
            }
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="text-center mb-4"><i class="fas fa-users"></i> Gestión de Emprendedores</h1>

        <!-- Búsqueda y Filtros -->
        <div class="row filter-container d-flex align-items-center justify-content-between mb-4">
            <div class="col-md-4">
                <label for="searchTerm" class="form-label"><i class="fas fa-search"></i> Buscar Emprendedor</label>
                <input type="text" id="searchTerm" class="form-control" placeholder="Nombre, Apellido o Correo">
            </div>
            <div class="col-md-4">
                <label for="estado" class="form-label"><i class="fas fa-filter"></i> Filtrar por Estado</label>
                <select id="estado" class="form-select">
                    <option value="">Todos los Estados</option>
                    <option value="1">Pendiente de Activación</option>
                    <option value="2">Activado</option>
                    <option value="3">Pendiente de Validación</option>
                    <option value="4">Desactivado</option>
                </select>
            </div>
            <div class="col-md-4 d-flex justify-content-end">
                <button id="btn-filtrar" class="btn btn-primary mt-4"><i class="fas fa-filter"></i> Filtrar</button>
            </div>
        </div>

        <div class="table-responsive" id="emprendedores-list">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th><i class="fas fa-id-card"></i> ID</th>
                        <th><i class="fas fa-user"></i> Nombre</th>
                        <th><i class="fas fa-envelope"></i> Correo</th>
                        <th><i class="fas fa-phone"></i> Teléfono</th>
                        <th><i class="fas fa-info-circle"></i> Estado Actual</th>
                        <th><i class="fas fa-tasks"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($emprendedores as $emprendedor): ?>
                        <tr>
                            <td><?php echo $emprendedor['id_emprendedor']; ?></td>
                            <td><?php echo htmlspecialchars($emprendedor['nombre1'] . " " . $emprendedor['apellido1']); ?></td>
                            <td><?php echo htmlspecialchars($emprendedor['correo']); ?></td>
                            <td><?php echo htmlspecialchars($emprendedor['telefono1']); ?></td>
                            <td><?php echo htmlspecialchars($emprendedor['nombre_estado']); ?></td>
                            <td class="text-center">
                                <!-- Botón para abrir el modal con los detalles del emprendedor -->
                                <button class="btn btn-info btn-sm revisar-emprendedor" data-id="<?php echo $emprendedor['id_emprendedor']; ?>">
                                    <i class="fas fa-search"></i> Revisar
                                </button>
                                <!-- Formulario para cambiar estado -->
                                <form method="POST" action="gestion_emprendedores.php" style="display:inline-block;">
                                    <select name="nuevo_estado" class="form-select form-select-sm d-inline-block" style="width: auto;">
                                        <option value="1" <?php echo $emprendedor['id_estado_usuario'] == 1 ? 'selected' : ''; ?>>Pendiente de Activación</option>
                                        <option value="2" <?php echo $emprendedor['id_estado_usuario'] == 2 ? 'selected' : ''; ?>>Activado</option>
                                        <option value="3" <?php echo $emprendedor['id_estado_usuario'] == 3 ? 'selected' : ''; ?>>Pendiente de Validación</option>
                                        <option value="4" <?php echo $emprendedor['id_estado_usuario'] == 4 ? 'selected' : ''; ?>>Desactivado</option>
                                    </select>
                                    <input type="hidden" name="id_emprendedor" value="<?php echo $emprendedor['id_emprendedor']; ?>">
                                    <button type="submit" class="btn btn-warning btn-sm">
                                        <i class="fas fa-sync-alt"></i> Actualizar Estado
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Modal para mostrar los detalles del emprendedor -->
        <div class="modal fade" id="modalEmprendedor" tabindex="-1" aria-labelledby="modalEmprendedorLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalEmprendedorLabel"><i class="fas fa-user"></i> Detalles del Emprendedor</h5>
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

    </div>

    <!-- jQuery, Bootstrap JS y SweetAlert -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Manejar el clic en el botón de "Revisar" para abrir el modal
        $(document).on('submit', 'form', function(event) {
            event.preventDefault(); // Evitar la recarga de página

            var formData = $(this).serialize(); // Captura los datos del formulario

            $.ajax({
                type: 'POST',
                url: $(this).attr('action'), // La acción del formulario
                data: formData,
                success: function(response) {
                    var result = JSON.parse(response);

                    // Cerrar cualquier modal abierto (en este caso, el modal de detalles del emprendedor)
                    $('#modalEmprendedor').modal('hide');

                    // Forzar la eliminación de backdrop (pantalla sombreada)
                    $('.modal-backdrop').remove(); // Elimina el backdrop que deja Bootstrap

                    Swal.fire({
                        icon: result.status === 'success' ? 'success' : 'error',
                        title: result.status === 'success' ? 'Éxito' : 'Error',
                        text: result.message
                    }).then(function() {
                        // Recargar la vista de emprendedores para reflejar los cambios después de cerrar el alert
                        $('#content-area').load('gestion_emprendedores.php');
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema con el servidor. Intenta nuevamente.'
                    });
                }
            });
        });


        $(document).on('click', '.revisar-emprendedor', function() {
            var id_emprendedor = $(this).data('id');

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
    </script>

    <script>
        $(document).ready(function() {
            // Al presionar Enter en el campo de búsqueda
            $('#searchTerm').on('keypress', function(event) {
                if (event.which === 13) { // Verificar si se presionó la tecla Enter
                    $('#btn-filtrar').click(); // Disparar el clic en el botón de filtrar
                }
            });

            // Al presionar Enter en el campo de filtro de estado
            $('#estado').on('keypress', function(event) {
                if (event.which === 13) { // Verificar si se presionó la tecla Enter
                    $('#btn-filtrar').click(); // Disparar el clic en el botón de filtrar
                }
            });

            // Función para el filtro y búsqueda de emprendedores
            $(document).on('click', '#btn-filtrar', function() {
                var searchTerm = $('#searchTerm').val(); // Captura el valor del campo de búsqueda
                var estado = $('#estado').val(); // Captura el valor del filtro de estado

                console.log("Search Term:", searchTerm); // Depuración: Verificar que el valor no sea undefined
                console.log("Estado:", estado); // Depuración: Verificar que el valor no sea undefined

                $.ajax({
                    url: 'buscar_emprendedores.php',
                    type: 'GET',
                    data: {
                        searchTerm: searchTerm,
                        estado: estado
                    },
                    success: function(response) {
                        console.log("Consulta realizada con éxito");
                        $('#emprendedores-list').html(response); // Actualiza la tabla con los resultados
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'No se pudieron cargar los emprendedores.'
                        });
                    }
                });
            });
        });
    </script>

</body>

</html>