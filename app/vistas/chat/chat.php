<?php

if (!isset($_SESSION['usuario'])) {
    header("Location: /index.php?pagina=login");

    exit;
}

$usuario = $_SESSION['usuario'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Chat</title>
    <link rel="stylesheet" href="/public/css/estilos.css" />
    <style>
        /* Estilos básicos para chat */
        #usuarios-lista {
            width: 25%;
            float: left;
            border-right: 1px solid #ccc;
            height: 500px;
            overflow-y: auto;
        }
        #chat-ventana {
            width: 70%;
            float: left;
            height: 500px;
            display: flex;
            flex-direction: column;
        }
        #mensajes {
            flex: 1;
            overflow-y: auto;
            padding: 10px;
            border-bottom: 1px solid #ccc;
        }
        #input-mensaje {
            display: flex;
        }
        #input-mensaje input[type=text] {
            flex: 1;
            padding: 10px;
            font-size: 1em;
        }
        #input-mensaje button {
            padding: 10px 20px;
            font-size: 1em;
        }
        .mensaje {
            margin-bottom: 10px;
        }
        .mensaje.emisor {
            text-align: right;
            color: blue;
        }
        .mensaje.receptor {
            text-align: left;
            color: green;
        }
        .usuario-item {
            cursor: pointer;
            padding: 5px;
            border-bottom: 1px solid #eee;
        }
        .usuario-item.activo {
            background-color: #ddd;
        }
    </style>
</head>
<body>
    <h2>Chat</h2>
    <div style="display: flex;">
        <?php if ($usuario['rol'] === 'admin'): ?>
        <div id="usuarios-lista">
            <h3>Usuarios</h3>
            <div id="listaUsuarios"></div>
        </div>
        <?php endif; ?>
        <div id="chat-ventana">
            <div id="mensajes"></div>
            <div id="input-mensaje">
                <input type="text" id="mensajeTexto" placeholder="Escribe un mensaje..." autocomplete="off" />
                <button id="btnEnviar">Enviar</button>
            </div>
        </div>
    </div>

<script>
const usuarioRol = '<?php echo $usuario['rol']; ?>';
const usuarioId = <?php echo (int)$usuario['id']; ?>;

let receptorId = usuarioRol === 'cliente' ? 1 : null; // admin id = 1 por defecto

const mensajesDiv = document.getElementById('mensajes');
const listaUsuariosDiv = document.getElementById('listaUsuarios');
const inputMensaje = document.getElementById('mensajeTexto');
const btnEnviar = document.getElementById('btnEnviar');

function agregarMensaje(mensaje) {
    const div = document.createElement('div');
    div.classList.add('mensaje');
    if (mensaje.emisor_id == usuarioId) {
        div.classList.add('emisor');
        div.textContent = "Tú: " + mensaje.mensaje;
    } else {
        div.classList.add('receptor');
        div.textContent = "Admin: " + mensaje.mensaje;
    }
    mensajesDiv.appendChild(div);
    mensajesDiv.scrollTop = mensajesDiv.scrollHeight;
}

function cargarMensajes() {
    if (!receptorId) return;
    fetch('/app/controladores/controladorChat.php?accion=cargarMensajes&otro_usuario_id=' + receptorId)
    .then(res => res.json())
    .then(data => {
        mensajesDiv.innerHTML = '';
        if (data.error) {
            mensajesDiv.textContent = data.error;
            return;
        }
        data.forEach(mensaje => agregarMensaje(mensaje));
    });
}

function enviarMensaje() {
    const msg = inputMensaje.value.trim();
    if (!msg) return;
    let datos = new URLSearchParams();
    datos.append('accion', 'enviarMensaje');
    datos.append('mensaje', msg);
    if (usuarioRol === 'admin') {
        if (!receptorId) {
            alert('Selecciona un usuario primero');
            return;
        }
        datos.append('receptor_id', receptorId);
    }
    fetch('/app/controladores/controladorChat.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: datos.toString()
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            inputMensaje.value = '';
            cargarMensajes();
        } else {
            alert('Error: ' + (data.error || 'No se pudo enviar'));
        }
    })
    .catch(() => alert('Error en la conexión'));
}

btnEnviar.addEventListener('click', enviarMensaje);
inputMensaje.addEventListener('keydown', e => {
    if (e.key === 'Enter') enviarMensaje();
});

if (usuarioRol === 'admin') {
    // Cargar lista de usuarios
    function cargarUsuarios() {
        fetch('/app/controladores/controladorChat.php?accion=usuariosChat')
        .then(res => res.json())
        .then(data => {
            listaUsuariosDiv.innerHTML = '';
            if (data.error) {
                listaUsuariosDiv.textContent = data.error;
                return;
            }
            data.forEach(usuario => {
                const div = document.createElement('div');
                div.classList.add('usuario-item');
                div.textContent = usuario.nombre;
                div.dataset.id = usuario.id;
                div.addEventListener('click', () => {
                    receptorId = usuario.id;
                    document.querySelectorAll('.usuario-item').forEach(el => el.classList.remove('activo'));
                    div.classList.add('activo');
                    cargarMensajes();
                });
                listaUsuariosDiv.appendChild(div);
            });
        });
    }
    cargarUsuarios();
    // Actualizar mensajes cada 3 segundos
    setInterval(() => {
        if (receptorId) cargarMensajes();
    }, 3000);
} else {
    // Cliente solo carga y refresca mensajes con admin (id=1)
    setInterval(cargarMensajes, 3000);
    cargarMensajes();
}
</script>
</body>
</html>
