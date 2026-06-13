<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../modelos/Carrito.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario']['id'])) {
    echo json_encode(['success'=>false,'message'=>'No autenticado']); exit;
}

$uid    = $_SESSION['usuario']['id'];
$accion = $_POST['accion'] ?? '';
$carrito = new Carrito($conn);

switch ($accion) {
    case 'añadirProducto':
        $pid  = (int)($_POST['producto_id'] ?? 0);
        $cant = (int)($_POST['cantidad'] ?? 1);
        $obs  = trim($_POST['observacion'] ?? '');
        if ($pid < 1) { echo json_encode(['success'=>false]); exit; }
        $ok = $carrito->añadirProducto($uid,$pid,$cant,$obs);
        echo json_encode(['success'=>(bool)$ok]); break;

    case 'actualizarCantidad':
        $pid  = (int)($_POST['producto_id'] ?? 0);
        $cant = (int)($_POST['cantidad'] ?? 1);
        $ok   = $carrito->actualizarCantidad($uid,$pid,$cant);
        echo json_encode(['success'=>(bool)$ok]); break;

    case 'actualizarObservacion':
        $pid = (int)($_POST['producto_id'] ?? 0);
        $obs = trim($_POST['observacion'] ?? '');
        $ok  = $carrito->actualizarObservacion($uid,$pid,$obs);
        echo json_encode(['success'=>(bool)$ok]); break;

    case 'eliminarProducto':
        $pid = (int)($_POST['producto_id'] ?? 0);
        $ok  = $carrito->eliminarProducto($uid,$pid);
        echo json_encode(['success'=>(bool)$ok]); break;

    case 'vaciarCarrito':
        $ok = $carrito->vaciarCarrito($uid);
        echo json_encode(['success'=>(bool)$ok]); break;

    default:
        echo json_encode(['success'=>false,'message'=>'Acción no válida']);
}
$conn->close();
