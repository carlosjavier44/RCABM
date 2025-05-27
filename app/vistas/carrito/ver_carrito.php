<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$carrito = $_SESSION['carrito'] ?? [];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Carrito de Compras</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
    <div class="container mt-5">
        <h2>🛒 Carrito de Compras</h2>

        <?php if (empty($carrito)): ?>
            <p>El carrito está vacío.</p>
        <?php else: ?>
            <table class="table table-bordered align-middle">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Total</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($carrito as $id => $producto): ?>
                        <tr>
                            <td><?= htmlspecialchars($producto['nombre']) ?></td>
                            <td>$<?= number_format($producto['precio'], 2) ?></td>
                            <td>
                                <input
                                    type="number"
                                    min="1"
                                    class="form-control inputCantidad"
                                    data-id="<?= $id ?>"
                                    value="<?= $producto['cantidad'] ?>"
                                    style="width: 80px;"
                                />
                            </td>
                            <td>$<?= number_format($producto['precio'] * $producto['cantidad'], 2) ?></td>
                            <td>
                                <button class="btn btn-danger btnEliminar" data-id="<?= $id ?>">Eliminar</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <p><strong>Total: </strong>$
                <?= number_format(array_reduce($carrito, fn($total, $p) => $total + $p['precio'] * $p['cantidad'], 0), 2) ?>
            </p>

            <button class="btn btn-warning" id="vaciarCarrito">Vaciar Carrito</button>
        <?php endif; ?>

        <br /><br />
        <a href="/proyecto/index.php" class="btn btn-primary">Seguir Comprando</a>
    </div>

    <script src="/proyecto/public/js/carrito.js"></script>
</body>
</html>
