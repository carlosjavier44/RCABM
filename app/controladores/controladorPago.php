<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../modelos/Carrito.php';

session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: /index.php?view=login");
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];

$nombre = $_POST['nombre_titular'] ?? '';
$numero = $_POST['numero_tarjeta'] ?? '';
$exp = $_POST['fecha_exp'] ?? '';
$cvc = $_POST['cvc'] ?? '';

if (strlen($numero) !== 16 || strlen($cvc) !== 3) {
    $_SESSION['mensaje_error'] = 'Datos de tarjeta inválidos (simulación)';
    header("Location: /index.php?view=carrito");
    exit;
}

$carrito = new Carrito($conn);
$productos = $carrito->obtenerProductos($usuario_id);

if (empty($productos)) {
    $_SESSION['mensaje_error'] = 'El carrito está vacío.';
    header("Location: /index.php?view=carrito");
    exit;
}

$total = 0;
foreach ($productos as $p) {
    $total += $p['precio'] * $p['cantidad'];
}

$stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, total) VALUES (?, ?)");
$stmt->bind_param("id", $usuario_id, $total);
if (!$stmt->execute()) {
    $_SESSION['mensaje_error'] = 'Error al registrar el pedido.';
    header("Location: /index.php?view=carrito");
    exit;
}
$pedido_id = $stmt->insert_id;
$stmt->close();

$stmt_detalle = $conn->prepare("INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, subtotal) VALUES (?, ?, ?, ?)");
foreach ($productos as $p) {
    $subtotal = $p['precio'] * $p['cantidad'];
    $stmt_detalle->bind_param("iiid", $pedido_id, $p['producto_id'], $p['cantidad'], $subtotal);
    $stmt_detalle->execute();
}
$stmt_detalle->close();

$carrito->vaciarCarrito($usuario_id);

$_SESSION['mensaje_exito'] = "Pago ficticio realizado. Pedido #$pedido_id registrado.";
header("Location: /index.php?view=pedidos");
exit;
