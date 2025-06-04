document.addEventListener('DOMContentLoaded', () => {
    const listaUsuariosDiv = document.getElementById('conversaciones-list');
    const formulario = document.getElementById('formularioChatAdmin');
    const inputMensaje = document.getElementById('mensaje');
    const chatBox = document.getElementById('chat-box');
    const usuarioIdInput = document.getElementById('usuarioId');

    function cargarListaUsuarios() {
        fetch('/proyecto/app/controladores/controladorChatAdmin.php?listar_usuarios=1')
        .then(res => res.json())
        .then(data => {
            listaUsuariosDiv.innerHTML = '';

            if (data.length === 0) {
                listaUsuariosDiv.textContent = 'No hay conversaciones.';
                return;
            }

            data.forEach(usuario => {
                const div = document.createElement('div');
                div.textContent = usuario.nombre || `Usuario #${usuario.id}`;
                div.style.padding = '8px';
                div.style.borderBottom = '1px solid #ccc';
                div.style.cursor = 'pointer';

                div.addEventListener('click', () => {
                    window.location.href = `?view=chat_admin&usuario_id=${usuario.id}`;
                });

                listaUsuariosDiv.appendChild(div);
            });
        })
        .catch(e => {
            console.error('Error cargando lista de usuarios:', e);
            listaUsuariosDiv.textContent = 'Error al cargar usuarios.';
        });
    }

    function cargarMensajes() {
        if (!usuarioIdInput) return;
        const usuarioId = usuarioIdInput.value;

        fetch(`/proyecto/app/controladores/controladorChatAdmin.php?usuario_id=${usuarioId}`)
        .then(res => res.json())
        .then(data => {
            chatBox.innerHTML = '';

            if (!data || data.length === 0) {
                chatBox.innerHTML = '<p>No hay mensajes aún.</p>';
                return;
            }

            data.forEach(mensaje => {
                const div = document.createElement('div');
                div.classList.add('mensaje-chat');
            
                if (mensaje.emisor === 'Admin') {
                    div.classList.add('admin');
                } else {
                    div.classList.add('usuario');
                }
            
                div.innerHTML = `
                    <p>${mensaje.mensaje}</p>
                    <small>${new Date(mensaje.fecha).toLocaleString()}</small>
                `;
            
                chatBox.appendChild(div);
            });
            
            chatBox.scrollTop = chatBox.scrollHeight;
        })
        .catch(e => {
            console.error('Error cargando mensajes:', e);
            chatBox.textContent = 'Error al cargar mensajes.';
        });
    }

    if (formulario) {
        formulario.addEventListener('submit', e => {
            e.preventDefault();

            const mensaje = inputMensaje.value.trim();
            const usuarioId = usuarioIdInput.value;

            if (mensaje === '') return;

            fetch('/proyecto/app/controladores/controladorChatAdmin.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `mensaje=${encodeURIComponent(mensaje)}&usuario_id=${usuarioId}`
            })
            .then(res => {
                if (!res.ok) throw new Error('Error al enviar el mensaje');
                return res.text();
            })
            .then(() => {
                inputMensaje.value = '';
                cargarMensajes();
            })
            .catch(err => {
                alert(err.message);
            });
        });
    }

    if (listaUsuariosDiv) cargarListaUsuarios();
    if (usuarioIdInput) cargarMensajes();
    
    if (usuarioIdInput) {
        setInterval(cargarMensajes, 5000);
    }
});
