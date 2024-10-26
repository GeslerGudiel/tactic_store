<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes Registrados</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <style>
        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .table thead th {
            background-color: #343a40;
            color: white;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h2><i class="fas fa-users"></i> Clientes Registrados</h2>

        <!-- Filtros de búsqueda -->
        <div class="row mb-3">
            <div class="col-md-4">
                <label for="buscar_nombre" class="form-label">Buscar por Nombre</label>
                <input type="text" id="buscar_nombre" class="form-control" placeholder="Escribe el nombre del cliente">
            </div>
            <div class="col-md-4">
                <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                <input type="date" id="fecha_inicio" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                <input type="date" id="fecha_fin" class="form-control">
            </div>
        </div>

        <!-- Tabla de clientes -->
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Correo</th>
                    <th>Teléfono</th>
                    <th>Dirección</th>
                    <th>Fecha de Registro</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="clientes-tbody">
                <!-- Los datos se cargarán dinámicamente aquí -->
            </tbody>
        </table>
    </div>

    <!-- Modal para editar cliente -->
    <div class="modal fade" id="modalEditarCliente" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="formEditarCliente">
                        <input type="hidden" id="id_cliente_emprendedor">
                        <div class="mb-3">
                            <label for="nombre_cliente" class="form-label">Nombre</label>
                            <input type="text" id="nombre_cliente" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="correo_cliente" class="form-label">Correo</label>
                            <input type="email" id="correo_cliente" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefono_cliente" class="form-label">Teléfono</label>
                            <input type="text" id="telefono_cliente" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="direccion_cliente" class="form-label">Dirección</label>
                            <input type="text" id="direccion_cliente" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            function cargarClientes() {
                const nombre = $('#buscar_nombre').val();
                const fechaInicio = $('#fecha_inicio').val();
                const fechaFin = $('#fecha_fin').val();

                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/venta_local/php/obtener_clientes.php',
                    method: 'GET',
                    dataType: 'json',
                    data: { nombre, fecha_inicio: fechaInicio, fecha_fin: fechaFin },
                    success: function (response) {
                        if (response.status === 'success') {
                            mostrarClientes(response.data);
                        } else {
                            Swal.fire('Error', response.error, 'error');
                        }
                    },
                    error: function (xhr) {
                        console.error('Error al cargar los clientes:', xhr.responseText);
                        Swal.fire('Error', 'Hubo un problema al cargar los clientes.', 'error');
                    }
                });
            }

            function mostrarClientes(clientes) {
                let filas = '';
                clientes.forEach(cliente => {
                    filas += `
                        <tr>
                            <td>${cliente.nombre_cliente}</td>
                            <td>${cliente.correo_cliente}</td>
                            <td>${cliente.telefono_cliente}</td>
                            <td>${cliente.direccion_cliente}</td>
                            <td>${cliente.fecha_registro}</td>
                            <td>
                                <button class="btn btn-warning btn-sm btn-editar" data-id="${cliente.id_cliente_emprendedor}" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-danger btn-sm btn-eliminar" data-id="${cliente.id_cliente_emprendedor}" title="Eliminar">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </td>
                        </tr>`;
                });
                $('#clientes-tbody').html(filas);
            }

            $('#buscar_nombre, #fecha_inicio, #fecha_fin').on('input change', cargarClientes);

            $(document).on('click', '.btn-editar', function () {
                const id = $(this).data('id');

                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/venta_local/php/obtener_clientes.php',
                    method: 'GET',
                    dataType: 'json',
                    data: { id_cliente_emprendedor: id },
                    success: function (response) {
                        const cliente = response.data[0];
                        $('#id_cliente_emprendedor').val(cliente.id_cliente_emprendedor);
                        $('#nombre_cliente').val(cliente.nombre_cliente);
                        $('#correo_cliente').val(cliente.correo_cliente);
                        $('#telefono_cliente').val(cliente.telefono_cliente);
                        $('#direccion_cliente').val(cliente.direccion_cliente);
                        $('#modalEditarCliente').modal('show');
                    }
                });
            });

            $('#formEditarCliente').on('submit', function (e) {
                e.preventDefault();

                const datos = {
                    id_cliente_emprendedor: $('#id_cliente_emprendedor').val(),
                    nombre_cliente: $('#nombre_cliente').val(),
                    correo_cliente: $('#correo_cliente').val(),
                    telefono_cliente: $('#telefono_cliente').val(),
                    direccion_cliente: $('#direccion_cliente').val()
                };

                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/venta_local/php/editar_cliente.php',
                    method: 'POST',
                    data: datos,
                    success: function () {
                        $('#modalEditarCliente').modal('hide');
                        cargarClientes();
                        Swal.fire('Éxito', 'Cliente actualizado correctamente.', 'success');
                    }
                });
            });

            $(document).on('click', '.btn-eliminar', function () {
                const id = $(this).data('id');

                Swal.fire({
                    title: '¿Estás seguro?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '/comercio_electronico/public/emprendedor/venta_local/php/eliminar_cliente.php',
                            method: 'POST',
                            data: { id_cliente_emprendedor: id },
                            success: function () {
                                cargarClientes();
                                Swal.fire('Eliminado', 'Cliente eliminado correctamente.', 'success');
                            }
                        });
                    }
                });
            });

            cargarClientes();
        });
    </script>

</body>

</html>
