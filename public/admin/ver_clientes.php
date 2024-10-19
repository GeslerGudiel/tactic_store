<?php
session_start();

// Verificar si el usuario tiene rol de administrador o superadmin
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener los estados de usuario para el filtro
$query_estados = "SELECT * FROM estado_usuario";
$stmt_estados = $db->prepare($query_estados);
$stmt_estados->execute();
$estados = $stmt_estados->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrar Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container mt-5">
        <h2 class="mb-4 text-center"><i class="fas fa-users"></i> Administración de Clientes</h2>

        <!-- Formulario de búsqueda y filtro por estado -->
        <form id="filtro-clientes" class="mb-4">
            <div class="input-group mb-3">
                <input type="text" name="buscar" id="buscar" class="form-control" placeholder="Buscar clientes por nombre, NIT o correo">
                <select name="estado" id="estado" class="form-select">
                    <option value="">Filtrar por estado</option>
                    <?php foreach ($estados as $estado): ?>
                        <option value="<?php echo $estado['id_estado_usuario']; ?>">
                            <?php echo $estado['nombre_estado']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> Buscar</button>
            </div>
        </form>

        <div id="clientes-list">
            <!-- Carga de los clientes -->

        </div>

        <!-- Modal para ver detalles del cliente -->
        <div class="modal fade" id="modal-detalle-cliente" tabindex="-1" aria-labelledby="detalleClienteLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered"> <!-- Cambio del tamaño y la alineación del modal -->
                <div class="modal-content shadow-lg rounded-4"> <!-- Sombra y bordes redondeados -->
                    <div class="modal-header bg-info text-white"> <!-- Se agrega color de fondo en el encabezado -->
                        <h5 class="modal-title" id="detalleClienteLabel"><i class="fas fa-info-circle"></i> Detalles del Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body p-4"> <!-- Espaciado adicional en el cuerpo -->
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>NIT:</strong> <span id="detalle-nit"></span></p>
                                <p><strong>Nombre Completo:</strong> <span id="detalle-nombre"></span></p>
                                <p><strong>Correo Electrónico:</strong> <span id="detalle-correo"></span></p>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Teléfono 1:</strong> <span id="detalle-telefono1"></span></p>
                                <p><strong>Teléfono 2:</strong> <span id="detalle-telefono2"></span></p>
                                <p><strong>Dirección:</strong> <span id="detalle-direccion"></span></p>
                            </div>
                        </div>
                        <p><strong>Estado:</strong> <span id="detalle-estado"></span></p>
                        <p><strong>Fecha de Creación:</strong> <span id="detalle-fecha"></span></p>
                    </div>
                    <div class="modal-footer d-flex justify-content-end">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal para editar cliente -->
        <div class="modal fade" id="modal-editar-cliente" tabindex="-1" aria-labelledby="editarClienteLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered"> <!-- Cambio en el tamaño y la alineación del modal -->
                <div class="modal-content shadow-lg rounded-4"> <!-- Sombra y bordes redondeados -->
                    <div class="modal-header bg-primary text-white"> <!-- Se agrega el color de fondo en el encabezado -->
                        <h5 class="modal-title" id="editarClienteLabel"><i class="fas fa-edit"></i> Editar Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body p-4"> <!-- Espaciado adicional en el cuerpo -->
                        <form id="form-editar-cliente">
                            <input type="hidden" id="id_cliente" name="id_cliente">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="NIT" class="form-label"><i class="fas fa-id-card"></i> NIT</label>
                                        <input type="text" id="NIT" name="NIT" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="correo" class="form-label"><i class="fas fa-envelope"></i> Correo Electrónico</label>
                                        <input type="email" id="correo" name="correo" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="nombre1" class="form-label"><i class="fas fa-user"></i> Primer Nombre</label>
                                        <input type="text" id="nombre1" name="nombre1" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="nombre2" class="form-label"><i class="fas fa-user"></i> Segundo Nombre</label>
                                        <input type="text" id="nombre2" name="nombre2" class="form-control">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="nombre3" class="form-label"><i class="fas fa-user"></i> Tercer Nombre</label>
                                        <input type="text" id="nombre3" name="nombre3" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="apellido1" class="form-label"><i class="fas fa-user"></i> Primer Apellido</label>
                                        <input type="text" id="apellido1" name="apellido1" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="apellido2" class="form-label"><i class="fas fa-user"></i> Segundo Apellido</label>
                                        <input type="text" id="apellido2" name="apellido2" class="form-control" required>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telefono1" class="form-label"><i class="fas fa-phone"></i> Teléfono 1</label>
                                        <input type="text" id="telefono1" name="telefono1" class="form-control" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telefono2" class="form-label"><i class="fas fa-phone-alt"></i> Teléfono 2</label>
                                        <input type="text" id="telefono2" name="telefono2" class="form-control">
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="id_direccion" class="form-label"><i class="fas fa-map-marker-alt"></i> Dirección</label>
                                <select id="id_direccion" name="id_direccion" class="form-select" required>
                                    <!-- Carga las opciones de direcciones -->
                                </select>
                            </div>

                            <div class="modal-footer d-flex justify-content-end">
                                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Guardar Cambios</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- Cargar scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Función para cargar los clientes
        function cargarClientes() {
            $.ajax({
                url: "ver_clientes_ajax.php",
                method: "GET",
                data: $("#filtro-clientes").serialize(),
                success: function(data) {
                    $("#clientes-list").html(data);
                },
                error: function() {
                    Swal.fire('Error', 'Hubo un error al cargar los clientes', 'error');
                }
            });
        }

        $(document).ready(function() {
            // Cargar los clientes al iniciar la página
            cargarClientes();

            // Buscar clientes y filtrar por estado al enviar el formulario
            $("#filtro-clientes").on("submit", function(e) {
                e.preventDefault();
                cargarClientes();
            });

            // Eliminar cliente
            $(document).on("click", ".eliminar-cliente", function(e) {
                e.preventDefault();
                var id_cliente = $(this).data("id");

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "¡No podrás revertir esto!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminarlo!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "eliminar_cliente_ajax.php",
                            method: "POST",
                            data: {
                                id_cliente: id_cliente
                            },
                            success: function(response) {
                                var res = JSON.parse(response);
                                if (res.status === 'success') {
                                    Swal.fire('Eliminado!', res.message, 'success');
                                    cargarClientes();
                                } else {
                                    Swal.fire('Error!', res.message, 'error');
                                }
                            }
                        });
                    }
                });
            });

            // Cambiar estado del cliente
            $(document).on("click", ".cambiar-estado", function(e) {
                e.preventDefault();
                var id_cliente = $(this).data("id");

                $.ajax({
                    url: "cambiar_estado_cliente_ajax.php",
                    method: "POST",
                    data: {
                        id_cliente: id_cliente
                    },
                    success: function(response) {
                        var res = JSON.parse(response);
                        if (res.status === 'success') {
                            Swal.fire('Actualizado!', res.message, 'success');
                            cargarClientes();
                        } else {
                            Swal.fire('Error!', res.message, 'error');
                        }
                    }
                });
            });

            // Ver detalles del cliente
            $(document).on("click", ".ver-detalles-cliente", function(e) {
                e.preventDefault();
                var id_cliente = $(this).data("id");

                // Obtener los detalles del cliente
                $.ajax({
                    url: "ver_detalle_cliente_ajax.php",
                    method: "POST",
                    data: {
                        id_cliente: id_cliente
                    },
                    success: function(response) {
                        var res = JSON.parse(response);
                        if (res.status === 'success') {
                            // Cargar los detalles en el modal
                            $("#detalle-nit").text(res.data.NIT);
                            $("#detalle-nombre").text(res.data.nombre1 + ' ' + res.data.nombre2 + ' ' + res.data.nombre3 + ' ' + res.data.apellido1 + ' ' + res.data.apellido2);
                            $("#detalle-correo").text(res.data.correo);
                            $("#detalle-telefono1").text(res.data.telefono1);
                            $("#detalle-telefono2").text(res.data.telefono2);
                            $("#detalle-direccion").text(res.data.localidad + ', ' + res.data.municipio + ', ' + res.data.departamento);
                            $("#detalle-estado").text(res.data.estado_nombre);
                            $("#detalle-fecha").text(res.data.fecha_creacion);

                            // Mostrar el modal
                            $("#modal-detalle-cliente").modal("show");
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Hubo un error al obtener los detalles del cliente.', 'error');
                    }
                });
            });

            // Editar cliente: Abrir modal y cargar datos del cliente seleccionado
            $(document).on("click", ".editar-cliente", function(e) {
                e.preventDefault();
                var id_cliente = $(this).data("id");

                // Obtener datos del cliente
                $.ajax({
                    url: "ver_detalle_cliente_ajax.php",
                    method: "POST",
                    data: {
                        id_cliente: id_cliente
                    },
                    success: function(response) {
                        var res = JSON.parse(response);
                        if (res.status === 'success') {
                            // Cargar los datos en el formulario
                            $("#id_cliente").val(res.data.id_cliente);
                            $("#NIT").val(res.data.NIT);
                            $("#nombre1").val(res.data.nombre1);
                            $("#nombre2").val(res.data.nombre2);
                            $("#nombre3").val(res.data.nombre3);
                            $("#apellido1").val(res.data.apellido1);
                            $("#apellido2").val(res.data.apellido2);
                            $("#telefono1").val(res.data.telefono1);
                            $("#telefono2").val(res.data.telefono2);
                            $("#correo").val(res.data.correo);

                            // Cargar direcciones disponibles
                            $.ajax({
                                url: "cargar_direcciones_ajax.php",
                                method: "GET",
                                success: function(direcciones) {
                                    var options = '';
                                    direcciones = JSON.parse(direcciones);
                                    $.each(direcciones, function(index, direccion) {
                                        var selected = (direccion.id_direccion == res.data.id_direccion) ? 'selected' : '';
                                        options += `<option value="${direccion.id_direccion}" ${selected}>${direccion.localidad}, ${direccion.municipio}, ${direccion.departamento}</option>`;
                                    });
                                    $("#id_direccion").html(options);
                                }
                            });

                            // Mostrar el modal de edición
                            $("#modal-editar-cliente").modal("show");
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    }
                });
            });

            // Enviar los datos del formulario de edición
            $("#form-editar-cliente").on("submit", function(e) {
                e.preventDefault();

                // Enviar datos actualizados
                $.ajax({
                    url: "actualizar_cliente_ajax.php",
                    method: "POST",
                    data: $(this).serialize(),
                    success: function(response) {
                        var res = JSON.parse(response);
                        if (res.status === 'success') {
                            Swal.fire('Éxito', res.message, 'success');
                            // Cerrar el modal y recargar la lista de clientes
                            $("#modal-editar-cliente").modal("hide");
                            cargarClientes(); // Actualiza la lista de clientes después de guardar cambios
                        } else {
                            Swal.fire('Error', res.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Hubo un error al actualizar el cliente.', 'error');
                    }
                });
            });
        });
    </script>
</body>

</html>