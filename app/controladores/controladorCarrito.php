<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../../config/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

header('Content-Type: application/json');

$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['action'])) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

$action = $data['action'];

if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

switch ($action) {
    case 'add':
        if (!isset($data['id']) || !is_numeric($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID de producto inválido']);
            exit;
        }

        $id = (int) $data['id'];

        $sql = "SELECT * FROM productos WHERE id = ?";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            echo json_encode(['success' => false, 'message' => 'Error en la consulta']);
            exit;
        }
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($producto = $result->fetch_assoc()) {
            if (isset($_SESSION['carrito'][$id])) {
                $_SESSION['carrito'][$id]['cantidad']++;
            } else {
                $_SESSION['carrito'][$id] = [
                    'nombre' => $producto['nombre'],
                    'precio' => (float) $producto['precio'],
                    'cantidad' => 1,
                ];
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
        }
        break;

    case 'remove':
        if (!isset($data['id']) || !is_numeric($data['id'])) {
            echo json_encode(['success' => false, 'message' => 'ID de producto inválido']);
            exit;
        }
        $id = (int) $data['id'];
        if (isset($_SESSION['carrito'][$id])) {
            unset($_SESSION['carrito'][$id]);
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Producto no en el carrito']);
        }
        break;

    case 'update':
        if (!isset($data['id'], $data['cantidad']) || !is_numeric($data['id']) || !is_numeric($data['cantidad'])) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos para actualización']);
            exit;
        }
        $id = (int) $data['id'];
        $cantidad = (int) $data['cantidad'];
        if ($cantidad < 1) {
            echo json_encode(['success' => false, 'message' => 'Cantidad inválida']);
            exit;
        }
        if (isset($_SESSION['carrito'][$id])) {
            $_SESSION['carrito'][$id]['cantidad'] = $cantidad;
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Producto no en el carrito']);
        }
        break;

    case 'clear':
        $_SESSION['carrito'] = [];
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}

$conn->close();
