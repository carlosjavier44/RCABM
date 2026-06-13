<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/modelos/Carrito.php';

if (session_status() == PHP_SESSION_NONE) session_start();

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<p class='alert alert-danger'>Producto no válido.</p>"; exit;
}

$id   = (int) $_GET['id'];
$sql  = "SELECT * FROM productos WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result  = $stmt->get_result();
$producto = $result->fetch_assoc();

if (!$producto) {
    echo "<div class='empty-state'><i class='fas fa-box-open'></i><p>Producto no encontrado.</p></div>"; exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['añadir_carrito'], $_SESSION['usuario'])) {
    $carrito = new Carrito($conn);
    $carrito->añadirProducto($_SESSION['usuario']['id'], (int)$_POST['producto_id'], 1);
    $_SESSION['mensaje_carrito'] = 'Producto añadido a la cesta';
    header("Location: /RCABM/index.php?view=detalle&id=" . $id);
    exit;
}
?>

<section class="detail-section">
  <!-- Breadcrumb -->
  <nav class="breadcrumb">
    <a href="javascript:void(0)" onclick="loadView('productos')">Tienda</a>
    <span>›</span>
    <span><?= htmlspecialchars($producto['categoria']) ?></span>
    <span>›</span>
    <span><?= htmlspecialchars($producto['nombre']) ?></span>
  </nav>

  <div class="detail-grid">
    <!-- Imagen -->
    <div class="detail-img-wrap">
      <img src="<?= $producto['imagen'] ? htmlspecialchars($producto['imagen']) : 'public/img/default-producto.png' ?>"
           alt="<?= htmlspecialchars($producto['nombre']) ?>">
    </div>

    <!-- Info -->
    <div class="detail-info">
      <span class="detail-category"><?= htmlspecialchars($producto['categoria']) ?></span>
      <h1 class="detail-name"><?= htmlspecialchars($producto['nombre']) ?></h1>
      <div class="detail-price"><?= number_format($producto['precio'],2,',','.') ?> €</div>
      <p class="detail-desc"><?= htmlspecialchars($producto['descripcion']) ?></p>

      <?php if (isset($_SESSION['usuario'])): ?>
        <?php if (isset($_SESSION['mensaje_carrito'])): ?>
          <div class="alert alert-success"><i class="fas fa-check"></i> <?= $_SESSION['mensaje_carrito'] ?></div>
          <?php unset($_SESSION['mensaje_carrito']); ?>
        <?php endif; ?>
        <form method="post" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;">
          <input type="hidden" name="producto_id" value="<?= $producto['id'] ?>">
          <button type="submit" name="añadir_carrito" class="btn-primary">
            <i class="fas fa-shopping-bag"></i> Añadir a la cesta
          </button>
          <button type="button" class="btn-secondary" onclick="loadView('carrito')">
            Ver cesta
          </button>
        </form>
      <?php else: ?>
        <div class="auth-notice">
          <i class="fas fa-lock" style="color:var(--gold)"></i>
          <span>
            <a href="javascript:void(0)" onclick="loadView('login')" style="color:var(--rose);font-weight:500">Inicia sesión</a>
            para añadir a la cesta.
          </span>
        </div>
      <?php endif; ?>
    </div>
  </div>
</section>

<?php $conn->close(); ?>
