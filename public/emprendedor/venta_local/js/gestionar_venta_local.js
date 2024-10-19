$(document).ready(function () {
    let carrito = [];
    let clienteSeleccionado = null;

    // Cargar productos al iniciar la página
    cargarProductos('');

    // Función para cargar los productos
    function cargarProductos(filtro) {
        $.get('/comercio_electronico/public/emprendedor/venta_local/php/buscar_producto.php', { filtro: filtro }, function (data) {
            $('#listaProductos').html(data);
            
             // Agregar el campo de cantidad y el botón de agregar al carrito a cada producto
        $('.product-card').each(function () {
            const cantidadInput = $('<input>', {
                type: 'number',
                class: 'form-control cantidad-producto',
                min: 1,
                value: 1,
                style: 'width: 50px; display: inline-block; margin-right: 5px;'
            });
            $(this).append(cantidadInput);

            const agregarBtn = $('<button>', {
                class: 'btn btn-primary btn-agregar-carrito',
                html: '<i class="fas fa-cart-plus"></i>',
                'data-id': $(this).data('id'),
                'data-nombre': $(this).data('nombre'),
                'data-precio': $(this).data('precio'),
                'data-stock': $(this).data('stock')
            });
            $(this).append(agregarBtn);
        });
        });
    }

    // Buscar productos
    $('#buscarProducto').on('input', function () {
        let filtro = $(this).val();
        cargarProductos(filtro);
    });

    // Agregar producto al carrito
    $('#listaProductos').on('click', '.btn-agregar-carrito', function () {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        const precio = parseFloat($(this).data('precio')) || 0;
        const cantidadInput = $(this).siblings('.cantidad-producto');
    const cantidad = parseInt(cantidadInput.val()) || 1;

        // Verificar si el producto ya está en el carrito
        const productoExistente = carrito.find(p => p.id === id);
        if (productoExistente) {
            if (productoExistente.cantidad + cantidad <= stock) {
                productoExistente.cantidad += cantidad;
            } else {
                Swal.fire('Error', 'La cantidad seleccionada supera el stock disponible.', 'error');
                return;
            }
        } else {
            carrito.push({ id, nombre, precio, cantidad, stock });
        }

        actualizarCarrito();
        Swal.fire('Éxito', 'Producto agregado al carrito.', 'success');
    });

    // Actualizar el contenido del carrito
    function actualizarCarrito() {
        let totalCantidad = 0;
        carrito.forEach(producto => {
            totalCantidad += producto.cantidad;
        });
        $('#contadorCarrito').text(totalCantidad);
    }

    // Mostrar el carrito en un modal
    function verCarrito() {
        let modalContent = `
            <div class="modal fade" id="carritoModal" tabindex="-1" aria-labelledby="carritoModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="carritoModalLabel">Carrito de Compras</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">`;

        if (clienteSeleccionado) {
            modalContent += `<p><strong>Cliente:</strong> ${clienteSeleccionado.nombre}</p>`;
        } else {
            modalContent += `<p class="text-danger">No se ha seleccionado un cliente.</p>`;
        }

        if (carrito.length === 0) {
            modalContent += `<p class="text-center">El carrito está vacío.</p>`;
        } else {
            let totalCompra = 0;
            modalContent += `
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>`;
            carrito.forEach(producto => {
                const precio = parseFloat(producto.precio) || 0;
                const subtotal = precio * producto.cantidad;
                totalCompra += subtotal;

                modalContent += `
                    <tr>
                        <td>${producto.nombre}</td>
                        <td>Q${precio.toFixed(2)}</td>
                        <td>
                            <input type="number" class="form-control form-control-sm" value="${producto.cantidad}" min="1" max="${producto.stock}" data-id="${producto.id}">
                        </td>
                        <td>Q${subtotal.toFixed(2)}</td>
                        <td>
                            <button class="btn btn-danger btn-sm btn-eliminar-carrito" data-id="${producto.id}">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>`;
            });
            modalContent += `
                    </tbody>
                </table>
                <div class="text-end">
                    <h5>Total: Q${totalCompra.toFixed(2)}</h5>
                </div>`;
        }

        modalContent += `
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>`;
        if (carrito.length > 0 && clienteSeleccionado) {
            modalContent += `<button type="button" class="btn btn-success" id="registrarVenta"><i class="fas fa-cash-register"></i> Registrar Venta</button>`;
        }
        modalContent += `
                        </div>
                    </div>
                </div>
            </div>`;

        $('#modalContainer').html(modalContent);
        $('#carritoModal').modal('show');
    }

    // Mostrar el carrito al hacer clic en el botón
    $('#verCarrito').on('click', verCarrito);

    // Evento para actualizar la cantidad en el carrito
    $('#modalContainer').on('change', 'input[type="number"]', function () {
        const id = $(this).data('id');
        const nuevaCantidad = parseInt($(this).val());
        const producto = carrito.find(p => p.id === id);
        if (producto && nuevaCantidad > 0 && nuevaCantidad <= producto.stock) {
            producto.cantidad = nuevaCantidad;
            actualizarCarrito(); // Llamada para actualizar el contador del carrito
            verCarrito(); // Actualizar el contenido del carrito
            Swal.fire({
                icon: 'success',
                title: 'Cantidad actualizada',
                text: 'La cantidad ha sido actualizada.',
                showConfirmButton: false,
                timer: 1500
            });
        } else {
            Swal.fire('Error', 'Cantidad no válida.', 'error');
        }
    });

    // Eliminar un producto del carrito
    $(document).on('click', '.btn-eliminar-carrito', function () {
        const idProducto = $(this).data('id');
        carrito = carrito.filter(producto => producto.id !== idProducto);
        actualizarCarrito();
        verCarrito();
        if (carrito.length === 0) {
            $('#carritoModal').modal('hide');
        }
        Swal.fire('Producto eliminado', 'El producto ha sido eliminado del carrito.', 'success');
    });

    // Evento para cerrar el modal del carrito y limpiar cualquier sombreado
    $(document).on('hidden.bs.modal', '#carritoModal', function () {
        $('body').removeClass('modal-open');
        $('.modal-backdrop').remove();
    });

    // Seleccionar un cliente de forma dinámica
    $('#buscarCliente').on('input', function () {
        const filtro = $(this).val();
        $.get('/comercio_electronico/public/emprendedor/venta_local/php/listar_clientes.php', { filtro: filtro }, function (data) {
            const clientes = JSON.parse(data);
            let opciones = '';
            if (clientes.length > 0) {
                clientes.forEach(cliente => {
                    opciones += `<li class="list-group-item cliente-item" data-id="${cliente.id_cliente_emprendedor}" data-nombre="${cliente.nombre_cliente}">${cliente.nombre_cliente}</li>`;
                });
            } else {
                opciones = '<li class="list-group-item">No se encontraron clientes. <a href="#" id="agregarNuevoCliente">Agregar nuevo cliente</a></li>';
            }
            $('#listaClientes').html(opciones).show();
        });
    });

    // Seleccionar un cliente de la lista
    $('#listaClientes').on('click', '.cliente-item', function () {
        const idCliente = $(this).data('id');
        const nombreCliente = $(this).data('nombre');
        clienteSeleccionado = { id: idCliente, nombre: nombreCliente };
        $('#buscarCliente').val(nombreCliente);
        $('#listaClientes').hide();
    });

    // Manejar la opción de agregar un nuevo cliente
    $('#listaClientes').on('click', '#agregarNuevoCliente', function (e) {
        e.preventDefault();
        $('#modalNuevoCliente').modal('show');
    });

    // Manejar el formulario para agregar un nuevo cliente
    $('#formNuevoCliente').submit(function (e) {
        e.preventDefault();
        const nombre_cliente = $('#nombre_cliente').val().trim();
        const correo_cliente = $('#correo_cliente').val().trim();
        const telefono_cliente = $('#telefono_cliente').val().trim();
        const direccion_cliente = $('#direccion_cliente').val().trim();

        $.ajax({
            url: '/comercio_electronico/public/emprendedor/venta_local/php/agregar_cliente.php',
            method: 'POST',
            data: {
                nombre_cliente: nombre_cliente,
                correo_cliente: correo_cliente,
                telefono_cliente: telefono_cliente
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#modalNuevoCliente').modal('hide');
                    clienteSeleccionado = { id: response.id_cliente_emprendedor, nombre: response.nombre_cliente };
                    $('#buscarCliente').val(response.nombre_cliente);
                    Swal.fire('Éxito', 'Cliente agregado correctamente.', 'success');
                } else {
                    Swal.fire('Error', response.message || 'No se pudo agregar el cliente.', 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Hubo un problema al agregar el cliente.', 'error');
            }
        });
    });

    // Registrar la venta al hacer clic en el botón de "Registrar Venta"
    $(document).on('click', '#registrarVenta', function () {
        if (carrito.length === 0) {
            Swal.fire('Error', 'El carrito está vacío.', 'error');
            return;
        }

        if (!clienteSeleccionado) {
            Swal.fire('Error', 'Debe seleccionar un cliente para la venta.', 'error');
            return;
        }

        // Enviar datos al servidor para procesar la venta
        $.ajax({
            url: '/comercio_electronico/public/emprendedor/venta_local/php/procesar_venta_local.php',
            method: 'POST',
            data: {
                id_cliente_emprendedor: clienteSeleccionado.id,
                carrito: JSON.stringify(carrito)
            },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Éxito',
                        text: response.message,
                        showConfirmButton: true
                    }).then(() => {
                        // Redirigir a la página de historial de ventas locales
                        window.location.href = '/comercio_electronico/public/emprendedor/venta_local/historial_ventas_locales.php';
                    });

                    // Limpiar el carrito después de registrar la venta
                    carrito = [];
                    clienteSeleccionado = null;
                    actualizarCarrito();
                    $('#carritoModal').modal('hide');
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function () {
                Swal.fire('Error', 'Hubo un problema al registrar la venta.', 'error');
            }
        });
    });
});
