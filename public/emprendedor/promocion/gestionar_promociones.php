<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<div class="container mt-5">
    <h2>Gestión de Promociones</h2>

    <!-- Filtros por rango de fechas -->
    <div class="row mb-4">
        <div class="col-md-3">
            <label for="fecha_inicio_filtro" class="form-label">Fecha Inicio</label>
            <input type="date" id="fecha_inicio_filtro" class="form-control">
        </div>
        <div class="col-md-3">
            <label for="fecha_fin_filtro" class="form-label">Fecha Fin</label>
            <input type="date" id="fecha_fin_filtro" class="form-control">
        </div>
    </div>

    <!-- Botón para crear promoción -->
    <button class="btn btn-success mb-3" id="btn-crear">Crear Promoción</button>

    <!-- Tabla para listar promociones -->
    <table class="table">
        <thead>
            <tr>
                <th><input type="checkbox" id="select-all"></th> <!-- Checkbox para seleccionar todas -->
                <th>Producto</th>
                <th>Tipo</th>
                <th>Descuento</th>
                <th>Precio Oferta</th>
                <th>Fecha Inicio</th>
                <th>Fecha Fin</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody id="lista-promociones"></tbody>
    </table>

    <button class="btn btn-danger mt-3" id="btn-eliminar-seleccionadas">Eliminar Seleccionadas</button>

</div>

