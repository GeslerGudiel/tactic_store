<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Ventas Locales</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .carrito-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 1000;
        }
    </style>
</head>

<body>
    <div class="container my-4">
        <h2 class="text-center">Productos Disponibles para Venta Local</h2>

        <!-- Campo de búsqueda de cliente -->
        <div class="mb-3">
            <label for="buscarCliente" class="form-label">Seleccionar Cliente:</label>
            <input type="text" id="buscarCliente" class="form-control" placeholder="Buscar cliente...">
            <ul class="list-group mt-2" id="listaClientes" style="display: none;"></ul>
        </div>

        <button class="btn btn-primary mb-4" id="agregarNuevoCliente">Agregar Nuevo Cliente</button>

        <div class="mb-3">
            <label for="buscarProducto" class="form-label">Buscar Producto:</label>
            <input type="text" id="buscarProducto" class="form-control" placeholder="Escriba el nombre del producto...">
        </div>

        <div id="listaProductos" class="row">
            <!-- Aquí se cargarán los productos mediante AJAX -->
        </div>
    </div>

    <button class="btn btn-success carrito-btn" id="verCarrito">
        <i class="fas fa-shopping-cart"></i> <span id="contadorCarrito">0</span>
    </button>

    <!-- Modal para agregar nuevo cliente -->
    <div class="modal fade" id="modalNuevoCliente" tabindex="-1" aria-labelledby="modalNuevoClienteLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNuevoClienteLabel">Agregar Nuevo Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="formNuevoCliente">
                        <div class="mb-3">
                            <label for="nombre_cliente" class="form-label">Nombre:</label>
                            <input type="text" id="nombre_cliente" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="correo_cliente" class="form-label">Correo Electrónico:</label>
                            <input type="email" id="correo_cliente" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label for="telefono_cliente" class="form-label">Teléfono:</label>
                            <input type="text" id="telefono_cliente" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label for="direccion_cliente" class="form-label">Dirección:</label>
                            <input type="text" id="direccion_cliente" class="form-control">
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar Cliente</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            let carrito = [];
            let clienteSeleccionado = null;

            // Función para buscar clientes
            $('#buscarCliente').on('input', function() {
                const filtro = $(this).val();
                if (filtro) {
                    $.get('/comercio_electronico/public/emprendedor/venta_local/buscar_cliente.php', {
                        filtro
                    }, function(data) {
                        $('#listaClientes').html(data).show();
                    });
                } else {
                    $('#listaClientes').hide();
                }
            });

            // Mostrar modal para agregar cliente nuevo
            $('#agregarNuevoCliente').on('click', function() {
                $('#modalNuevoCliente').modal('show');
            });

            // Guardar cliente nuevo
            $('#formNuevoCliente').on('submit', function(e) {
                e.preventDefault();
                const nombre_cliente = $('#nombre_cliente').val().trim();
                const correo_cliente = $('#correo_cliente').val().trim();
                const telefono_cliente = $('#telefono_cliente').val().trim();
                const direccion_cliente = $('#direccion_cliente').val().trim();

                $.post('/comercio_electronico/public/emprendedor/venta_local/agregar_cliente.php', {
                    nombre_cliente,
                    correo_cliente,
                    telefono_cliente,
                    direccion_cliente
                }, function(response) {
                    if (response.success) {
                        clienteSeleccionado = {
                            id: response.id_cliente,
                            nombre: nombre_cliente
                        };
                        $('#buscarCliente').val(nombre_cliente);
                        Swal.fire('Éxito', 'Cliente agregado correctamente.', 'success');
                        $('#modalNuevoCliente').modal('hide');
                    } else {
                        Swal.fire('Error', 'No se pudo agregar el cliente.', 'error');
                    }
                }, 'json').fail(function() {
                    Swal.fire('Error', 'Hubo un problema al agregar el cliente.', 'error');
                });
            });

            // Seleccionar cliente de la lista
            $('#listaClientes').on('click', '.cliente-item', function() {
                const idCliente = $(this).data('id');
                const nombreCliente = $(this).data('nombre');
                const correoCliente = $(this).data('correo');
                const telefonoCliente = $(this).data('telefono');

                clienteSeleccionado = {
                    id: idCliente,
                    nombre: nombreCliente,
                    correo: correoCliente,
                    telefono: telefonoCliente
                };
                $('#buscarCliente').val(nombreCliente);
                $('#listaClientes').hide();
            });

            // Función para cargar los productos de forma dinámica
            function cargarProductos(filtro = '') {
                $.get('/comercio_electronico/public/emprendedor/venta_local/mostrar_productos.php', { filtro: filtro }, function(data) {
                    $('#listaProductos').html(data);
                }).fail(function() {
                    Swal.fire('Error', 'No se pudo cargar los productos', 'error');
                });
            }

            // Llamada inicial para cargar productos al abrir la página
            cargarProductos();

            // Evento para buscar productos en tiempo real
            $('#buscarProducto').on('input', function() {
                const filtro = $(this).val();
                cargarProductos(filtro);
            });
            
            // Evento para agregar producto al carrito
            $('#listaProductos').on('click', '.btn-agregar-carrito', function() {
                const id = $(this).data('id');
                const nombre = $(this).data('nombre');
                const precio = parseFloat($(this).data('precio')) || 0;
                const stock = parseInt($(this).data('stock')) || 0;
                const cantidad = 1; // Cantidad inicial agregada

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
                    carrito.push({
                        id,
                        nombre,
                        precio,
                        cantidad,
                        stock
                    });
                }

                actualizarCarrito();
                Swal.fire('Éxito', 'Producto agregado al carrito.', 'success');
            });

            // Actualizar el contenido del carrito
            function actualizarCarrito() {
                const totalCantidad = carrito.reduce((total, producto) => total + producto.cantidad, 0);
                $('#contadorCarrito').text(totalCantidad);
            }

            function registrarVenta() {
                // Verificar si el carrito tiene productos
                if (carrito.length === 0) {
                    Swal.fire('Error', 'El carrito está vacío.', 'error');
                    return;
                }

                if (!clienteSeleccionado) {
                    Swal.fire('Error', 'Debe seleccionar o agregar un cliente para registrar la venta.', 'error');
                    return;
                }

                // Enviar los datos del carrito al servidor
                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/venta_local/procesar_venta.php',
                    method: 'POST',
                    data: {
                        id_cliente_emprendedor: clienteSeleccionado.id,
                        carrito: JSON.stringify(carrito)
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('Éxito', 'La venta se registró correctamente.', 'success').then(() => {
                                carrito = []; // Limpiar el carrito
                                clienteSeleccionado = null; // Limpiar la selección del cliente
                                actualizarCarrito(); // Actualizar el contador de carrito
                                //$('#verCarrito').click(); // Cerrar el modal
                                cargarProductos(); // Actualizar la vista de productos para reflejar el nuevo stock
                            });
                        } else {
                            Swal.fire('Error', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Hubo un problema al registrar la venta.', 'error');
                    }
                });
            }

            // Mostrar el contenido del carrito en un modal con opciones para actualizar y eliminar productos
            $('#verCarrito').click(function() {
                if (carrito.length === 0) {
                    Swal.fire('Carrito vacío', 'No has agregado ningún producto.', 'info');
                    return;
                }

                let contenidoCarrito = '<h3>Carrito de Compras</h3><ul class="list-group">';
                let total = 0;

                // Verificar si hay un cliente seleccionado
                if (clienteSeleccionado) {
                    contenidoCarrito += `
                        <div class="mb-3">
                            <strong>Cliente Seleccionado:</strong><br>
                            Nombre: ${clienteSeleccionado.nombre} <br>
                            Correo: ${clienteSeleccionado.correo} <br>
                            Teléfono: ${clienteSeleccionado.telefono}
                        </div>`;
                } else {
                    contenidoCarrito += `
                        <div class="mb-3 text-danger">
                            <strong>No se ha seleccionado un cliente. Seleccione o agregue uno antes de registrar la venta.</strong>
                        </div>`;
                    }

                carrito.forEach((producto, index) => {
                    const subtotal = producto.cantidad * producto.precio;
                    total += subtotal;
                    contenidoCarrito += `
                <li class="list-group-item d-flex justify-content-between align-items-center">
                    <div>
                        <strong>${producto.nombre}</strong> - Q${producto.precio.toFixed(2)}
                        <br>
                        Cantidad: 
                        <input type="number" class="form-control d-inline-block w-auto cantidad-carrito" data-index="${index}" min="1" max="${producto.stock}" value="${producto.cantidad}">
                    </div>
                    <div>
                        <strong>Subtotal: Q${subtotal.toFixed(2)}</strong>
                        <button class="btn btn-danger btn-sm btn-eliminar-carrito" data-index="${index}">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </li>`;
                });

                contenidoCarrito += '</ul><div class="text-end mt-3"><strong>Total: Q' + total.toFixed(2) + '</strong></div>';

                contenidoCarrito += `
                    <div class="text-end mt-3">
                        <button id="registrarVenta" class="btn btn-success">Registrar Venta</button>
                    </div>`;

                Swal.fire({
                    title: 'Contenido del Carrito',
                    html: contenidoCarrito,
                    showCloseButton: true,
                    focusConfirm: false,
                    confirmButtonText: 'Cerrar',
                    didOpen: () => {
                        // Evento para actualizar cantidad en el carrito
                        $('.cantidad-carrito').on('input', function() {
                            const index = $(this).data('index');
                            const nuevaCantidad = parseInt($(this).val());
                            const producto = carrito[index];
                            if (nuevaCantidad > 0 && nuevaCantidad <= producto.stock) {
                                producto.cantidad = nuevaCantidad;
                                actualizarCarrito();
                                $('#verCarrito').click(); // Refrescar el modal para actualizar el total
                            } else {
                                Swal.fire('Error', 'Cantidad no válida.', 'error');
                            }
                        });

                        // Evento para eliminar producto del carrito
                        $('.btn-eliminar-carrito').on('click', function() {
                            const index = $(this).data('index');
                            carrito.splice(index, 1); // Eliminar el producto del carrito
                            actualizarCarrito();
                            $('#verCarrito').click(); // Refrescar el modal para mostrar los cambios
                        });
                    }
                });

                $('#registrarVenta').on('click', function() {
                    registrarVenta();
                });
            });
        });
    </script>

</body>

</html>