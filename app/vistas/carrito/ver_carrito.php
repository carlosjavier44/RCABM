<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/modelos/Carrito.php';

if (!isset($_SESSION['usuario'])) {
    echo "<p>Debes iniciar sesión para ver el carrito.</p>";
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];
$carritoObj = new Carrito($conn);

$observaciones_form = $_SESSION['observaciones'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['actualizar'])) {
        $producto_id = (int) $_POST['producto_id'];
        $cantidad = (int) $_POST['cantidad'];
        $carritoObj->actualizarCantidad($usuario_id, $producto_id, $cantidad);

        $_SESSION['observaciones'][$producto_id] = $_POST['observacion_actual'] ?? '';

        header("Location: index.php?view=carrito");
        exit;
    }

    if (isset($_POST['eliminar'])) {
        $producto_id = (int) $_POST['producto_id'];
        $carritoObj->eliminarProducto($usuario_id, $producto_id);
        unset($_SESSION['observaciones'][$producto_id]);
        header("Location: index.php?view=carrito");
        exit;
    }

    if (isset($_POST['vaciar'])) {
        $carritoObj->vaciarCarrito($usuario_id);
        unset($_SESSION['observaciones']);
        header("Location: index.php?view=carrito");
        exit;
    }

    if (isset($_POST['finalizar'])) {
        $productos = $carritoObj->obtenerProductos($usuario_id);
        $observaciones_input = $_POST['observaciones'] ?? [];

        if (!empty($productos)) {
            $total = 0;
            foreach ($productos as $p) {
                $total += $p['precio'] * $p['cantidad'];
            }

            $stmt = $conn->prepare("INSERT INTO pedidos (usuario_id, total) VALUES (?, ?)");
            $stmt->bind_param("id", $usuario_id, $total);
            $stmt->execute();
            $pedido_id = $stmt->insert_id;
            $stmt->close();

            $stmt_det = $conn->prepare("INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, subtotal, observaciones) VALUES (?, ?, ?, ?, ?)");

            foreach ($productos as $p) {
                $subtotal = $p['precio'] * $p['cantidad'];
                $obs = $observaciones_input[$p['producto_id']] ?? '';
                $stmt_det->bind_param("iiids", $pedido_id, $p['producto_id'], $p['cantidad'], $subtotal, $obs);
                $stmt_det->execute();
            }

            $stmt_det->close();
            $carritoObj->vaciarCarrito($usuario_id);
            unset($_SESSION['observaciones']);

            $_SESSION['mensaje_exito'] = "Compra realizada con éxito. Pedido #$pedido_id.";
            header("Location: index.php?view=pedidos");
            exit;
        } else {
            $_SESSION['mensaje_error'] = "Tu carrito está vacío.";
            header("Location: index.php?view=carrito");
            exit;
        }
    }
}

$productos = $carritoObj->obtenerProductos($usuario_id);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Carrito</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form').forEach(function (form) {
                form.addEventListener('keydown', function (event) {
                    if (event.key === 'Enter') {
                        event.preventDefault();
                    }
                });
            });
        });
    </script>
</head>
<body>
<div class="container mt-4">
    <h2>Carrito de compras</h2>

    <?php if (isset($_SESSION['mensaje_exito'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['mensaje_exito']; unset($_SESSION['mensaje_exito']); ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['mensaje_error'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['mensaje_error']; unset($_SESSION['mensaje_error']); ?>
        </div>
    <?php endif; ?>

    <?php if (empty($productos)): ?>
        <p>Tu carrito está vacío.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                <tr>
                    <th>Producto</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                    <th>Observaciones/Personalización</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php $total = 0; ?>
                <?php foreach ($productos as $p): ?>
                    <?php
                    $subtotal = $p['precio'] * $p['cantidad'];
                    $producto_id = $p['producto_id'];
                    $obs_value = htmlspecialchars($observaciones_form[$producto_id] ?? '', ENT_QUOTES);
                    ?>
                    <tr>
                        <form method="post">
                            <td><?= htmlspecialchars($p['nombre']) ?></td>
                            <td><?= number_format($p['precio'], 2, ',', '.') ?> €</td>
                            <td>
                                <input type="hidden" name="producto_id" value="<?= $producto_id ?>">
                                <input type="number" name="cantidad" value="<?= $p['cantidad'] ?>" min="1" required style="width: 70px;">
                            </td>
                            <td><?= number_format($subtotal, 2, ',', '.') ?> €</td>
                            <td>
                                <input type="text" name="observacion_actual" class="form-control"
                                       value="<?= $obs_value ?>">
                            </td>
                            <td class="d-flex gap-2">
                                <button type="submit" name="actualizar" class="btn btn-sm btn-primary">Actualizar</button>
                                <button type="submit" name="eliminar" class="btn btn-sm btn-danger">Eliminar</button>
                            </td>
                        </form>
                    </tr>
                    <?php $total += $subtotal; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p><strong>Total: <?= number_format($total, 2, ',', '.') ?> €</strong></p>

        <!-- Formulario Finalizar/Vaciar -->
        <form method="post">
            <?php foreach ($productos as $p): ?>
                <input type="hidden" name="observaciones[<?= $p['producto_id'] ?>]" value="<?= htmlspecialchars($observaciones_form[$p['producto_id']] ?? '', ENT_QUOTES) ?>">
            <?php endforeach; ?>
            <div class="d-flex gap-2">
                <button type="submit" name="vaciar" class="btn btn-warning">Vaciar Carrito</button>
                <button type="submit" name="finalizar" class="btn btn-success">Finalizar Compra</button>
                <a href="index.php?view=productos" class="btn btn-info">Seguir Comprando</a>
            </div>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
