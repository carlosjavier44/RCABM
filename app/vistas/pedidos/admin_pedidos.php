<?php
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    echo "<p>No tienes permisos para acceder a esta página.</p>";
    exit;
}

// Si se envió un cambio de estado por POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pedido_id'], $_POST['nuevo_estado'])) {
    $pedido_id = (int)$_POST['pedido_id'];
    $nuevo_estado = $_POST['nuevo_estado'];

    $estados_validos = ['en espera', 'en proceso', 'en envío'];

    if (in_array($nuevo_estado, $estados_validos)) {
        $stmt = $conn->prepare("UPDATE pedidos SET estado = ? WHERE id = ?");
        $stmt->bind_param("si", $nuevo_estado, $pedido_id);
        $stmt->execute();
        $stmt->close();
        $mensaje = "Estado del pedido #$pedido_id actualizado a '$nuevo_estado'.";
    } else {
        $mensaje = "Estado inválido.";
    }
}

// Obtener todos los pedidos con info usuario
$query = "
    SELECT p.*, u.nombre AS usuario_nombre, u.email 
    FROM pedidos p 
    JOIN usuarios u ON p.usuario_id = u.id
    ORDER BY p.fecha DESC
";

$resultado = $conn->query($query);
$pedidos = $resultado->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <title>Gestión de Pedidos - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h2>Gestión de Pedidos (Admin)</h2>

    <?php if (!empty($mensaje)): ?>
        <div class="alert alert-info"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <?php if (empty($pedidos)): ?>
        <p>No hay pedidos para mostrar.</p>
    <?php else: ?>
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Usuario</th>
                    <th>Email</th>
                    <th>Fecha</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pedidos as $pedido): ?>
                    <tr>
                        <td><?= $pedido['id'] ?></td>
                        <td><?= htmlspecialchars($pedido['usuario_nombre']) ?></td>
                        <td><?= htmlspecialchars($pedido['email']) ?></td>
                        <td><?= $pedido['fecha'] ?></td>
                        <td><?= number_format($pedido['total'], 2, ',', '.') ?> €</td>
                        <td><?= ucfirst($pedido['estado']) ?></td>
                        <td>
                            <form method="post" class="d-inline-flex align-items-center" style="gap:0.3rem;">
                                <input type="hidden" name="pedido_id" value="<?= $pedido['id'] ?>">
                                <select name="nuevo_estado" class="form-select form-select-sm" required>
                                    <?php
                                    $estados = ['en espera', 'en proceso', 'en envío'];
                                    foreach ($estados as $estado):
                                    ?>
                                        <option value="<?= $estado ?>" <?= $pedido['estado'] === $estado ? 'selected' : '' ?>>
                                            <?= ucfirst($estado) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn btn-sm btn-primary">Actualizar</button>
                            </form>
                            <a href="index.php?view=detalle_admin&id=<?= $pedido['id'] ?>" class="btn btn-sm btn-info">Ver</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
