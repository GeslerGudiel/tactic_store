<?php
session_start();

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // Si la última actividad fue hace más de 30 minutos
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time(); // Se actualiza el timestamp

// Verificar si el usuario tiene rol de emprendedor
if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    header("Location: ../auth/login.php");
    exit;
}

if (isset($_SESSION['message'])) {
    echo "<script>
    Swal.fire({
        icon: 'success',
        title: 'Atención',
        text: '" . $_SESSION['message'] . "',
        confirmButtonText: 'Aceptar'
    });
    </script>";
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Emprendedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }

        .sidebar {
            width: 250px;
            background-color: #343a40;
            height: 100vh;
            padding-top: 20px;
            transition: all 0.3s ease-in-out;
            overflow-y: auto;
        }

        .sidebar.hidden {
            width: 80px;
            overflow-x: hidden;
        }

        .sidebar-title {
            display: flex;
            align-items: center;
            color: white;
            /* Color del texto blanco */
            padding-left: 15px;
            padding-right: 15px;
        }

        #toggle-sidebar {
            background-color: #1abc9c;
            border: none;
            color: white;
            /* Ícono del botón sea blanco */
            padding: 5px;
            cursor: pointer;
            border-radius: 5px;
        }

        .sidebar.hidden .sidebar-title span {
            display: none;
        }

        .sidebar a {
            text-decoration: none;
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            /* Contenido esté alineado al principio */
            transition: all 0.3s;
        }

        .sidebar a i {
            margin-right: 10px;
            font-size: 18px;
            min-width: 30px;
            text-align: center;
        }

        .sidebar.hidden a {
            justify-content: center;
            /* Íconos entrados */
        }

        .sidebar.hidden a i {
            margin-right: 0;
        }

        .sidebar a:hover {
            background-color: #495057;
        }

        .sidebar.hidden a span {
            opacity: 0;
            visibility: hidden;
            width: 0;
            transition: opacity 0.3s ease, width 0.3s ease;
        }

        .sidebar a span {
            transition: opacity 0.3s ease, width 0.3s ease;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
            overflow-y: auto;
        }

        .sidebar.hidden~.content {
            margin-left: 80px;
        }
    </style>
</head>

