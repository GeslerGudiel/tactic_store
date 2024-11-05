<?php
session_start();
if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    header("Location: ../auth/login.php");
    exit;
}

include_once '../../../../src/config/database.php';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container my-4">
        <h2 class="text-center">Clientes del Emprendedor</h2>
        <!-- Selector de mes -->
        <div class="mb-3">
            <label for="filtroMes" class="form-label">Filtrar por mes de creación:</label>
            <select id="filtroMes" class="form-select">
                <!-- Opciones de meses serán generadas dinámicamente -->
            </select>
        </div>

        <button class="btn btn-primary mb-4" id="btnAgregarCliente">Agregar Cliente</button>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID Cliente</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="tablaClientes">
                    <!-- Aquí se cargarán los clientes de forma dinámica -->
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para agregar y editar clientes -->
    <div class="modal fade" id="clienteModal" tabindex="-1" aria-labelledby="clienteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clienteModalLabel">Agregar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formCliente">
                        <input type="hidden" id="clienteId">
                        <div class="mb-3">
                            <label for="nombre_cliente" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre_cliente" name="nombre_cliente" required>
                        </div>
                        <div class="mb-3">
                            <label for="correo_cliente" class="form-label">Correo</label>
                            <input type="email" class="form-control" id="correo_cliente" name="correo_cliente" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefono_cliente" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono_cliente" name="telefono_cliente" required>
                        </div>
                        <div class="mb-3">
                            <label for="direccion_cliente" class="form-label">Dirección</label>
                            <input type="text" class="form-control" id="direccion_cliente" name="direccion_cliente">
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            const currentMonth = new Date().toISOString().slice(0, 7); // Mes actual

            // Cargar clientes con el mes seleccionado
            function cargarClientes(mes = currentMonth) {
                $.get('/comercio_electronico/public/emprendedor/venta_local/clientes/obtener_clientes.php', {
                    mes: mes
                }, function(data) {
                    $('#tablaClientes').html(data);
                });
            }

            // Cargar los meses de creación de clientes en el select
            function cargarMeses() {
                $.get('/comercio_electronico/public/emprendedor/venta_local/clientes/obtener_clientes.php', {
                    meses: true
                }, function(response) {
                    if (response.success) {
                        const selectMes = $('#filtroMes');
                        selectMes.empty();
                        response.meses.forEach(mes => {
                            // Crear una fecha temporal para formatear correctamente
                            const dateObj = new Date(mes + '-01T00:00:00');
                            const formattedMes = dateObj.toLocaleString('es', {
                                year: 'numeric',
                                month: 'long'
                            });
                            const selected = mes === currentMonth ? 'selected' : '';
                            selectMes.append(`<option value="${mes}" ${selected}>${formattedMes}</option>`);
                        });
                        cargarClientes(currentMonth); // Cargar clientes del mes actual
                    } else {
                        console.error('Error al cargar los meses:', response.message);
                    }
                }, 'json');
            }

            cargarMeses();

            $('#filtroMes').change(function() {
                const mesSeleccionado = $(this).val();
                cargarClientes(mesSeleccionado);
            });

            // Mostrar modal para agregar cliente
            $('#btnAgregarCliente').click(function() {
                $('#formCliente')[0].reset();
                $('#clienteId').val('');
                $('#clienteModalLabel').text('Agregar Cliente');
                $('#clienteModal').modal('show');
            });

            // Guardar/Editar cliente
            $('#formCliente').on('submit', function(e) {
                e.preventDefault();
                const id = $('#clienteId').val();
                const nombre = $('#nombre_cliente').val();
                const correo = $('#correo_cliente').val();
                const telefono = $('#telefono_cliente').val();
                const direccion = $('#direccion_cliente').val();

                $.post(id ? '/comercio_electronico/public/emprendedor/venta_local/clientes/editar_cliente.php' : '/comercio_electronico/public/emprendedor/venta_local/clientes/agregar_cliente.php', {
                    id,
                    nombre_cliente: nombre,
                    correo_cliente: correo,
                    telefono_cliente: telefono,
                    direccion_cliente: direccion
                }, function(response) {
                    if (response.success) {
                        Swal.fire('Éxito', 'Cliente guardado correctamente', 'success');
                        $('#clienteModal').modal('hide');
                        cargarClientes();
                    } else {
                        // Mostrar mensaje específico si hay duplicado
                        if (response.message === 'Cliente duplicado') {
                            Swal.fire('Error', 'Este cliente ya está registrado con el mismo nombre y/o teléfono.', 'warning');
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    }
                }, 'json');
            });

            // Evento para editar cliente
            $(document).on('click', '.btn-editar', function() {
                const id = $(this).data('id');
                $.get('/comercio_electronico/public/emprendedor/venta_local/clientes/editar_cliente.php', {
                    id
                }, function(response) {
                    if (response.success) {
                        const cliente = response.cliente;
                        $('#clienteId').val(cliente.id);
                        $('#nombre_cliente').val(cliente.nombre);
                        $('#correo_cliente').val(cliente.correo);
                        $('#telefono_cliente').val(cliente.telefono);
                        $('#direccion_cliente').val(cliente.direccion);
                        $('#clienteModalLabel').text('Editar Cliente');
                        $('#clienteModal').modal('show');
                    } else {
                        Swal.fire('Error', response.message, 'error');
                    }
                }, 'json');
            });

            // Eliminar cliente
            $(document).on('click', '.btn-eliminar', function() {
                const id = $(this).data('id');
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Esta acción no se puede deshacer",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.post('/comercio_electronico/public/emprendedor/venta_local/clientes/eliminar_cliente.php', {
                            id
                        }, function(response) {
                            if (response.success) {
                                Swal.fire('Eliminado', response.message, 'success');
                                cargarClientes();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        }, 'json');
                    }
                });
            });
        });
    </script>

</body>

</html>