<?php
session_start();
require_once __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['usuario']['id'])) {
    echo json_encode(['success' => false, 'error' => 'No autenticado']);
    exit();
}

$usuarioId = $_SESSION['usuario']['id'];
$adminId = 1;

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

if ($action === 'obtener_mensajes') {
    $stmt = $conn->prepare("SELECT m.*, u.nombre AS emisor_nombre FROM mensajes m 
        JOIN usuarios u ON m.emisor_id = u.id
        WHERE (emisor_id = ? AND receptor_id = ?) OR (emisor_id = ? AND receptor_id = ?) 
        ORDER BY fecha ASC");
    $stmt->bind_param("iiii", $usuarioId, $adminId, $adminId, $usuarioId);
    $stmt->execute();
    $result = $stmt->get_result();

    $mensajes = [];
    while ($row = $result->fetch_assoc()) {
        $mensajes[] = [
            'id' => $row['id'],
            'emisor_id' => $row['emisor_id'],
            'mensaje' => $row['mensaje'],
            'fecha' => $row['fecha'],
            'emisor_nombre' => $row['emisor_nombre'],
        ];
    }

    echo json_encode($mensajes);
    exit();
}

if ($action === 'enviar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = trim($_POST['mensaje'] ?? '');

    if ($mensaje === '') {
        echo json_encode(['success' => false, 'error' => 'Mensaje vacío']);
        exit();
    }

    // Insertar mensaje en la tabla mensajes
    $stmt = $conn->prepare("INSERT INTO mensajes (emisor_id, receptor_id, mensaje, fecha) VALUES (?, ?, ?, NOW())");
    $stmt->bind_param("iis", $usuarioId, $adminId, $mensaje);
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Error al guardar el mensaje']);
    }
    exit();
}

echo json_encode(['success' => false, 'error' => 'Acción no permitida']);
exit();
