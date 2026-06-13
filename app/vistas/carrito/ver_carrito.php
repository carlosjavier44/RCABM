<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/modelos/Carrito.php';

if (!isset($_SESSION['usuario'])) {
    echo '<div class="auth-notice" style="max-width:600px;margin:4rem auto"><i class="fas fa-lock" style="color:var(--gold)"></i><span><a href="index.php?view=login" style="color:var(--rose);font-weight:500">Inicia sesión</a> para ver tu cesta.</span></div>';
    exit;
}

$usuario_id = $_SESSION['usuario']['id'];
$carritoObj = new Carrito($conn);

// Finalizar compra (POST normal)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar'])) {
    $productos = $carritoObj->obtenerProductos($usuario_id);
    if (!empty($productos)) {
        $total = array_sum(array_map(fn($p) => $p['precio'] * $p['cantidad'], $productos));
        $stmt  = $conn->prepare("INSERT INTO pedidos (usuario_id, total) VALUES (?, ?)");
        $stmt->bind_param("id", $usuario_id, $total); $stmt->execute();
        $pedido_id = $stmt->insert_id; $stmt->close();

        $stmt2 = $conn->prepare("INSERT INTO detalles_pedido (pedido_id, producto_id, cantidad, subtotal, observaciones) VALUES (?, ?, ?, ?, ?)");
        foreach ($productos as $p) {
            $sub = $p['precio'] * $p['cantidad'];
            $obs = $p['observacion'] ?? '';
            $stmt2->bind_param("iiids", $pedido_id, $p['producto_id'], $p['cantidad'], $sub, $obs);
            $stmt2->execute();
        }
        $stmt2->close();
        $carritoObj->vaciarCarrito($usuario_id);
        $_SESSION['mensaje_exito'] = "¡Pedido #$pedido_id realizado con éxito!";
        echo '<script>window.location.href="index.php?view=pedidos"</script>'; exit;
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
      <a href="index.php?view=productos" class="btn-primary"><i class="fas fa-store"></i> Explorar productos</a>
    </div>
  <?php else: ?>
    <?php $total = 0; ?>
    <div id="cart-items">
    <?php foreach ($productos as $p):
      $subtotal = $p['precio'] * $p['cantidad'];
      $total   += $subtotal;
      $pid      = $p['producto_id'];
    ?>
      <div class="cart-item" data-id="<?= $pid ?>" data-precio="<?= $p['precio'] ?>">
        <img class="cart-item-img"
             src="<?= $p['imagen'] ? htmlspecialchars($p['imagen']) : 'public/img/default-producto.png' ?>"
             alt="<?= htmlspecialchars($p['nombre']) ?>">
        <div class="cart-item-info">
          <h4><?= htmlspecialchars($p['nombre']) ?></h4>
          <span><?= number_format($p['precio'],2,',','.') ?> € / ud.</span>
          <?php if (!empty($p['personalizable'])): ?>
            <div class="cart-obs">
              <input type="text" class="obs-input" data-id="<?= $pid ?>"
                     value="<?= htmlspecialchars($p['observacion'] ?? '', ENT_QUOTES) ?>"
                     placeholder="✏️ Personalización…">
            </div>
          <?php elseif (!empty($p['observacion'])): ?>
            <div class="cart-obs">
              <input type="text" class="obs-input" data-id="<?= $pid ?>"
                     value="<?= htmlspecialchars($p['observacion'], ENT_QUOTES) ?>"
                     placeholder="Nota…">
            </div>
          <?php endif; ?>
        </div>
        <div class="cart-item-qty">
          <input type="number" class="qty-input" data-id="<?= $pid ?>" value="<?= $p['cantidad'] ?>" min="1">
        </div>
        <div style="display:flex;flex-direction:column;gap:0.5rem;align-items:flex-end">
          <span class="cart-item-price" data-id="<?= $pid ?>"><?= number_format($subtotal,2,',','.') ?> €</span>
          <button class="btn-danger btn-eliminar" data-id="<?= $pid ?>" style="padding:0.4rem 0.9rem;font-size:0.78rem">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>
    <?php endforeach; ?>
    </div>

    <div class="cart-summary">
      <div class="cart-total">Total: <span id="cart-total"><?= number_format($total,2,',','.') ?> €</span></div>
      <div class="cart-actions">
        <button class="btn-secondary" id="btn-vaciar"><i class="fas fa-times"></i> Vaciar cesta</button>
        <a href="index.php?view=productos" class="btn-secondary"><i class="fas fa-arrow-left"></i> Seguir comprando</a>
        <form method="post" style="margin:0">
          <button type="submit" name="finalizar" class="btn-primary"><i class="fas fa-check"></i> Finalizar pedido</button>
        </form>
      </div>
    </div>
  <?php endif; ?>
</section>

<!-- Toast -->
<div id="cart-toast" style="display:none;position:fixed;bottom:5rem;left:50%;transform:translateX(-50%);background:var(--ink);color:white;padding:0.6rem 1.2rem;border-radius:99px;font-size:0.82rem;z-index:500;box-shadow:0 4px 16px rgba(0,0,0,0.2);align-items:center;gap:0.5rem">
  <i class="fas fa-check-circle" style="color:#A8E6CF"></i><span id="cart-toast-msg"></span>
</div>

<script>
(function(){
  const API = '/RCABM/app/controladores/controladorCarrito.php';
  const toast = document.getElementById('cart-toast');
  const toastMsg = document.getElementById('cart-toast-msg');
  let toastTimer, saveTimer;

  function showToast(msg){
    toastMsg.textContent = msg;
    toast.style.display = 'flex';
    clearTimeout(toastTimer);
    toastTimer = setTimeout(()=>{ toast.style.display='none'; }, 1800);
  }

  function fmt(n){ return n.toFixed(2).replace('.', ',') + ' €'; }

  function recalcTotal(){
    let total = 0;
    document.querySelectorAll('.cart-item').forEach(item=>{
      const precio = parseFloat(item.dataset.precio);
      const qty    = parseInt(item.querySelector('.qty-input').value) || 1;
      const sub    = precio * qty;
      item.querySelector('.cart-item-price').textContent = fmt(sub);
      total += sub;
    });
    document.getElementById('cart-total').textContent = fmt(total);
  }

  // Cambio de cantidad → guarda automáticamente
  document.querySelectorAll('.qty-input').forEach(inp=>{
    inp.addEventListener('change', async function(){
      const id  = this.dataset.id;
      let cant  = parseInt(this.value) || 1;
      if (cant < 1) { cant = 1; this.value = 1; }
      recalcTotal();
      const fd = new FormData();
      fd.append('accion','actualizarCantidad');
      fd.append('producto_id', id);
      fd.append('cantidad', cant);
      await fetch(API, {method:'POST', body:fd});
      showToast('Cantidad actualizada');
    });
  });

  // Cambio de observación → guarda automáticamente (con debounce)
  document.querySelectorAll('.obs-input').forEach(inp=>{
    inp.addEventListener('input', function(){
      const id = this.dataset.id;
      const val = this.value;
      clearTimeout(saveTimer);
      saveTimer = setTimeout(async ()=>{
        const fd = new FormData();
        fd.append('accion','actualizarObservacion');
        fd.append('producto_id', id);
        fd.append('observacion', val);
        await fetch(API, {method:'POST', body:fd});
        showToast('Personalización guardada');
      }, 800);
    });
  });

  // Eliminar
  document.querySelectorAll('.btn-eliminar').forEach(btn=>{
    btn.addEventListener('click', async function(){
      const id = this.dataset.id;
      const fd = new FormData();
      fd.append('accion','eliminarProducto');
      fd.append('producto_id', id);
      await fetch(API, {method:'POST', body:fd});
      this.closest('.cart-item').remove();
      recalcTotal();
      showToast('Producto eliminado');
      if (!document.querySelectorAll('.cart-item').length) location.reload();
    });
  });

  // Vaciar
  document.getElementById('btn-vaciar')?.addEventListener('click', async function(){
    if (!confirm('¿Vaciar toda la cesta?')) return;
    const fd = new FormData();
    fd.append('accion','vaciarCarrito');
    await fetch(API, {method:'POST', body:fd});
    location.reload();
  });
})();
</script>

<?php $conn->close(); ?>
