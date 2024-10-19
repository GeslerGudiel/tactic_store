function inicializarChat(tipoUsuario) {
    const chatBox = document.getElementById('chat-box');
    const formChat = document.getElementById('form-chat');
    const mensajeInput = document.getElementById('mensaje');
    const imagenInput = document.getElementById('imagen');
    const submitButton = formChat.querySelector('button');

    let selectUsuario, idUsuarioInput, cargarMensajesURL, enviarMensajeURL;

    if (tipoUsuario === 'cliente') {
        selectUsuario = document.getElementById('clienteSelect');
        idUsuarioInput = document.getElementById('id_cliente');
        cargarMensajesURL = 'cargar_mensajes_client.php';
        enviarMensajeURL = 'enviar_mensaje_client.php';
    } else if (tipoUsuario === 'emprendedor') {
        selectUsuario = document.getElementById('emprendedorSelect');
        idUsuarioInput = document.getElementById('id_emprendedor');
        cargarMensajesURL = 'cargar_mensajes.php';
        enviarMensajeURL = 'enviar_mensaje.php';
    }

    // Verificación adicional para evitar el error
    if (!selectUsuario || !idUsuarioInput) {
        console.error('Error: No se pudo encontrar el select o el input de usuario.');
        return;
    }

    formChat.removeEventListener('submit', handleSubmit);

    function handleSubmit(e) {
        e.preventDefault();

        // Verificar si se ha seleccionado un cliente o emprendedor
        if (!idUsuarioInput || !idUsuarioInput.value) {
            console.error('Error: No se ha seleccionado ningún usuario.');
            return;
        }

        const formData = new FormData(formChat);

        fetch(enviarMensajeURL, {
            method: 'POST',
            body: formData
        })
        .then(() => {
            if (tipoUsuario === 'cliente') {
                cargarMensajes(idUsuarioInput.value, true); // Forzar scroll al enviar un nuevo mensaje
            } else if (tipoUsuario === 'emprendedor') {
                cargarMensajes(idUsuarioInput.value, true); // Forzar scroll al enviar un nuevo mensaje
            }
            mensajeInput.value = ''; // Limpiar campo
            imagenInput.value = ''; // Limpiar imagen
        })
        .catch(error => {
            console.error('Error al enviar el mensaje.', error);
        });
    }

    // Función para cargar mensajes
    function cargarMensajes(idUsuario, scrollForzado = false) {
        if (!cargarMensajesURL) {
            console.error('Error: URL de carga de mensajes no definida.');
            return;
        }

        fetch(`${cargarMensajesURL}?id_${tipoUsuario}=` + idUsuario)
            .then(response => response.json())
            .then(data => {
                const alturaAnterior = chatBox.scrollHeight; // Altura del chat antes de cargar los nuevos mensajes
                chatBox.innerHTML = ''; // Limpiar chat
                data.forEach(mensaje => {
                    const messageDiv = document.createElement('div');
                    messageDiv.classList.add('message', mensaje.enviado_por === 'admin' ? 'admin' : tipoUsuario);
                    let contenido = `<strong>${mensaje.enviado_por === 'admin' ? 'Tú' : tipoUsuario.charAt(0).toUpperCase() + tipoUsuario.slice(1)}:</strong> ${mensaje.mensaje}`;
                    if (mensaje.imagen) {
                        contenido += `<br><img src="../../uploads/chat_imagenes/${mensaje.imagen}" alt="Imagen del mensaje" style="max-width: 100%; max-height: 200px;">`;
                    }
                    messageDiv.innerHTML = contenido;
                    chatBox.appendChild(messageDiv);
                });

                // Verificar si el scroll debe forzarse
                if (scrollForzado) {
                    chatBox.scrollTop = chatBox.scrollHeight; // Forzar el scroll hacia abajo si está forzado
                }
            })
            .catch(error => {
                console.error(`Error al cargar los mensajes del ${tipoUsuario}.`, error);
            });
    }

    // Monitorear el cambio de selección del usuario (cliente o emprendedor)
    if (selectUsuario) {
        selectUsuario.addEventListener('change', function () {
            const idUsuario = this.value;
            idUsuarioInput.value = idUsuario;

            if (idUsuario) {
                mensajeInput.disabled = false;
                imagenInput.disabled = false;
                submitButton.disabled = false;
                cargarMensajes(idUsuario, true); // Forzar scroll la primera vez
            } else {
                mensajeInput.disabled = true;
                imagenInput.disabled = true;
                submitButton.disabled = true;
                chatBox.innerHTML = ''; // Limpiar el chat
            }
        });
    }

    formChat.addEventListener('submit', handleSubmit);
}
