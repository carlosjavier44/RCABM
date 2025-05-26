<?php
//controladorCarrito
require_once __DIR__ . '/../config/config.php'; // Incluir config.php
require_once __DIR__ . '/../modelos/Carrito.php'; // Incluir el modelo Carrito

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action']) && $data['action'] === 'añadir') {
        if (!isset($_SESSION['usuario_id'])) {
            echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para añadir productos al carrito']);
            exit;
        }

        $usuario_id = $_SESSION['usuario_id'];
        $producto_id = $data['producto_id'];
        $cantidad = $data['cantidad'];

        $carrito = new Carrito($conn); // Pasar la conexión al modelo
        if ($carrito->añadirProducto($usuario_id, $producto_id, $cantidad)) {
            echo json_encode(['success' => true, 'message' => 'Producto añadido al carrito']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al añadir el producto al carrito']);
        }
    }
}
?>