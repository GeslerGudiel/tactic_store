<div class="container my-4">
    <h2 class="text-center">Productos Disponibles para Venta Local</h2>
    <div class="mb-3">
        <input type="text" id="buscarProducto" class="form-control" placeholder="Buscar producto...">
    </div>
    <button class="btn btn-success carrito-btn" id="verCarrito">
        <i class="fas fa-shopping-cart"></i> <span id="contadorCarrito">0</span>
    </button>

    <div class="mb-3">
        <label for="buscarCliente" class="form-label">Cliente:</label>
        <input type="text" id="buscarCliente" class="form-control" placeholder="Buscar cliente...">
        <ul class="list-group" id="listaClientes" style="display: none;"></ul>
        <input type="hidden" id="id_cliente" name="id_cliente_emprendedor">
    </div>

    <!-- Modal para agregar un nuevo cliente -->
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
                            <label for="correo_cliente" class="form-label">Correo:</label>
                            <input type="email" id="correo_cliente" class="form-control">
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



    <div id="listaProductos" class="row">
        <!-- Los productos se cargarán aquí dinámicamente mediante AJAX -->
    </div>
</div>
<div id="modalContainer">
    <!-- Aquí se cargarán los modales de carrito y cliente mediante AJAX -->
</div>

<!-- Cargar los scripts -->
<script src="/comercio_electronico/public/emprendedor/venta_local/js/gestionar_venta_local.js"></script>