<body>

    <!-- Barra lateral -->
    <div class="sidebar">
        <h4 class="sidebar-title d-flex align-items-center">
            <button id="toggle-sidebar" class="me-2">
                <i class="fas fa-bars"></i>
            </button>
            <span>Emprendedor</span>
        </h4>

        <a href="#" class="nav-link" id="notificacion-link">
            <i class="fas fa-bell"></i> <span>Notificaciones</span>
            <span id="notification-badge" class="badge bg-danger" style="display: none;"></span>
        </a>

        <a href="#" class="nav-link" id="perfil-link">
            <i class="fas fa-user"></i> <span>Mi Perfil</span>
        </a>

        <a href="#" class="nav-link" id="ver-productos-link">
            <i class="fas fa-box"></i> <span>Ver Productos</span>
        </a>

        <a href="#" class="nav-link" id="ver-pedidos-link">
            <i class="fas fa-shopping-basket"></i> <span>Ver Pedidos</span>
        </a>

        <!-- Menú desplegable de Ventas Locales -->
        <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#productos-submenu" aria-expanded="false" aria-controls="productos-submenu">
            <i class="fa-solid fa-cart-arrow-down"></i> <span>Ventas Locales</span> <i class="fas fa-caret-down float-end"></i>
        </a>
        <div class="collapse" id="productos-submenu">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a href="#" class="nav-link" id="ventas-locales-link">
                        <i class="fas fa-shopping-cart"></i> <span>Registrar Venta</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" id="historial-ventas-locales-link">
                        <i class="fas fa-history"></i> <span>Historial de Ventas</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" id="analisis-ventas-locales-link">
                        <i class="fas fa-chart-bar"></i> <span>Análisis de Ventas Locales</span>
                    </a>
                </li>
            </ul>
        </div>

        <a href="#" class="nav-link" id="estado-cuenta-link">
            <i class="fas fa-wallet"></i> <span>Estado De Cuenta</span>
        </a>

        <a href="#" class="nav-link" id="comentario-link">
            <i class="fas fa-comment"></i> <span>Comentarios</span>
        </a>

        <a href="#" class="nav-link" id="inventario-link">
            <i class="fas fa-boxes"></i> <span>Inventario</span>
        </a>

        <a href="#" class="nav-link" id="reportes-link">
            <i class="fas fa-chart-line"></i> <span>Ver Reportes</span>
        </a>

        <a href="#" class="nav-link" id="analisis-ventas-link">
            <i class="fas fa-chart-line"></i> <span>Analisis de Ventas</span>
        </a>

        <a href="logout.php" class="nav-link">
            <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
        </a>
    </div>

    <!-- Contenido principal -->
    <div class="content" id="content-area">
        <h2>Bienvenido al Dashboard del Emprendedor</h2>
        <p>Selecciona una opción en la barra lateral.</p>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function activarOpcion(selector) {
            $('.sidebar a').removeClass('active'); // Remover clases activas de otros enlaces
            $(selector).addClass('active'); // Agregar clase activa al enlace seleccionado
        }
        $(document).ready(function() {
            // Función para actualizar el contador de notificaciones no leídas
            function actualizarNotificaciones() {
                $.get('/comercio_electronico/public/emprendedor/notificacion/notificaciones_no_leidas.php', function(data) {
                    const result = JSON.parse(data);
                    const noLeidas = result.no_leidas;
                    if (noLeidas > 0) {
                        $('#notification-badge').text(noLeidas).show();
                    } else {
                        $('#notification-badge').hide();
                    }
                }).fail(function() {
                    console.error('Error al cargar las notificaciones no leídas.');
                });
            }

            // Llamar a la función al cargar la página y cada minuto para mantener actualizado el contador
            actualizarNotificaciones();
            setInterval(actualizarNotificaciones, 60000); // Actualiza cada minuto

            // Cargar el contenido de notificaciones al hacer clic en el ícono
            $('#notificacion-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('/comercio_electronico/public/emprendedor/notificacion/notificaciones.php', function() {
                    activarOpcion('#notificacion-link');
                    actualizarNotificaciones(); // Actualiza después de cargar las notificaciones
                });
            });

            // Código para marcar las notificaciones como leídas sin recargar el dashboard
            $(document).on('click', '.mark-as-read', function(e) {
                e.preventDefault(); // Evitar recarga
                const id = $(this).data('id');
                const notificacionCard = $(this).closest('.notification'); // Seleccionar la tarjeta correspondiente

                $.post('/comercio_electronico/public/emprendedor/notificacion/marcar_notificacion_leida.php', {
                    id_notificacion: id
                }, function(response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        actualizarNotificaciones(); // Actualizar contador sin recargar
                        //notificacionCard.remove(); // Eliminar notificación de la vista

                        // Mostrar alerta con SweetAlert
                        /*Swal.fire({
                            icon: 'success',
                            title: 'Notificación marcada como leída',
                            showConfirmButton: false,
                            timer: 1500
                        });*/
                    } else {
                        Swal.fire('Error', 'No se pudo marcar la notificación como leída', 'error');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Hubo un problema al procesar la solicitud.', 'error');
                });
            });
        });

        // Manejo del botón para ocultar/mostrar la barra lateral
        $('#toggle-sidebar').click(function() {
            $('.sidebar').toggleClass('hidden');
        });

        // Cierra los menús desplegables cuando se selecciona una opción
        $('.collapse').on('click', 'a', function() {
            $(this).closest('.collapse').collapse('hide');
        });

        // Manejo de las pestañas sin recargar la página
        $(document).ready(function() {
            // Función para resaltar la opción activa
            function activarOpcion(id) {
                $('.sidebar a').removeClass('active');
                $(id).addClass('active');
            }

            $('#notificacion-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('/comercio_electronico/public/emprendedor/notificacion/notificaciones.php');
                activarOpcion('#notificacion-link');
            });

            $(document).on('click', '#perfil-link', function(e) {
                e.preventDefault();
                $('#content-area').empty().load('/comercio_electronico/public/emprendedor/perfil/ver_perfil.php', function() {
                    activarOpcion('#perfil-link');
                });
            });

            $(document).on('click', '#editar-perfil-link', function(e) {
                e.preventDefault();
                $('#content-area').empty().load('/comercio_electronico/public/emprendedor/perfil/editar_perfil.php', function() {
                    activarOpcion('#editar-perfil-link');
                });
            });

            $('#ver-productos-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('/comercio_electronico/public/emprendedor/producto/productos.php');
                activarOpcion('#ver-productos-link');
            });

            $('#ver-pedidos-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('/comercio_electronico/public/emprendedor/pedido/ver_pedidos.php');
                activarOpcion('#ver-pedidos-link');
            });

            $('#ventas-locales-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('/comercio_electronico/public/emprendedor/venta_local/index.php');
                activarOpcion('#ventas-locales-link');
            });

            $('#historial-ventas-locales-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('/comercio_electronico/public/emprendedor/venta_local/historial_ventas_locales.php');
                activarOpcion('#historial-ventas-locales-link');
            });

            $('#analisis-ventas-locales-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('/comercio_electronico/public/emprendedor/venta_local/analisis_venta_local/analisis_ventas_locales.php');
                activarOpcion('#analisis-ventas-locales-link');
            });

            $('#estado-cuenta-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('/comercio_electronico/public/emprendedor/cuenta/estado_cuenta.php');
                activarOpcion('#estado-cuenta-link');
            });

            $('#comentario-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('/comercio_electronico/public/emprendedor/comentario/ver_comentario.php');
                activarOpcion('#comentario-link');
            });

            $('#inventario-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('ver_inventario.php');
                activarOpcion('#inventario-link');
            });

            $('#reportes-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('/comercio_electronico/public/emprendedor/analisis/analisis.php');
                activarOpcion('#reportes-link');
            });

            $('#analisis-ventas-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('/comercio_electronico/public/emprendedor/analisis/analisis_ventas.php');
                activarOpcion('#analisis-ventas-link');
            });
        });
    </script>
</body>

</html>