<!-- Modal para Crear/Editar Promoción -->
<div class="modal fade" id="modalPromocion" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear/Editar Promoción</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-promocion">
                    <input type="hidden" id="id_promocion" name="id_promocion">
                    <input type="hidden" id="precio_promocional" name="precio_promocional">

                    <div class="mb-3">
                        <label for="tipo_promocion" class="form-label">Tipo de Promoción</label>
                        <select class="form-control" id="tipo_promocion" name="tipo_promocion" required>
                            <option value="Descuento">Descuento (%)</option>
                            <option value="Oferta">Oferta (Precio fijo)</option>
                            <option value="2x1">2x1</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="productos" class="form-label">Seleccionar Producto</label>
                        <select class="form-control" id="productos" name="id_producto" required></select>
                    </div>

                    <div class="mb-3">
                        <label for="precio_original" class="form-label">Precio Original</label>
                        <input type="number" class="form-control" id="precio_original" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="porcentaje_descuento" class="form-label">Porcentaje de Descuento</label>
                        <input type="number" class="form-control" id="porcentaje_descuento" name="porcentaje_descuento" min="0" max="100">
                    </div>

                    <div class="mb-3">
                        <label for="precio_descuento" class="form-label">Precio con Descuento</label>
                        <input type="number" class="form-control" id="precio_descuento" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="fecha_inicio" class="form-label">Fecha de Inicio</label>
                        <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" required>
                    </div>

                    <div class="mb-3">
                        <label for="fecha_fin" class="form-label">Fecha de Fin</label>
                        <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Guardar</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function() {
        const fechaActual = new Date();
        const primerDia = new Date(fechaActual.getFullYear(), fechaActual.getMonth(), 1).toISOString().split('T')[0];
        const ultimoDia = new Date(fechaActual.getFullYear(), fechaActual.getMonth() + 1, 0).toISOString().split('T')[0];

        // Asignar las fechas al filtro
        $('#fecha_inicio_filtro').val(primerDia);
        $('#fecha_fin_filtro').val(ultimoDia);

        $('#fecha_inicio_filtro, #fecha_fin_filtro').on('change', function() {
            const fechaInicio = $('#fecha_inicio_filtro').val();
            const fechaFin = $('#fecha_fin_filtro').val();
            cargarPromociones(fechaInicio, fechaFin); // Llamar con las nuevas fechas
        });

        // Función para eliminar backdrop adicional al abrir el modal
        function limpiarBackdrops() {
            $('.modal-backdrop').remove(); // Eliminar todas las capas de backdrop
            $('body').removeClass('modal-open'); // Asegurar que no quede bloqueado el scroll
            $('body').css('padding-right', '0'); // Corregir cualquier padding sobrante
        }

        // Ejecutar la revisión de promociones cada hora (3600000 ms = 1 hora)
        setInterval(revisarPromociones, 3600000);

        // Llamada inicial para revisar promociones al cargar la página
        revisarPromociones();
        cargarPromociones(primerDia, ultimoDia); // Llamada inicial para cargar promociones
        cargarProductos();

        // Función para revisar y desactivar promociones expiradas
        function revisarPromociones() {
            $.ajax({
                url: '/comercio_electronico/public/emprendedor/promocion/revisar_promociones.php',
                method: 'GET',
                success: function(response) {
                    let data = JSON.parse(response);

                    if (data.success) {
                        console.log(data.message);
                        Swal.fire('Revisión Completa', data.message, 'success');
                        cargarPromociones(); // Actualizar la lista de promociones
                    } else {
                        console.log(data.message); // Para depuración
                    }
                },
                error: function() {
                    console.error('Error al revisar promociones expiradas.');
                }
            });
        }

        // Mostrar modal para crear promoción
        $('#btn-crear').click(function() {
            $('#form-promocion')[0].reset();
            $('#id_promocion').val('');
            $('#precio_original').val('');
            $('#precio_descuento').val('');
            const modal = new bootstrap.Modal(document.getElementById('modalPromocion'));
            modal.show();
        });

        // Cargar productos en el select
        function cargarProductos() {
            $.ajax({
                url: '/comercio_electronico/public/emprendedor/promocion/obtener_productos.php',
                method: 'GET',
                success: function(response) {
                    let data = JSON.parse(response);
                    $('#productos').empty();

                    if (data.success) {
                        data.productos.forEach(producto => {
                            $('#productos').append(`
                                <option value="${producto.id_producto}" data-precio="${producto.precio}">
                                    ${producto.nombre_producto}
                                </option>
                            `);
                        });
                    } else {
                        Swal.fire('Advertencia', data.message, 'warning');
                    }
                }
            });
        }

        // Mostrar el precio original al seleccionar un producto
        $('#productos').on('change', function() {
            let precio = $(this).find('option:selected').data('precio');
            console.log('Precio Original:', precio); // Para depuración
            $('#precio_original').val(precio);
            calcularPrecioConDescuento();
        });

        // Calcular el precio con descuento al ingresar el porcentaje
        $('#porcentaje_descuento').on('input', function() {
            calcularPrecioConDescuento();
        });

        // Función para calcular el precio con descuento
        function calcularPrecioConDescuento() {
            let precioOriginal = parseFloat($('#precio_original').val());
            let descuento = parseFloat($('#porcentaje_descuento').val()) || 0;

            if (!isNaN(precioOriginal)) {
                let precioFinal = precioOriginal - (precioOriginal * (descuento / 100));
                $('#precio_descuento').val(precioFinal.toFixed(2));
                $('#precio_promocional').val(precioFinal.toFixed(2)); // Enviar con el formulario
            }
        }

        // Guardar promoción
        $('#form-promocion').submit(function(e) {
            e.preventDefault();

            let id_promocion = $('#id_promocion').val();
            let url = id_promocion ?
                '/comercio_electronico/public/emprendedor/promocion/editar_promocion.php' :
                '/comercio_electronico/public/emprendedor/promocion/guardar_promocion.php';

            $.ajax({
                url: url,
                method: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    let data = JSON.parse(response);

                    if (data.success) {
                        Swal.fire('¡Guardado!', data.message, 'success');
                        cargarPromociones();
                        const modal = bootstrap.Modal.getInstance(document.getElementById('modalPromocion'));
                        modal.hide();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                }
            });
        });

        $(document).on('click', '.btn-editar', function() {
            limpiarBackdrops(); // Eliminar cualquier rastro previo
            let id_promocion = $(this).data('id');

            $.ajax({
                url: '/comercio_electronico/public/emprendedor/promocion/obtener_promocion.php',
                method: 'GET',
                data: {
                    id_promocion: id_promocion
                },
                success: function(response) {
                    let data = JSON.parse(response);

                    if (data.success) {
                        let promocion = data.promocion;

                        // Rellenar el formulario con los datos de la promoción
                        $('#id_promocion').val(promocion.id_promocion);
                        $('#productos').val(promocion.id_producto);
                        $('#tipo_promocion').val(promocion.tipo_promocion);
                        $('#porcentaje_descuento').val(promocion.porcentaje_descuento);
                        $('#precio_descuento').val(promocion.precio_oferta);
                        $('#fecha_inicio').val(promocion.fecha_inicio);
                        $('#fecha_fin').val(promocion.fecha_fin);

                        // Mostrar el modal de edición
                        const modal = new bootstrap.Modal(document.getElementById('modalPromocion'));
                        modal.show();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo obtener los datos de la promoción.', 'error');
                }
            });
        });

        // Seleccionar o deseleccionar todas las promociones
        $('#select-all').on('change', function() {
            $('input[name="promocion-checkbox"]').prop('checked', this.checked);
        });

        // Eliminar promociones seleccionadas
        $('#btn-eliminar-seleccionadas').on('click', function() {
            let ids = [];
            $('input[name="promocion-checkbox"]:checked').each(function() {
                ids.push($(this).val());
            });

            if (ids.length === 0) {
                Swal.fire('Advertencia', 'No se seleccionaron promociones.', 'warning');
                return;
            }

            Swal.fire({
                title: '¿Estás seguro?',
                text: 'Esta acción eliminará las promociones seleccionadas.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/comercio_electronico/public/emprendedor/promocion/eliminar_promocion.php',
                        method: 'POST',
                        data: {
                            ids_promociones: ids
                        },
                        success: function(response) {
                            let data = JSON.parse(response);

                            if (data.success) {
                                Swal.fire('Eliminadas', data.message, 'success');
                                cargarPromociones();
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        }
                    });
                }
            });
        });

        // Evento para eliminar una promoción individual
        $(document).on('click', '.btn-eliminar', function() {
            let id_promocion = $(this).data('id'); // Obtener el ID de la promoción

            Swal.fire({
                title: '¿Estás seguro?',
                text: 'No podrás revertir esta acción.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Llamada AJAX para eliminar la promoción
                    $.ajax({
                        url: '/comercio_electronico/public/emprendedor/promocion/eliminar_promocion.php',
                        method: 'POST',
                        data: {
                            ids_promociones: [id_promocion]
                        }, // Enviar el ID como un array
                        success: function(response) {
                            let data = JSON.parse(response);

                            if (data.success) {
                                Swal.fire('Eliminada', data.message, 'success');
                                cargarPromociones(); // Recargar la lista de promociones
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Hubo un problema al eliminar la promoción.', 'error');
                        }
                    });
                }
            });
        });

        // Eliminar backdrop y clases sobrantes al cerrar el modal
        $('#modalPromocion').on('hidden.bs.modal', function() {
            $('.modal-backdrop').remove(); // Eliminar el fondo oscuro
            $('body').removeClass('modal-open'); // Quitar clase que bloquea scroll
            $('body').css('padding-right', ''); // Quitar cualquier padding residual
        });

        // Cargar promociones en la tabla
        function cargarPromociones(fechaInicio = primerDia, fechaFin = ultimoDia) {
            if (!fechaInicio || !fechaFin) {
                console.error('Fechas de filtro no válidas.');
                return;
            }

            $.ajax({
                url: '/comercio_electronico/public/emprendedor/promocion/listar_promociones.php',
                method: 'GET',
                data: {
                    fecha_inicio: fechaInicio,
                    fecha_fin: fechaFin
                },
                success: function(response) {
                    let data = JSON.parse(response);
                    let html = '';

                    if (data.success) {
                        data.promociones.forEach(promocion => {
                            html += `
                        <tr>
                            <td><input type="checkbox" name="promocion-checkbox" value="${promocion.id_promocion}"></td>
                            <td>${promocion.nombre_producto}</td>
                            <td>${promocion.tipo_promocion}</td>
                            <td>${promocion.porcentaje_descuento || '-'}</td>
                            <td>${promocion.precio_oferta || '-'}</td>
                            <td>${promocion.fecha_inicio}</td>
                            <td>${promocion.fecha_fin}</td>
                            <td>${promocion.estado}</td>
                            <td>
                                <button class="btn btn-warning btn-sm btn-editar" data-id="${promocion.id_promocion}">Editar</button>
                                <button class="btn btn-danger btn-sm btn-eliminar" data-id="${promocion.id_promocion}">Eliminar</button>
                            </td>
                        </tr>`;
                        });
                    } else {
                        html = '<tr><td colspan="9" class="text-center">No se encontraron promociones</td></tr>';
                    }

                    $('#lista-promociones').html(html);
                },
                error: function() {
                    console.error('Error al cargar promociones.');
                }
            });
        }

    });
</script>