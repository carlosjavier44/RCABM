<?php
session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../modelos/Carrito.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario']['id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];
$accion = $_POST['accion'] ?? '';

$carrito = new Carrito($conn);

switch ($accion) {
    case 'actualizarCantidad':
        $producto_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $cantidad = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 1;
        if ($producto_id < 1 || $cantidad < 1) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }
        $success = $carrito->actualizarCantidad($usuario_id, $producto_id, $cantidad);
        echo json_encode(['success' => $success]);
        break;

    case 'eliminarProducto':
        $producto_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($producto_id < 1) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }
        $success = $carrito->eliminarProducto($usuario_id, $producto_id);
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Producto eliminado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al eliminar producto.']);
        }
        break;

    case 'vaciarCarrito':
        $success = $carrito->vaciarCarrito($usuario_id);
        if ($success) {
            echo json_encode(['success' => true, 'message' => 'Carrito vaciado correctamente.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al vaciar el carrito.']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no válida']);
        break;
}

$conn->close();
