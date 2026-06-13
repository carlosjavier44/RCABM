<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/modelos/Carrito.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p class='alert alert-danger'>Producto no válido.</p>"; exit;
}

$id   = (int) $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM productos WHERE id = ?");
$stmt->bind_param("i", $id); $stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();

if (!$producto) {
    echo "<div class='empty-state'><i class='fas fa-box-open'></i><p>Producto no encontrado.</p></div>"; exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['añadir_carrito'], $_SESSION['usuario'])) {
    $carrito = new Carrito($conn);
    $obs = trim($_POST['observacion'] ?? '');
    $carrito->añadirProducto($_SESSION['usuario']['id'], (int)$_POST['producto_id'], 1, $obs);
    $_SESSION['mensaje_carrito'] = 'Producto añadido a la cesta';
    echo '<script>window.location.href="/RCABM/index.php?view=detalle&id=' . $id . '"</script>'; exit;
}

$personalizable = !empty($producto['personalizable']);
$campos = [];
if ($personalizable && !empty($producto['campos_personalizacion'])) {
    $campos = json_decode($producto['campos_personalizacion'], true) ?? [];
}
?>

<section class="detail-section">
  <nav class="breadcrumb">
    <a href="index.php?view=productos">Tienda</a>
    <span>›</span>
    <span><?= htmlspecialchars($producto['categoria']) ?></span>
    <span>›</span>
    <span><?= htmlspecialchars($producto['nombre']) ?></span>
  </nav>

  <div class="detail-grid">
    <div class="detail-img-wrap">
      <img src="<?= $producto['imagen'] ? htmlspecialchars($producto['imagen']) : 'public/img/default-producto.png' ?>"
           alt="<?= htmlspecialchars($producto['nombre']) ?>">
    </div>

    <div class="detail-info">
      <span class="detail-category">
        <?= htmlspecialchars($producto['categoria']) ?>
        <?php if ($personalizable): ?>
          <span style="color:var(--gold);margin-left:6px">✏️ Personalizable</span>
        <?php endif; ?>
      </span>
      <h1 class="detail-name"><?= htmlspecialchars($producto['nombre']) ?></h1>
      <div class="detail-price"><?= number_format($producto['precio'],2,',','.') ?> €</div>
      <p class="detail-desc"><?= htmlspecialchars($producto['descripcion']) ?></p>

      <?php if (isset($_SESSION['usuario'])): ?>
        <?php if (isset($_SESSION['mensaje_carrito'])): ?>
          <div class="alert alert-success"><i class="fas fa-check"></i> <?= $_SESSION['mensaje_carrito'] ?></div>
          <?php unset($_SESSION['mensaje_carrito']); ?>
        <?php endif; ?>

        <form method="post" id="form-detalle">
          <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
          <input type="hidden" name="añadir_carrito" value="1">
          <input type="hidden" name="observacion" id="obs-hidden">

          <?php if ($personalizable): ?>
            <div style="background:var(--gold-pale);border:1px solid var(--gold);border-radius:var(--radius-md);padding:1rem;margin-bottom:1rem">
              <p style="font-size:0.82rem;font-weight:500;color:var(--ink);margin-bottom:0.75rem">
                <i class="fas fa-pencil-alt" style="color:var(--gold)"></i> Personalización
              </p>
              <?php if (!empty($campos)): ?>
                <?php foreach ($campos as $campo): ?>
                  <div class="form-group">
                    <label class="form-label"><?= htmlspecialchars($campo) ?> <span style="color:var(--rose)">*</span></label>
                    <input class="form-input campo-pers" type="text"
                           data-label="<?= htmlspecialchars($campo, ENT_QUOTES) ?>"
                           placeholder="Escribe <?= htmlspecialchars(strtolower($campo)) ?>…">
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="form-group">
                  <label class="form-label">Tu personalización <span style="color:var(--rose)">*</span></label>
                  <textarea class="form-input campo-pers" data-label="Personalización"
                            placeholder="Indica cómo quieres personalizar este artículo…"
                            style="height:80px;resize:vertical"></textarea>
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <div style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center">
            <button type="submit" class="btn-primary" id="btn-detalle-add">
              <i class="fas fa-shopping-bag"></i> Añadir a la cesta
            </button>
            <a href="index.php?view=carrito" class="btn-secondary">Ver cesta</a>
          </div>
        </form>

      <?php else: ?>
        <div class="auth-notice">
          <i class="fas fa-lock" style="color:var(--gold)"></i>
          <span><a href="index.php?view=login" style="color:var(--rose);font-weight:500">Inicia sesión</a> para añadir a la cesta.</span>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php if ($personalizable && isset($_SESSION['usuario'])): ?>
<script>
document.getElementById('form-detalle').addEventListener('submit', function(e){
  const campos = document.querySelectorAll('.campo-pers');
  let allOk = true;
  campos.forEach(c=>{ if(!c.value.trim()) allOk=false; });
  if(!allOk){
    e.preventDefault();
    campos.forEach(c=>{
      if(!c.value.trim()){
        c.style.borderColor='var(--rose)';
        c.style.boxShadow='0 0 0 3px rgba(196,134,122,0.2)';
      } else {
        c.style.borderColor='';
        c.style.boxShadow='';
      }
    });
    return;
  }
  // Construir observación con etiquetas
  let obs = '';
  campos.forEach(c=>{ obs += c.dataset.label+': '+c.value.trim()+'\n'; });
  document.getElementById('obs-hidden').value = obs.trim();
});
</script>
<?php endif; ?>

<?php $conn->close(); ?>
