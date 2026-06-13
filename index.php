<?php
require_once __DIR__ . '/config/config.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Regalos con Amor by Moni – Regalos personalizados</title>
  <meta name="description" content="Regalos personalizados hechos con amor para cada ocasión.">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="public/css/estilos.css">
</head>
<body>

<header><?php include __DIR__ . '/includes/header.php'; ?></header>

<main id="main">
<?php
if (isset($_SESSION['mensaje'])) {
    echo '<div class="alert alert-success" style="max-width:900px;margin:1rem auto"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($_SESSION['mensaje']) . '</div>';
    unset($_SESSION['mensaje']);
}

$view = $_GET['view'] ?? 'inicio';

$vistas = [
    'login'           => 'app/vistas/usuarios/login.php',
    'register'        => 'app/vistas/usuarios/register.php',
    'productos'       => 'app/vistas/productos/productos.php',
    'detalle'         => 'app/vistas/productos/detalle.php',
    'carrito'         => 'app/vistas/carrito/ver_carrito.php',
    'finalizar_compra'=> 'app/vistas/carrito/finalizar_compra.php',
    'chat'            => 'app/vistas/chat/chat.php',
    'chat_admin'      => 'app/vistas/chat/admin.php',
    'pedidos'         => 'app/vistas/pedidos/lista.php',
    'detalle_pedido'  => 'app/vistas/pedidos/detalle_pedido.php',
    'admin_pedidos'   => 'app/vistas/pedidos/admin_pedidos.php',
    'detalle_admin'   => 'app/vistas/pedidos/detalle_admin.php',
    'admin_productos' => 'app/vistas/admin/productos.php',
];

if ($view === 'inicio' || $view === 'productos') {
    $hayBusqueda = !empty($_GET['q']) || !empty($_GET['categoria']);
    if (!$hayBusqueda) {
        echo '
        <section class="hero">
          <p class="hero-eyebrow">✦ Hechos con amor ✦</p>
          <h2>El regalo perfecto<br>para cada momento</h2>
          <p>Regalos personalizados que dejan huella. Porque los detalles que vienen del corazón son los que más importan.</p>
          <a href="index.php?view=productos" class="btn-hero"><i class="fas fa-gift"></i> Explorar productos</a>
        </section>
        <div id="productos-section">';
        include __DIR__ . '/app/vistas/productos/productos.php';
        echo '</div>';
    } else {
        include __DIR__ . '/app/vistas/productos/productos.php';
    }
} elseif (isset($vistas[$view])) {
    include __DIR__ . '/' . $vistas[$view];
} else {
    echo '<div class="empty-state"><i class="fas fa-compass"></i><p>Página no encontrada.</p><a href="index.php" class="btn-secondary">Volver a la tienda</a></div>';
}
?>
</main>

<footer><?php include __DIR__ . '/includes/footer.php'; ?></footer>

<script src="public/js/scripts.js" defer></script>
</body>
</html>
