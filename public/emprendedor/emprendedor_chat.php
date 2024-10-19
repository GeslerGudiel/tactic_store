<?php
session_start();
include_once '../../src/config/database.php';

// Verificar si el usuario es un emprendedor
if (!isset($_SESSION['id_emprendedor'])) {
    header("Location: ../auth/login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Obtener el ID del emprendedor desde la sesión
$id_emprendedor = $_SESSION['id_emprendedor'];

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat con Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

        .message.emprendedor {
            text-align: left;
            color: green;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h3>Chat con Administrador</h3>

        <!-- Chat box -->
        <div class="chat-box" id="chat-box"></div>

        <div id="notificacion-nuevos-mensajes" style="display: none; color: red;">
            Tienes mensajes sin leer.
        </div>

        <!-- Formulario de chat -->
        <form id="form-chat" enctype="multipart/form-data">
            <input type="hidden" name="id_emprendedor" value="<?php echo $id_emprendedor; ?>">
            <input type="hidden" name="enviado_por" value="emprendedor">
            <div class="input-group mb-3">
                <input type="text" id="mensaje" name="mensaje" class="form-control" placeholder="Escribe tu mensaje...">
            </div>
            <div class="input-group mb-3">
                <input type="file" id="imagen" name="imagen" class="form-control">
            </div>
            <div class="input-group">
                <button type="submit" class="btn btn-primary">Enviar</button>
            </div>
        </form>
    </div>

    <script>
        const chatBox = document.getElementById('chat-box');
        const formChat = document.getElementById('form-chat');
        const mensajeInput = document.getElementById('mensaje');

        // Cargar mensajes entre el emprendedor y el administrador
        function cargarMensajes() {
            fetch('cargar_mensajes.php?id_emprendedor=<?php echo $id_emprendedor; ?>&enviado_por=emprendedor')
                .then(response => response.json())
                .then(data => {
                    chatBox.innerHTML = ''; // Limpiar chat
                    let hayNoLeidos = false;
                    data.forEach(mensaje => {
                        const messageDiv = document.createElement('div');
                        messageDiv.classList.add('message', mensaje.enviado_por === 'admin' ? 'admin' : 'emprendedor');
                        messageDiv.innerHTML = `<strong>${mensaje.enviado_por === 'admin' ? 'Administrador' : 'Tú'}:</strong> ${mensaje.mensaje}`;
                        //chatBox.appendChild(messageDiv);

                        // Verificar si hay una imagen
                        if (mensaje.imagen_url) {
                            const img = document.createElement('img');
                            img.src = mensaje.imagen_url;
                            img.style.maxWidth = '200px';
                            img.style.display = 'block';
                            messageDiv.appendChild(img);
                        }

                        chatBox.appendChild(messageDiv);

                        // Comprobar si hay mensajes no leídos
                        if (mensaje.leido == 0 && mensaje.enviado_por === 'admin') {
                            hayNoLeidos = true;
                        }
                    });

                    chatBox.scrollTop = chatBox.scrollHeight; // Auto-scroll

                    // Marcar los mensajes como leídos cuando se abren
                    if (hayNoLeidos) {
                        fetch('marcar_leido.php?id_emprendedor=<?php echo $id_emprendedor; ?>', {
                            method: 'POST'
                        }).then(() => {
                            // Actualización opcional: desactivar notificación o marcar como leídos visualmente
                            document.getElementById('notificacion-nuevos-mensajes').style.display = 'none';
                        });
                    }
                })
                .catch(error => {
                    console.error('Error al cargar los mensajes:', error);
                });
        }

        // Enviar un mensaje
        formChat.addEventListener('submit', (e) => {
            e.preventDefault();
            const formData = new FormData(formChat);
            fetch('enviar_mensaje.php', {
                method: 'POST',
                body: formData
            }).then(() => {
                cargarMensajes();
                mensajeInput.value = ''; // Limpiar campo
            }).catch(error => {
                console.error('Error al enviar el mensaje:', error);
            });
        });

        // Recargar mensajes automáticamente cada 2 segundos
        setInterval(() => {
            cargarMensajes();
        }, 2000); // 2 segundos
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>