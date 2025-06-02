<?php

require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    header("Location: ../usuarios/login.php");
    exit();
}

$usuarioSeleccionado = isset($_GET['usuario_id']) ? intval($_GET['usuario_id']) : null;
$nombreUsuario = null;

if ($usuarioSeleccionado) {
    // Obtener nombre del usuario seleccionado para mostrar en el título
    $stmt = $conn->prepare("SELECT nombre FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $usuarioSeleccionado);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($fila = $resultado->fetch_assoc()) {
        $nombreUsuario = $fila['nombre'];
    } else {
        $nombreUsuario = "Usuario #$usuarioSeleccionado";
    }
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Panel de Chat - Administrador</title>
    <link rel="stylesheet" href="/proyecto/public/css/estilos.css" />
    <style>
        /* Aquí va todo tu CSS (igual que antes) */
        .admin-chat-container {
            display: flex;
            height: 60vh;
            border: 1px solid #ccc;
            margin: 20px;
            border-radius: 8px;
            overflow: hidden;
            font-family: Arial, sans-serif;
        }

        .lista-usuarios {
            width: 25%;
            border-right: 1px solid #ccc;
            overflow-y: auto;
            padding: 10px;
            background: #f9f9f9;
        }

        .lista-usuarios div {
            padding: 10px;
            border-bottom: 1px solid #ddd;
            cursor: pointer;
        }

        .lista-usuarios div div:hover {
            background-color: #e0e0e0;
        }

        .chat-mensajes {
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            padding: 10px;
        }

        #chat-title {
            margin: 0 0 10px 0;
            font-weight: bold;
        }

        #chat-box {
            flex-grow: 1;
            border: 1px solid #ccc;
            padding: 10px;
            overflow-y: auto;
            background: #fff;
            margin-bottom: 10px;
            border-radius: 4px;
        }

        #chat-box div {
            margin-bottom: 8px;
            padding: 6px 10px;
            border-radius: 10px;
            max-width: 60%;
            word-wrap: break-word;
        }

        #chat-box .Admin {
            background-color: #d1ffd6;
            align-self: flex-end;
            font-weight: bold;
        }

        #chat-box .Usuario {
            background-color: #f1f1f1;
            align-self: flex-start;
        }

        #form-chat {
            display: flex;
        }

        #form-chat input[type="text"] {
            flex-grow: 1;
            padding: 10px;
            font-size: 1rem;
            border-radius: 4px 0 0 4px;
            border: 1px solid #ccc;
            outline: none;
        }

        #form-chat button {
            padding: 10px 20px;
            font-size: 1rem;
            border: none;
            background-color: #007bff;
            color: white;
            cursor: pointer;
            border-radius: 0 4px 4px 0;
        }

        #form-chat button:hover {
            background-color: #0056b3;
        }

        p {
            font-style: italic;
        }

        #chat-box {
            max-height: 500px;
            overflow-y: auto;
            padding: 10px;
            background: #f9f9f9;
        }

        .mensaje-chat {
            max-width: 70%;
            margin: 8px 0;
            padding: 10px;
            border-radius: 10px;
            clear: both;
            word-wrap: break-word;
        }

        #mensaje{
            border: solid gray 2px ;
            border-radius: 25px;
        }

        .mensaje-chat.usuario {
            background-color: #e0e0e0;
            text-align: left;
            float: left;
        }

        .mensaje-chat.admin {
            background-color: #d1ffd6;
            text-align: right;
            float: right;
        }

        .mensaje-chat p {
            margin: 5px 0;
        }

        .mensaje-chat small {
            display: block;
            font-size: 0.75em;
            color: #555;
            margin-top: 4px;
        }
    </style>
</head>

<body>

    <h2 style="text-align:center; margin-top: 20px;">Panel de Chat - Administrador</h2>

    <div class="admin-chat-container">
        <div class="lista-usuarios">
            <h3>Conversaciones</h3>
            <div id="conversaciones-list">
            </div>
        </div>

        <div class="chat-mensajes">
            <?php if ($usuarioSeleccionado): ?>
                <h3 id="chat-title">
                    <?= htmlspecialchars($nombreUsuario) ?>
                </h3>
                <div id="chat-box" class="chat-box"></div>

                <form id="formularioChatAdmin" method="post" autocomplete="off">
                    <input type="hidden" id="usuarioId" name="usuario_id" value="<?= $usuarioSeleccionado ?>">
                    <input type="text" id="mensaje" name="mensaje" placeholder="Escribe tu mensaje" required />
                    <button type="submit" class="mensaje">📩</button>
                </form>
            <?php else: ?>
                <p>Selecciona un usuario de la lista para comenzar a chatear.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="/proyecto/public/js/chatAdmin.js"></script>
</body>

</html>