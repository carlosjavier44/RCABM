<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/modelos/Carrito.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p class='text-center text-danger'>Producto no válido.</p>";
    exit;
}

$id = (int) $_GET['id'];

$sql = "SELECT * FROM productos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($producto = $result->fetch_assoc()):
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Detalle Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <img src="<?= htmlspecialchars($producto['imagen']) ?>" alt="<?= htmlspecialchars($producto['nombre']) ?>" class="img-fluid" />
        </div>
        <div class="col-md-6">
            <h2><?= htmlspecialchars($producto['nombre']) ?></h2>
            <p class="text-muted"><?= htmlspecialchars($producto['categoria']) ?></p>
            <h3 class="text-primary"><?= number_format($producto['precio'], 2, ',', '.') ?> €</h3>
            <p><?= htmlspecialchars($producto['descripcion']) ?></p>
            <p><strong>Stock disponible:</strong> <?= $producto['stock'] ?></p>

            <?php if (isset($_SESSION['usuario'])): ?>
                <form method="post" action="">
                    <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
                    <input type="submit" name="añadir_carrito" class="btn btn-success mt-3" value="Añadir al carrito">
                </form>
                <div class="mt-2 text-success">
                    <?= isset($_SESSION['mensaje_carrito']) ? $_SESSION['mensaje_carrito'] : '' ?>
                    <?php unset($_SESSION['mensaje_carrito']); ?>
                </div>
            <?php else: ?>
                <p class="text-danger mt-3">Inicia sesión para añadir al carrito.</p>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>

<?php
else:
    echo "<p class='text-center text-danger'>Producto no encontrado.</p>";
endif;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['añadir_carrito'], $_SESSION['usuario'])) {
    $producto_id = (int) $_POST['producto_id'];
    $usuario_id = $_SESSION['usuario']['id'];
    $carrito = new Carrito($conn);
    $carrito->añadirProducto($usuario_id, $producto_id, 1);
    $_SESSION['mensaje_carrito'] = 'Producto añadido al carrito correctamente';
    header("Location: /RCABM/index.php?view=detalle&id=" . $producto_id);

    exit;
}

$conn->close();
?>
