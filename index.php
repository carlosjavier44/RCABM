<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
ob_start();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>RCABM</title>
    <link rel="stylesheet" href="public/css/estilos.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
    <script src="public/js/scripts.js"></script>
    <script src="public/js/carrito.js"></script>
    <script src="public/js/chat.js"></script>

</head>

<body>
    <header>
        <?php include __DIR__ . '/includes/header.php'; ?>
    </header>

    <main id="main" class="container mt-5 mb-5">

        <?php
        if (isset($_SESSION['mensaje'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['mensaje']) . '</div>';
            unset($_SESSION['mensaje']);
        }
        ?>

        <?php
        $view = isset($_GET['view']) ? $_GET['view'] : 'productos';

        $valid_views = [
            'login' => 'app/vistas/usuarios/login.php',
            'register' => 'app/vistas/usuarios/register.php',
            'productos' => 'app/vistas/productos/productos.php',
            'carrito' => 'app/vistas/carrito/ver_carrito.php',
            'chat' => 'app/vistas/chat/chat.php'
        ];

        if (array_key_exists($view, $valid_views)) {
            include __DIR__ . '/' . $valid_views[$view];
        } else {
            echo "<h2>Página no encontrada</h2>";
        }
        ?>
    </main>

    <footer>
        <?php include __DIR__ . '/includes/footer.php'; ?>
    </footer>

</body>

</html>