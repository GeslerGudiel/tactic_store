$(document).ready(function () {
    let carrito = [];

    // Evento para agregar producto al carrito
    $('#listaProductos').on('click', '.btn-agregar-carrito', function () {
        const id = $(this).data('id');
        const nombre = $(this).data('nombre');
        const precio = parseFloat($(this).data('precio'));
        const stock = parseInt($(this).data('stock'));
        
        // Verificar si el producto ya está en el carrito
        const productoExistente = carrito.find(producto => producto.id === id);
        
        if (productoExistente) {
            // Verificar si la cantidad solicitada supera el stock
            if (productoExistente.cantidad < stock) {
                productoExistente.cantidad += 1;
            } else {
                Swal.fire('Error', 'La cantidad seleccionada supera el stock disponible.', 'error');
                return;
            }
        } else {
            // Agregar nuevo producto al carrito con cantidad inicial de 1
            carrito.push({ id, nombre, precio, cantidad: 1, stock });
        }
        
        actualizarCarrito();
        Swal.fire('Éxito', 'Producto agregado al carrito.', 'success');
    });

    // Función para actualizar el contador del carrito
    function actualizarCarrito() {
        const totalProductos = carrito.reduce((total, producto) => total + producto.cantidad, 0);
        $('#contadorCarrito').text(totalProductos);
    }

    // Mostrar el contenido del carrito en el modal
    $('#verCarrito').click(function () {
        let contenidoCarrito = '<h3>Carrito de Compras</h3><ul class="list-group">';
        carrito.forEach(producto => {
            contenidoCarrito += `
                <li class="list-group-item">
                    ${producto.nombre} - Cantidad: ${producto.cantidad} - Subtotal: Q${(producto.cantidad * producto.precio).toFixed(2)}
                </li>`;
        });
        contenidoCarrito += '</ul>';
        
        Swal.fire({
            title: 'Contenido del Carrito',
            html: contenidoCarrito,
            confirmButtonText: 'Cerrar'
        });
    });
});
