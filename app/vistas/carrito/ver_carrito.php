<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/modelos/Carrito.php';

if (session_status() == PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['usuario'])) {
    echo '<div class="auth-notice" style="max-width:600px;margin:4rem auto;"><i class="fas fa-lock" style="color:var(--gold)"></i><span><a href="javascript:void(0)" onclick="loadView(\'login\')" style="color:var(--rose);font-weight:500">Inicia sesión</a> para ver tu cesta.</span></div>';
    exit;
}

$usuario_id  = $_SESSION['usuario']['id'];
$carritoObj  = new Carrito($conn);
$obs_form    = $_SESSION['observaciones'] ?? [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['actualizar'])) {
        $carritoObj->actualizarCantidad($usuario_id, (int)$_POST['producto_id'], (int)$_POST['cantidad']);
        $_SESSION['observaciones'][(int)$_POST['producto_id']] = $_POST['observacion_actual'] ?? '';
        header("Location: index.php?view=carrito"); exit;
    }
    if (isset($_POST['eliminar'])) {
        $carritoObj->eliminarProducto($usuario_id, (int)$_POST['producto_id']);
        unset($_SESSION['observaciones'][(int)$_POST['producto_id']]);
        header("Location: index.php?view=carrito"); exit;
    }
    if (isset($_POST['vaciar'])) {
        $carritoObj->vaciarCarrito($usuario_id);
        unset($_SESSION['observaciones']);
        header("Location: index.php?view=carrito"); exit;
    }
    if (isset($_POST['finalizar'])) {
        $productos = $carritoObj->obtenerProductos($usuario_id);
        $obs_input = $_POST['observaciones'] ?? [];
        if (!empty($productos)) {
            $total = array_sum(array_map(fn($p) => $p['precio'] * $p['cantidad'], $productos));
            $stmt  = $conn->prepare("INSERT INTO pedidos (usuario_id, total) VALUES (?, ?)");
            $stmt->bind_param("id", $usuario_id, $total); $stmt->execute();
            $pedido_id = $stmt->insert_id; $stmt->close();
            $stmt2 = $conn->prepare("INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, subtotal, observaciones) VALUES (?, ?, ?, ?, ?)");
            foreach ($productos as $p) {
                $sub = $p['precio'] * $p['cantidad'];
                $obs = $obs_input[$p['producto_id']] ?? '';
                $stmt2->bind_param("iiids", $pedido_id, $p['producto_id'], $p['cantidad'], $sub, $obs);
                $stmt2->execute();
            }
            $stmt2->close();
            $carritoObj->vaciarCarrito($usuario_id);
            unset($_SESSION['observaciones']);
            $_SESSION['mensaje_exito'] = "¡Pedido #$pedido_id realizado con éxito!";
            header("Location: index.php?view=pedidos"); exit;
        }
    }
}

$productos = $carritoObj->obtenerProductos($usuario_id);
?>

<section class="cart-section">
  <h2>Mi cesta</h2>

  <?php if (isset($_SESSION['mensaje_exito'])): ?>
    <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $_SESSION['mensaje_exito'] ?></div>
    <?php unset($_SESSION['mensaje_exito']); ?>
  <?php endif; ?>

  <?php if (empty($productos)): ?>
    <div class="cart-empty">
      <i class="fas fa-shopping-bag"></i>
      <p>Tu cesta está vacía.</p>
      <button class="btn-primary" onclick="loadView('productos')"><i class="fas fa-store"></i> Explorar productos</button>
    </div>
  <?php else: ?>
    <?php $total = 0; ?>
    <?php foreach ($productos as $p):
      $subtotal    = $p['precio'] * $p['cantidad'];
      $total      += $subtotal;
      $product_id  = $p['producto_id'];
      $obs_val     = htmlspecialchars($obs_form[$product_id] ?? '', ENT_QUOTES);
    ?>
      <form method="post">
        <input type="hidden" name="producto_id" value="<?= $product_id ?>">
        <div class="cart-item">
          <img class="cart-item-img"
               src="<?= $p['imagen'] ?? 'public/img/default-producto.png' ?>"
               alt="<?= htmlspecialchars($p['nombre']) ?>">
          <div class="cart-item-info">
            <h4><?= htmlspecialchars($p['nombre']) ?></h4>
            <span><?= number_format($p['precio'],2,',','.') ?> € / ud.</span>
            <div class="cart-obs">
              <input type="text" name="observacion_actual" value="<?= $obs_val ?>"
                     placeholder="Personalización o nota…">
            </div>
          </div>
          <div class="cart-item-qty">
            <input type="number" name="cantidad" value="<?= $p['cantidad'] ?>" min="1">
          </div>
          <div style="display:flex;flex-direction:column;gap:0.5rem;align-items:flex-end">
            <span class="cart-item-price"><?= number_format($subtotal,2,',','.') ?> €</span>
            <div style="display:flex;gap:0.4rem">
              <button type="submit" name="actualizar" class="btn-secondary" style="padding:0.4rem 0.9rem;font-size:0.78rem">
                <i class="fas fa-sync-alt"></i> Actualizar
              </button>
              <button type="submit" name="eliminar" class="btn-danger" style="padding:0.4rem 0.9rem;font-size:0.78rem">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
        </div>
      </form>
    <?php endforeach; ?>

    <form method="post">
      <?php foreach ($productos as $p): ?>
        <input type="hidden" name="observaciones[<?= $p['producto_id'] ?>]" value="<?= htmlspecialchars($obs_form[$p['producto_id']] ?? '', ENT_QUOTES) ?>">
      <?php endforeach; ?>
      <div class="cart-summary">
        <div class="cart-total">Total: <span><?= number_format($total,2,',','.') ?> €</span></div>
        <div class="cart-actions">
          <button type="submit" name="vaciar" class="btn-secondary">
            <i class="fas fa-times"></i> Vaciar cesta
          </button>
          <button onclick="loadView('productos')" type="button" class="btn-secondary">
            <i class="fas fa-arrow-left"></i> Seguir comprando
          </button>
          <button type="submit" name="finalizar" class="btn-primary">
            <i class="fas fa-check"></i> Finalizar pedido
          </button>
        </div>
      </div>
    </form>
  <?php endif; ?>
</section>

<?php $conn->close(); ?>
