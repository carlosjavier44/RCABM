<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Acceso denegado']);
    exit;
}

$admin_id = $_SESSION['usuario']['id']; // Ahora dinámico según sesión

// Listar usuarios que tienen mensajes con el admin y número de mensajes no leídos
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['listar_usuarios'])) {
    $sql = "SELECT DISTINCT 
                u.id, u.nombre,
                (SELECT COUNT(*) FROM mensajes m2 WHERE m2.emisor_id = u.id AND m2.receptor_id = ? AND m2.leido = 0) AS no_leidos
            FROM mensajes m 
            JOIN usuarios u ON (u.id = m.emisor_id OR u.id = m.receptor_id)
            WHERE (m.emisor_id = ? OR m.receptor_id = ?) 
              AND u.id != ?
            ORDER BY u.nombre ASC";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => $conn->error]);
        exit;
    }

    $stmt->bind_param("iiii", $admin_id, $admin_id, $admin_id, $admin_id);
    $stmt->execute();
    $resultado = $stmt->get_result();

    $usuarios = [];

    while ($row = $resultado->fetch_assoc()) {
        $usuarios[] = [
            'id' => $row['id'],
            'nombre' => $row['nombre'],
            'no_leidos' => (int)$row['no_leidos'],
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($usuarios);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['usuario_id'])) {
    $usuario_id = intval($_GET['usuario_id']);

    // Marcar mensajes como leídos (los que usuario envió y admin no ha leído)
    $update = "UPDATE mensajes SET leido = 1 WHERE emisor_id = ? AND receptor_id = ? AND leido = 0";
    $stmtUpdate = $conn->prepare($update);
    $stmtUpdate->bind_param("ii", $usuario_id, $admin_id);
    $stmtUpdate->execute();
    $stmtUpdate->close();

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

// Enviar mensaje admin → usuario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mensaje'], $_POST['usuario_id'])) {
    $mensaje = trim($_POST['mensaje']);
    $receptor_id = intval($_POST['usuario_id']);

    if ($mensaje === '') {
        http_response_code(400);
        echo "Mensaje vacío.";
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO mensajes (emisor_id, receptor_id, mensaje, fecha, leido) VALUES (?, ?, ?, NOW(), 0)");
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
