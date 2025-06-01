<?php
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['usuario'])) {
    echo "<p>Debes iniciar sesión para ver tus pedidos.</p>";
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];

$stmt = $conn->prepare("SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY fecha DESC");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$resultado = $stmt->get_result();
$pedidos = $resultado->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h2>Mis pedidos</h2>

    <?php if (empty($pedidos)): ?>
        <p>No has realizado ningún pedido todavía.</p>
    <?php else: ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td><?= $pedido['id'] ?></td>
                        <td><?= $pedido['fecha'] ?></td>
                        <td><?= number_format($pedido['total'], 2, ',', '.') ?> €</td>
                        <td><?= ucfirst($pedido['estado']) ?></td>
                        <td>
                            <a href="index.php?view=detalle_pedido&id=<?= $pedido['id'] ?>" class="btn btn-sm btn-info">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
