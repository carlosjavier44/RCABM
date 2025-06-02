<?php
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    echo "<p>No tienes permisos para acceder a esta página.</p>";
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>ID de pedido inválido.</p>";
    exit;
}

$pedido_id = (int)$_GET['id'];

// Obtener info del pedido con datos usuario (nombre, email)
$stmt = $conn->prepare("
    SELECT p.*, u.nombre AS usuario_nombre, u.email 
    FROM pedidos p 
    JOIN usuarios u ON p.usuario_id = u.id 
    WHERE p.id = ?
");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result = $stmt->get_result();
$pedido = $result->fetch_assoc();
$stmt->close();

if (!$pedido) {
    echo "<p>Pedido no encontrado.</p>";
    exit;
}

// Obtener detalles del pedido (productos)
$stmt = $conn->prepare("
    SELECT dp.cantidad, dp.subtotal, pr.nombre 
    FROM detalles_pedido dp
    JOIN productos pr ON dp.producto_id = pr.id
    WHERE dp.pedido_id = ?
");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$result_detalle = $stmt->get_result();
$detalle_productos = $result_detalle->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Detalle técnico del Pedido #<?= $pedido_id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h2>Detalle técnico del Pedido #<?= $pedido_id ?></h2>
    <a href="index.php?view=admin_pedidos" class="btn btn-secondary mb-3">Volver a lista de pedidos</a>

    <h4>Datos del usuario</h4>
    <ul>
        <li><strong>Nombre:</strong> <?= htmlspecialchars($pedido['usuario_nombre']) ?></li>
        <li><strong>Email:</strong> <?= htmlspecialchars($pedido['email']) ?></li>
    </ul>

    <h4>Datos del pedido</h4>
    <ul>
        <li><strong>Fecha:</strong> <?= $pedido['fecha'] ?></li>
        <li><strong>Estado:</strong> <?= ucfirst($pedido['estado']) ?></li>
        <li><strong>Total:</strong> <?= number_format($pedido['total'], 2, ',', '.') ?> €</li>
    </ul>

    <h4>Productos del pedido</h4>
    <?php if (empty($detalle_productos)): ?>
        <p>Este pedido no tiene productos.</p>
    <?php else: ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Nombre producto</th>
                    <th>Cantidad</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalle_productos as $producto): ?>
                    <tr>
                        <td><?= htmlspecialchars($producto['nombre']) ?></td>
                        <td><?= $producto['cantidad'] ?></td>
                        <td><?= number_format($producto['subtotal'], 2, ',', '.') ?> €</td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
