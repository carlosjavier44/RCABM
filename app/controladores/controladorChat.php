<?php
// app/controladores/controladorChat.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../modelos/Chat.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$chat = new Chat();

$accion = $_POST['accion'] ?? $_GET['accion'] ?? null;
$usuario = $_SESSION['usuario'] ?? null;

if (!$usuario) {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

switch ($accion) {
    case 'enviarMensaje':
        $mensaje = trim($_POST['mensaje'] ?? '');
        $receptor_id = intval($_POST['receptor_id'] ?? 0);
        $emisor_id = $usuario['id'];

        if (!$mensaje) {
            echo json_encode(['error' => 'Mensaje vacío']);
            exit;
        }

        if ($usuario['rol'] === 'cliente') {
            // Cliente solo puede enviar al admin (suponemos admin id = 1)
            $receptor_id = 1;
        }

        if (!$receptor_id) {
            echo json_encode(['error' => 'Receptor inválido']);
            exit;
        }

        $enviado = $chat->enviarMensaje($emisor_id, $receptor_id, $mensaje);
        if ($enviado) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['error' => 'Error al guardar mensaje']);
        }
        break;

    case 'cargarMensajes':
        $otro_usuario_id = intval($_GET['otro_usuario_id'] ?? 0);
        $emisor_id = $usuario['id'];

        if (!$otro_usuario_id) {
            echo json_encode(['error' => 'Usuario inválido']);
            exit;
        }

        $mensajes = $chat->obtenerMensajesEntre($emisor_id, $otro_usuario_id);
        echo json_encode($mensajes);
        break;

    case 'usuariosChat':
        // Sólo para admin: obtener lista de usuarios que han enviado mensajes
        if ($usuario['rol'] !== 'admin') {
            echo json_encode(['error' => 'No autorizado']);
            exit;
        }

        $usuarios = $chat->obtenerUsuariosConChat();
        echo json_encode($usuarios);
        break;

    default:
        echo json_encode(['error' => 'Acción no válida']);
}
