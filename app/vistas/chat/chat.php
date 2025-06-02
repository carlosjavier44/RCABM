<?php

if (!isset($_SESSION['usuario']['id'])) {
    header("Location: ../usuarios/login.php");
    exit();
}

$usuarioId = $_SESSION['usuario']['id'];
$usuarioNombre = $_SESSION['usuario']['nombre'];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Chat con el Administrador</title>
    <style>
        /* Contenedor principal centrado y con ancho fijo */
        .container {
            max-width: 800px;
            margin: 30px auto;
            padding: 0 15px;
            font-family: Arial, sans-serif;
        }

        h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        #chat-box {
            border: 1px solid #ccc;
            height: 400px;
            overflow-y: auto;
            padding: 10px;
            background: #fefefe;
            margin-bottom: 10px;
            border-radius: 5px;
            max-width: 100%;
            box-sizing: border-box;
        }

        .message {
            margin-bottom: 10px;
            max-width: 70%;
            padding: 8px 12px;
            border-radius: 15px;
            clear: both;
            word-wrap: break-word;
        }

        .my-message {
            background-color: #d1ffd6;
            float: right;
            text-align: right;
        }

        .other-message {
            background-color: #f1f1f1;
            float: left;
            text-align: left;
        }

        .message strong {
            display: block;
            margin-bottom: 5px;
        }

        .message small {
            display: block;
            font-size: 0.75em;
            color: #666;
        }

        form {
            display: flex;
            max-width: 100%; /* para que no se salga */
            box-sizing: border-box;
        }

        input[type="text"] {
            flex-grow: 1;
            padding: 10px;
            font-size: 1rem;
            border-radius: 4px 0 0 4px;
            border: 1px solid #ccc;
            outline: none;
        }

        button {
            padding: 10px 20px;
            font-size: 1rem;
            border: none;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            border-radius: 0 4px 4px 0;
        }

        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>

<body data-user-id="<?= $usuarioId ?>">

    <div class="container">
        <h2>Chat con el Administrador</h2>

        <div id="chat-box"></div>

        <form id="form-chat" autocomplete="off">
            <input type="text" id="mensaje" name="mensaje" placeholder="Escribe un mensaje..." required />
            <button type="submit">Enviar</button>
        </form>
    </div>

    <script>
        class ChatApp {
            constructor(userId) {
                this.userId = userId;
                this.chatBox = document.getElementById('chat-box');
                this.form = document.getElementById('form-chat');
                this.init();
            }

            init() {
                this.loadMessages();
                this.form.addEventListener('submit', e => {
                    e.preventDefault();
                    this.sendMessage();
                });
                // Actualizar mensajes cada 3 segundos
                setInterval(() => this.loadMessages(), 3000);
            }

            async loadMessages() {
                try {
                    const response = await fetch('/proyecto/app/controladores/controladorChat.php?action=obtener_mensajes');
                    const messages = await response.json();

                    this.chatBox.innerHTML = messages.map(msg => {
                        const isMe = msg.emisor_id == this.userId;
                        return `
        <div class="message ${isMe ? 'my-message' : 'other-message'}">
            <p>${this.escapeHtml(msg.mensaje)}</p>
            <small>${new Date(msg.fecha).toLocaleString()}</small>
        </div>
    `;
                    }).join('');

                    this.chatBox.scrollTop = this.chatBox.scrollHeight;
                } catch (error) {
                    console.error('Error cargando mensajes:', error);
                }
            }

            async sendMessage() {
                const mensajeInput = this.form.mensaje;
                const mensaje = mensajeInput.value.trim();
                if (!mensaje) return;

                const formData = new FormData();
                formData.append('mensaje', mensaje);
                formData.append('action', 'enviar');

                try {
                    const response = await fetch('/proyecto/app/controladores/controladorChat.php', {
                        method: 'POST',
                        body: formData
                    });

                    const data = await response.json();

                    if (data.success) {
                        mensajeInput.value = '';
                        this.loadMessages();
                    } else {
                        alert('Error al enviar mensaje: ' + (data.error || 'Error desconocido'));
                    }
                } catch (error) {
                    console.error('Error enviando mensaje:', error);
                }
            }

            escapeHtml(text) {
                if (!text) return '';
                return text
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            const userId = parseInt(document.body.dataset.userId || '0');
            if (userId) {
                new ChatApp(userId);
            }
        });
    </script>

</body>

</html>
