<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

$admin_id = 1; // ID fijo del admin

// Listar usuarios con los que el admin ha chateado
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['listar_usuarios'])) {
    $sql = "SELECT DISTINCT 
                u.id, u.nombre 
            FROM mensajes m 
            JOIN usuarios u ON (u.id = m.emisor_id OR u.id = m.receptor_id)
            WHERE (m.emisor_id = ? OR m.receptor_id = ?) 
              AND u.id != ?";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => $conn->error]);
        exit;
    }

    $stmt->bind_param("iii", $admin_id, $admin_id, $admin_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $usuarios = [];

    while ($row = $resultado->fetch_assoc()) {
        $usuarios[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($usuarios);
    exit;
}

// Obtener mensajes de un usuario concreto
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['usuario_id'])) {
    $usuario_id = intval($_GET['usuario_id']);

    $sql = "SELECT m.*, 
                CASE WHEN m.emisor_id = ? THEN 'Admin' ELSE u.nombre END AS emisor_nombre
            FROM mensajes m
            LEFT JOIN usuarios u ON u.id = m.emisor_id
            WHERE (m.emisor_id = ? AND m.receptor_id = ?) OR (m.emisor_id = ? AND m.receptor_id = ?)
            ORDER BY m.fecha ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => $conn->error]);
        exit;
    }

    $stmt->bind_param("iiiii", $admin_id, $admin_id, $usuario_id, $usuario_id, $admin_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $mensajes = [];

    while ($row = $resultado->fetch_assoc()) {
        $mensajes[] = [
            'id' => $row['id'],
            'emisor' => $row['emisor_nombre'],
            'mensaje' => $row['mensaje'],
            'fecha' => $row['fecha']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($mensajes);
    exit;
}

// Enviar mensaje del admin a un usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'], $_POST['usuario_id'])) {
    $mensaje = trim($_POST['mensaje']);
    $receptor_id = intval($_POST['usuario_id']);

    if ($mensaje === '') {
        http_response_code(400);
        echo "Mensaje vacío.";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO mensajes (emisor_id, receptor_id, mensaje, fecha) VALUES (?, ?, ?, NOW())");
    if (!$stmt) {
        http_response_code(500);
        echo "Error en prepare: " . $conn->error;
        exit;
    }

    $stmt->bind_param("iis", $admin_id, $receptor_id, $mensaje);

    if ($stmt->execute()) {
        echo "Mensaje enviado correctamente.";
    } else {
        http_response_code(500);
        echo "Error al enviar el mensaje: " . $stmt->error;
    }

    $stmt->close();
    exit;
}

http_response_code(400);
echo "Solicitud inválida.";
