<?php
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['usuario'])) {
    echo "<p>Debes iniciar sesión para ver los detalles del pedido.</p>";
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p>Pedido no válido.</p>";
    exit;
}

$pedido_id = (int)$_GET['id'];
$usuario_id = $_SESSION['usuario']['id'];

$stmt = $conn->prepare("SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?");
$stmt->bind_param("ii", $pedido_id, $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {
    echo "<p>No se encontró el pedido.</p>";
    exit;
}

$pedido = $resultado->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("
    SELECT dp.*, p.nombre, p.precio 
    FROM detalles_pedido dp
    JOIN productos p ON dp.producto_id = p.id
    WHERE dp.pedido_id = ?
");
$stmt->bind_param("i", $pedido_id);
$stmt->execute();
$detalles = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle del Pedido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h2>Pedido #<?= $pedido['id'] ?></h2>
    <p><strong>Fecha:</strong> <?= $pedido['fecha'] ?></p>
    <p><strong>Estado:</strong> <?= ucfirst($pedido['estado']) ?></p>
    <p><strong>Total:</strong> <?= number_format($pedido['total'], 2, ',', '.') ?> €</p>

    <h4>Productos</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Producto</th>
                <th>Precio</th>
                <th>Cantidad</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalles as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['nombre']) ?></td>
                    <td><?= number_format($item['precio'], 2, ',', '.') ?> €</td>
                    <td><?= $item['cantidad'] ?></td>
                    <td><?= number_format($item['subtotal'], 2, ',', '.') ?> €</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="index.php?view=pedidos" class="btn btn-secondary">Volver</a>
</div>
</body>
</html>
