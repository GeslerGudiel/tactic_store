<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.1.3/js/bootstrap.bundle.min.js"></script>

<div class="container mt-4">
    <h2 class="text-center">Mis Productos</h2>
    <div class="input-group mb-3">
        <input type="text" id="buscar-producto" class="form-control" placeholder="Buscar por nombre, descripción, categoría o estado...">
    </div>

    <!-- Botón para abrir el modal de agregar producto -->
    <button class="btn btn-success mb-3" id="btn-agregar-producto">
        <i class="fas fa-plus"></i> Agregar Producto
    </button>

    <div id="productos-lista">
        <!-- Aquí es donde se cargarán los productos -->
    </div>
</div>

<!-- Modal para editar producto -->
<div class="modal fade" id="modal-editar-producto" tabindex="-1" aria-labelledby="editarProductoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarProductoLabel">Editar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="form-editar-producto" enctype="multipart/form-data">
                    <input type="hidden" id="id_producto" name="id_producto">

                    <div class="mb-3">
                        <label for="nombre_producto" class="form-label">Nombre del Producto</label>
                        <input type="text" id="nombre_producto" name="nombre_producto" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="costo" class="form-label">Costo</label>
                        <input type="number" step="0.01" id="costo" name="costo" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="precio" class="form-label">Precio</label>
                        <input type="number" step="0.01" id="precio" name="precio" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" id="stock" name="stock" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <select id="categoria-editar" name="id_categoria" class="form-select" required></select>
                    </div>

                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select id="estado" name="estado" class="form-select" required>
                            <option value="disponible">Disponible</option>
                            <option value="no disponible">No disponible</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="imagen" class="form-label">Imagen del Producto</label>
                        <img id="imagen-actual" src="" alt="Imagen del Producto" width="100" class="d-block mb-2">
                        <input type="file" id="imagen" name="imagen" class="form-control" accept="image/*">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar producto -->
<div class="modal fade" id="modal-agregar-producto" tabindex="-1" aria-labelledby="agregarProductoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="agregarProductoLabel">Agregar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="form-agregar-producto" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="nombre_producto" class="form-label">Nombre del Producto</label>
                        <input type="text" id="nombre_producto" name="nombre_producto" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="costo" class="form-label">Costo</label>
                        <input type="number" step="0.01" id="costo" name="costo" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="precio" class="form-label">Precio</label>
                        <input type="number" step="0.01" id="precio" name="precio" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="stock" class="form-label">Stock</label>
                        <input type="number" id="stock" name="stock" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="categoria" class="form-label">Categoría</label>
                        <select id="categoria-agregar" name="id_categoria" class="form-select" required></select>
                    </div>
                    <div class="mb-3">
                        <label for="imagen" class="form-label">Imagen del Producto</label>
                        <input type="file" id="imagen" name="imagen" class="form-control" accept="image/*" required>
                    </div>
                    <div class="mb-3">
                        <label for="estado" class="form-label">Estado</label>
                        <select id="estado" name="estado" class="form-select" required>
                            <option value="disponible">Disponible</option>
                            <option value="no disponible">No disponible</option>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Agregar Producto</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>


