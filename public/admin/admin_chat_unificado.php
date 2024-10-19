<?php
session_start();
include_once '../../src/config/database.php';

// Verificar si el usuario es administrador o superadmin
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Obtener emprendedores y clientes
$query_emprendedores = "SELECT id_emprendedor, nombre1, apellido1 FROM emprendedor";
$stmt_emprendedores = $db->prepare($query_emprendedores);
$stmt_emprendedores->execute();
$emprendedores = $stmt_emprendedores->fetchAll(PDO::FETCH_ASSOC);

$query_clientes = "SELECT id_cliente, nombre1, apellido1 FROM cliente";
$stmt_clientes = $db->prepare($query_clientes);
$stmt_clientes->execute();
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat con Emprendedores y Clientes</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .chat-box {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }
        .message {
            margin-bottom: 10px;
        }
        .message.admin {
            text-align: right;
            color: blue;
        }
        .message.emprendedor, .message.cliente {
            text-align: left;
            color: green;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <h3><i class="fas fa-comments"></i> Chat con Emprendedores y Clientes</h3>

        <!-- Seleccionar entre Emprendedores o Clientes -->
        <div class="mb-3">
            <label for="tipoChat" class="form-label"><i class="fas fa-users"></i> Seleccionar tipo de chat</label>
            <select id="tipoChat" class="form-select">
                <option value="emprendedores">Chat con Emprendedores</option>
                <option value="clientes">Chat con Clientes</option>
            </select>
        </div>

        <!-- Seleccionar emprendedor o cliente según el tipo de chat -->
        <div class="mb-3" id="selectorEmprendedores">
            <label for="emprendedorSelect" class="form-label"><i class="fas fa-user-tie"></i> Seleccionar Emprendedor</label>
            <select id="emprendedorSelect" class="form-select">
                <option value="">Selecciona un emprendedor</option>
                <?php foreach ($emprendedores as $emprendedor): ?>
                    <option value="<?php echo $emprendedor['id_emprendedor']; ?>">
                        <?php echo $emprendedor['nombre1'] . ' ' . $emprendedor['apellido1']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3" id="selectorClientes" style="display: none;">
            <label for="clienteSelect" class="form-label"><i class="fas fa-user"></i> Seleccionar Cliente</label>
            <select id="clienteSelect" class="form-select">
                <option value="">Selecciona un cliente</option>
                <?php foreach ($clientes as $cliente): ?>
                    <option value="<?php echo $cliente['id_cliente']; ?>">
                        <?php echo $cliente['nombre1'] . ' ' . $cliente['apellido1']; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <!-- Chat box -->
        <div class="chat-box" id="chat-box"></div>

        <!-- Formulario de chat -->
        <form id="form-chat" enctype="multipart/form-data">
            <input type="hidden" name="id_usuario" id="id_usuario">
            <input type="hidden" name="enviado_por" value="admin">
            <div class="input-group mb-2">
                <input type="text" id="mensaje" name="mensaje" class="form-control" placeholder="Escribe tu mensaje..." disabled>
                <button type="submit" class="btn btn-primary" disabled><i class="fas fa-paper-plane"></i> Enviar</button>
            </div>
            <div class="input-group">
                <input type="file" id="imagen" name="imagen" class="form-control" disabled>
            </div>
        </form>
    </div>

    <script>
        const chatBox = document.getElementById('chat-box');
        const formChat = document.getElementById('form-chat');
        const mensajeInput = document.getElementById('mensaje');
        const imagenInput = document.getElementById('imagen');
        const submitButton = formChat.querySelector('button');
        const tipoChat = document.getElementById('tipoChat');
        const emprendedorSelect = document.getElementById('emprendedorSelect');
        const clienteSelect = document.getElementById('clienteSelect');
        const idUsuarioInput = document.getElementById('id_usuario');
        const selectorEmprendedores = document.getElementById('selectorEmprendedores');
        const selectorClientes = document.getElementById('selectorClientes');

        // Alternar entre chat con emprendedores o clientes
        tipoChat.addEventListener('change', function() {
            if (this.value === 'emprendedores') {
                selectorEmprendedores.style.display = 'block';
                selectorClientes.style.display = 'none';
            } else {
                selectorEmprendedores.style.display = 'none';
                selectorClientes.style.display = 'block';
            }
            chatBox.innerHTML = ''; // Limpiar chat al cambiar tipo
            mensajeInput.disabled = true;
            imagenInput.disabled = true;
            submitButton.disabled = true;
        });

        // Cargar mensajes para emprendedores o clientes
        function cargarMensajes(idUsuario, tipo) {
            fetch(`cargar_mensajes.php?id_usuario=${idUsuario}&tipo=${tipo}&enviado_por=admin`)
                .then(response => response.json())
                .then(data => {
                    chatBox.innerHTML = ''; // Limpiar chat
                    data.forEach(mensaje => {
                        const messageDiv = document.createElement('div');
                        messageDiv.classList.add('message', mensaje.enviado_por === 'admin' ? 'admin' : (tipo === 'emprendedor' ? 'emprendedor' : 'cliente'));
                        let contenido = `<strong>${mensaje.enviado_por === 'admin' ? 'Tú' : (tipo === 'emprendedor' ? 'Emprendedor' : 'Cliente')}:</strong> ${mensaje.mensaje}`;

                        if (mensaje.imagen) {
                            contenido += `<br><img src="../../uploads/chat_imagenes/${mensaje.imagen}" alt="Imagen" style="max-width: 200px;">`;
                        }

                        messageDiv.innerHTML = contenido;
                        chatBox.appendChild(messageDiv);
                    });

                    chatBox.scrollTop = chatBox.scrollHeight; // Auto-scroll
                })
                .catch(error => {
                    console.error('Error al cargar los mensajes:', error);
                });
        }

        // Manejar selección de emprendedor o cliente
        emprendedorSelect.addEventListener('change', function() {
            const idEmprendedor = this.value;
            idUsuarioInput.value = idEmprendedor;

            if (idEmprendedor) {
                mensajeInput.disabled = false;
                imagenInput.disabled = false;
                submitButton.disabled = false;
                cargarMensajes(idEmprendedor, 'emprendedor');
            } else {
                mensajeInput.disabled = true;
                imagenInput.disabled = true;
                submitButton.disabled = true;
                chatBox.innerHTML = ''; // Limpiar chat
            }
        });

        clienteSelect.addEventListener('change', function() {
            const idCliente = this.value;
            idUsuarioInput.value = idCliente;

            if (idCliente) {
                mensajeInput.disabled = false;
                imagenInput.disabled = false;
                submitButton.disabled = false;
                cargarMensajes(idCliente, 'cliente');
            } else {
                mensajeInput.disabled = true;
                imagenInput.disabled = true;
                submitButton.disabled = true;
                chatBox.innerHTML = ''; // Limpiar chat
            }
        });

        // Enviar mensaje
        formChat.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(formChat);
            fetch('enviar_mensaje.php', {
                method: 'POST',
                body: formData
            }).then(() => {
                cargarMensajes(idUsuarioInput.value, tipoChat.value === 'emprendedores' ? 'emprendedor' : 'cliente');
                mensajeInput.value = ''; // Limpiar mensaje
                imagenInput.value = ''; // Limpiar imagen
            }).catch(error => {
                console.error('Error al enviar el mensaje:', error);
            });
        });

        // Auto-recargar mensajes cada 2 segundos
        setInterval(() => {
            if (idUsuarioInput.value) {
                cargarMensajes(idUsuarioInput.value, tipoChat.value === 'emprendedores' ? 'emprendedor' : 'cliente');
            }
        }, 2000);
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
