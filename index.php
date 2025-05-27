<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ob_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCABM</title>
    <link rel="stylesheet" href="public/css/estilos.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script src="public/js/scripts.js" defer></script>
    <script src="public/js/carrito.js" defer></script>
    <script src="public/js/chat.js" defer></script>
</head>

<body>
    <header>
        <?php include __DIR__ . '/includes/header.php'; ?>
    </header>

    <main id="main" class="container mt-5 mb-5" data-loaded-view="<?= htmlspecialchars(isset($_GET['view']) ? $_GET['view'] : 'productos') ?>">
        <?php
        if (isset($_SESSION['mensaje'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['mensaje']) . '</div>';
            unset($_SESSION['mensaje']);
        }

        $view = isset($_GET['view']) ? $_GET['view'] : 'productos';

        $valid_views = [
            'login' => 'app/vistas/usuarios/login.php',
            'register' => 'app/vistas/usuarios/register.php',
            'productos' => 'app/vistas/productos/productos.php',
            'carrito' => 'app/vistas/carrito/ver_carrito.php',
            'chat' => 'app/vistas/chat/chat.php',
            'pedidos' => 'app/vistas/pedidos/lista.php',
            'detalle' => 'app/vistas/productos/detalle.php',
            'seguimiento' => 'app/vistas/productos/seguimiento.php',
            'finalizar_compra' => 'app/vistas/carrito/finalizar_compra.php'
        ];

        if (array_key_exists($view, $valid_views)) {
            include __DIR__ . '/' . $valid_views[$view];
        } else {
            echo "<h2 class='text-center'>Página no encontrada</h2>";
        }
        ?>
    </main>

    <footer>
        <?php include __DIR__ . '/includes/footer.php'; ?>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const mainContent = document.getElementById('main');
            const urlParams = new URLSearchParams(window.location.search);
            const view = urlParams.get('view') || 'productos';
            const id = urlParams.get('id') || '';

            if (!mainContent.dataset.loadedView || mainContent.dataset.loadedView !== view) {
                loadView(view, id);
            }
        });
    </script>

</body>

</html>
