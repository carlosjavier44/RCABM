<?php
require_once __DIR__ . '/../../../config/config.php';

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

            <button class="btn btn-success mt-3" id="btnAñadirCarrito" data-id="<?= $producto['id'] ?>">Añadir al carrito</button>
            <div id="mensajeCarrito" class="mt-2"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnAñadir = document.getElementById('btnAñadirCarrito');
    const mensaje = document.getElementById('mensajeCarrito');

    if (btnAñadir) {
        btnAñadir.addEventListener('click', function() {
            const id = btnAñadir.getAttribute('data-id');
            if (!id) return;

            fetch('/app/controladores/controladorCarrito.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'add', id: parseInt(id) })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    mensaje.textContent = 'Producto añadido al carrito';
                    mensaje.style.color = 'green';
                } else {
                    mensaje.textContent = data.message || 'Error al añadir producto';
                    mensaje.style.color = 'red';
                }
            })
            .catch(() => {
                mensaje.textContent = 'Error en la comunicación con el servidor';
                mensaje.style.color = 'red';
            });
        });
    }
});
</script>
</body>
</html>

<?php
else:
    echo "<p class='text-center text-danger'>Producto no encontrado.</p>";
endif;

$conn->close();
?>