<script>
    // Función para cargar productos
    function cargarProductos(termino = '') {
        $.ajax({
            url: '/comercio_electronico/public/emprendedor/producto/cargar_productos.php',
            method: 'GET',
            data: {
                buscar: termino
            },
            success: function(data) {
                $('#productos-lista').html(data);
                inicializarEventos(); // Inicializar eventos después de cargar productos
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar productos:', error);
            }
        });
    }

    // Inicializar eventos de edición y eliminación
    function inicializarEventos() {
        $('#productos-lista').on('click', '.btn-editar', function() {
            const producto = $(this).data('producto');
            abrirModalEditarProducto(producto);
        });

        $('#productos-lista').on('click', '.btn-eliminar', function() {
            const idProducto = $(this).data('id');
            eliminarProducto(idProducto);
        });
    }

    // Abrir modal de edición y cargar los datos del producto
    function abrirModalEditarProducto(producto) {
        $('#id_producto').val(producto.id_producto);
        $('#nombre_producto').val(producto.nombre_producto);
        $('#descripcion').val(producto.descripcion);
        $('#costo').val(producto.costo);
        $('#precio').val(producto.precio);
        $('#stock').val(producto.stock);
        $('#estado').val(producto.estado);
        $('#imagen-actual').attr('src', `/comercio_electronico/uploads/productos/${producto.imagen}`);

        // Cargar categorías y seleccionar la correspondiente
        cargarCategorias('#categoria-editar', producto.id_categoria);
        $('#modal-editar-producto').modal('show');
    }

    // Función para cargar categorías en el <select> correspondiente
    function cargarCategorias(selector = '#categoria-agregar', categoriaSeleccionada = null) {
        $.ajax({
            url: '/comercio_electronico/public/emprendedor/producto/obtener_categorias.php',
            method: 'GET',
            dataType: 'json',
            success: function(categorias) {
                let opciones = categorias.map(c => {
                    const selected = categoriaSeleccionada == c.id_categoria ? 'selected' : '';
                    return `<option value="${c.id_categoria}" ${selected}>${c.nombre_categoria}</option>`;
                }).join('');
                $(selector).html(opciones);
            },
            error: function(error) {
                console.error('Error al cargar categorías:', error);
                Swal.fire('Error', 'No se pudieron cargar las categorías.', 'error');
            }
        });
    }

    // Manejar el envío del formulario de edición
    $('#form-editar-producto').on('submit', function(e) {
        e.preventDefault(); // Evita la recarga de la página

        const formData = new FormData(this);

        const stock = parseInt($('#stock').val(), 10);
        const estado = $('#estado').val();

        // Validar: Si el stock es 0, forzar estado a "No disponible"
        if (stock === 0 && estado === 'disponible') {
            Swal.fire('Error', 'No puedes dejar el producto disponible con stock 0.', 'error');
            return;
        }

        fetch('/comercio_electronico/public/emprendedor/producto/editar_producto.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    Swal.fire('Producto actualizado', '', 'success');
                    $('#modal-editar-producto').modal('hide');
                    cargarProductos(); // Recargar productos
                } else {
                    Swal.fire('Error', data.message || 'No se pudo actualizar el producto', 'error');
                }
            })
            .catch(error => {
                console.error('Error al editar producto:', error);
                Swal.fire('Error', 'Ocurrió un error al guardar los cambios', 'error');
            });
    });

    // Eliminar producto
    function eliminarProducto(idProducto) {
        Swal.fire({
            title: '¿Estás seguro?',
            text: 'Este producto será eliminado.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/comercio_electronico/public/emprendedor/producto/eliminar_producto.php',
                    method: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        id_producto: idProducto
                    }),
                    success: function(data) {
                        Swal.fire(data.message, '', data.status);
                        if (data.status === 'success') {
                            cargarProductos(); // Recargar productos
                        }
                    },
                    error: function(error) {
                        console.error('Error al eliminar producto:', error);
                    }
                });
            }
        });
    }

    // Mostrar el modal de agregar producto
    $('#btn-agregar-producto').on('click', function() {
        $('#form-agregar-producto')[0].reset(); // Resetear formulario
        cargarCategorias(); // Cargar categorías disponibles
        $('#modal-agregar-producto').modal('show');
    });

    // Manejar el envío del formulario de agregar producto
    $('#form-agregar-producto').on('submit', function(e) {
        e.preventDefault(); // Evitar recarga de la página
        const formData = new FormData(this);

        $.ajax({
            url: '/comercio_electronico/public/emprendedor/producto/agregar_producto.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                try {
                    const data = JSON.parse(response);
                    Swal.fire(data.message, '', data.status);
                    if (data.status === 'success') {
                        $('#modal-agregar-producto').modal('hide');
                        cargarProductos(); // Recargar productos
                    }
                } catch (error) {
                    console.error('Error al parsear JSON:', error, response);
                    Swal.fire('Error', 'Respuesta inválida del servidor', 'error');
                }
            },
            error: function(error) {
                console.error('Error al agregar producto:', error);
                Swal.fire('Error', 'Ocurrió un error al agregar el producto', 'error');
            }
        });
    });

    // Búsqueda de manera dinámica
    $('#buscar-producto').on('input', function() {
        const termino = $(this).val();
        cargarProductos(termino);
    });

    // Validación del stock en tiempo real
    $('#stock').on('input', function() {
        const stock = parseInt($(this).val(), 10);
        const estado = $('#estado');

        if (stock === 0) {
            estado.val('no disponible');
            estado.prop('disabled', true);
        } else {
            estado.prop('disabled', false);
        }
    });

    // Cargar productos al iniciar la página
    $(document).ready(function() {
        cargarProductos(); // Cargar productos al inicio
    });
</script>