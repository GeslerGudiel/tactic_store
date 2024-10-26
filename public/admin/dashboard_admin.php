<?php
session_start();

// Verificar si el usuario tiene rol de administrador
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: login_admin.php");
    exit;
}

//Mensaje de bienvenida con SweetAlert
if (isset($_SESSION['message'])) { 
    if (is_string($_SESSION['message'])) {
        echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Atención',
            text: '" . $_SESSION['message'] . "',
            confirmButtonText: 'Aceptar'
        });
        </script>";
    } elseif (is_array($_SESSION['message'])) {
        $message_text = implode(', ', $_SESSION['message']);
        echo "<script>
        Swal.fire({
            icon: 'success',
            title: 'Atención',
            text: '" . $message_text . "',
            confirmButtonText: 'Aceptar'
        });
        </script>";
    }
    unset($_SESSION['message']);
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador</title>
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
            /* Cambia el color del texto a blanco */
            padding-left: 15px;
            padding-right: 15px;
        }

        #toggle-sidebar {
            background-color: #1abc9c;
            border: none;
            color: white;
            /*ícono del botón blanco */
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
            /* Contenido alineado al principio */
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
            /* Cuando se colapsa, los íconos deben estar centrados */
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
            <!-- Botón para ocultar/mostrar la barra lateral -->
            <button id="toggle-sidebar" class="me-2">
                <i class="fas fa-bars"></i>
            </button>
            <span>Admin</span>
        </h4>




        <a href="#" class="nav-link" id="perfil-link">
            <i class="fas fa-user"></i> <span>Mi perfil</span>
        </a>

        <!-- Menú desplegable de Emprendedores -->
        <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#emprendedores-submenu" aria-expanded="false" aria-controls="emprendedores-submenu">
            <i class="fas fa-users"></i> <span>Emprendedores</span> <i class="fas fa-caret-down float-end"></i>
        </a>
        <div class="collapse" id="emprendedores-submenu">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a href="#" class="nav-link" id="revisar-emprendedores-link">
                        <i class="fas fa-user-check"></i> <span>Revisar emprendedores</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" id="gestion-emprendedores-link">
                        <i class="fas fa-user-cog"></i> <span>Gestión de emprendedores</span>
                    </a>
                </li>
                <li class="nav_item">
                    <a href="#" class="nav-link" id="negocios-link">
                        <i class="fas fa-store"></i> <span>Ver Negocios</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Menú desplegable de Productos -->
        <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#productos-submenu" aria-expanded="false" aria-controls="productos-submenu">
            <i class="fas fa-box-open"></i> <span>Productos</span> <i class="fas fa-caret-down float-end"></i>
        </a>
        <div class="collapse" id="productos-submenu">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a href="#" class="nav-link" id="categorias-link">
                        <i class="fa-solid fa-list"></i> <span>Gestionar Categorías</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" id="productos-link">
                        <i class="fa-solid fa-gifts"></i> <span>Gestionar Productos</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" id="comentarios-link">
                        <i class="fa-solid fa-comment-dots"></i> <span>Gestionar Comentarios</span>
                    </a>
                </li>
            </ul>
        </div>

        <a href="#" class="nav-link" id="notificaciones-link">
            <i class="fas fa-bell"></i> <span>Generar notificaciones</span>
        </a>
        <a href="#" class="nav-link" id="clientes-link">
            <i class="fa-solid fa-user-tag"></i> <span>Gestión de clientes</span>
        </a>
        <a href="#" class="nav-link" id="pedidos-link">
            <i class="fas fa-shopping-cart"></i> <span>Gestión de pedidos</span>
        </a>
        <a href="#" class="nav-link" id="comision-link">
            <i class="fa-solid fa-cash-register"></i> <span>Gestión de comisiones</span>
        </a>

        <!-- Menú desplegable de chat -->
        <a href="#" class="nav-link" data-bs-toggle="collapse" data-bs-target="#chat-submenu" aria-expanded="false" aria-controls="chat-submenu">
            <i class="fa-solid fa-comments"></i> <span>Chat</span> <i class="fas fa-caret-down float-end"></i>
        </a>
        <div class="collapse" id="chat-submenu">
            <ul class="nav flex-column ms-3">
                <li class="nav-item">
                    <a href="#" class="nav-link" id="chat-emprendedor-link">
                        <i class="fa-solid fa-comment"></i> <span>Chat Emprendedores</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" id="chat-client-link">
                        <i class="fa-regular fa-comment"></i> <span>Chat Clientes</span>
                    </a>
                </li>
            </ul>
        </div>

        <a href="#" class="nav-link" id="reportes-link">
            <i class="fas fa-file-alt"></i> <span>Ver reportes</span>
        </a>

        <a href="logout_admin.php" class="nav-link">
            <i class="fas fa-sign-out-alt"></i> <span>Cerrar Sesión</span>
        </a>

        <a href="#" class="nav-link" id="backup-admin-link">
            <i class="fas fa-file-alt"></i> <span>Backup</span>
        </a>
    </div>

    <!-- Contenido principal -->
    <div class="content" id="content-area">
        <h2>Bienvenido al Dashboard del Administrador</h2>
        <p>Selecciona una opción en la barra lateral.</p>
    </div>

    <!-- jQuery y Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Manejo del botón para ocultar/mostrar la barra lateral
        $('#toggle-sidebar').click(function() {
            $('.sidebar').toggleClass('hidden');
        });

        // Cierra los menús desplegables cuando seleccionas una opción
        $('.collapse').on('click', 'a', function() {
            $(this).closest('.collapse').collapse('hide');
        });

        // Manejo de las pestañas sin recargar la página
        $(document).ready(function() {
            $('#perfil-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('ver_perfil_admin.php');
            });

            $('#revisar-emprendedores-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('revisar_emprendedores.php');
            });

            $('#gestion-emprendedores-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('gestion_emprendedores.php');
            });

            $('#negocios-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('ver_negocios.php');
            });

            $('#productos-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('ver_productos_admin.php');
            });

            $('#categorias-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('gestionar_categorias.php');
            });

            $('#comentarios-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('admin_gestion_comentarios.php');
            });

            $('#notificaciones-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('enviar_notificacion.php');
            });

            $('#pedidos-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('gestion_pedidos.php');
            });

            $('#reportes-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('ver_reportes.php');
            });

            $('#clientes-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('ver_clientes.php');
            });

            $('#suscripcion-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('ver_suscripciones_admin.php');
            });

            $('#emprendedor-suscripcion-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('ver_emprendedores_suscripcion.php');
            });

            $('#comision-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('gestion_comisiones.php');
            });

            // Cargar el chat de clientes
            $('#chat-client-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('admin_chat_client.php', function() {
                    inicializarChat(); // Ejecutar después de cargar el contenido
                });
            });

            // Cargar el chat de emprendedores
            $('#chat-emprendedor-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('admin_chat.php', function() {
                    inicializarChat(); // Ejecutar después de cargar el contenido
                });
            });

            $('#backup-admin-link').click(function(e) {
                e.preventDefault();
                $('#content-area').load('backup_admin.php');
            });
        });
    </script>
</body>

</html>