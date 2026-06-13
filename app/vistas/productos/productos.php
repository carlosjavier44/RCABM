<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/modelos/Producto.php';

if (session_status() == PHP_SESSION_NONE) session_start();
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);

$productoModel = new Producto($conn);
$termino   = isset($_GET['q'])        ? trim($_GET['q'])        : '';
$orden     = isset($_GET['orden'])    ? $_GET['orden']          : '';
$categoria = isset($_GET['categoria'])? $_GET['categoria']      : '';
$productos = $productoModel->obtenerProductos($termino, $categoria, $orden);
?>

<section class="products-section">
  <div class="section-header">
    <h2 class="section-title">
      <?php
      if ($categoria) echo htmlspecialchars($categoria);
      elseif ($termino) echo 'Resultados para "' . htmlspecialchars($termino) . '"';
      else echo 'Todos los productos';
      ?>
    </h2>

    <form method="get" class="sort-control">
      <input type="hidden" name="view" value="productos">
      <?php if ($termino):   ?><input type="hidden" name="q"        value="<?= htmlspecialchars($termino) ?>"><?php endif; ?>
      <?php if ($categoria): ?><input type="hidden" name="categoria" value="<?= htmlspecialchars($categoria) ?>"><?php endif; ?>
      <label for="orden">Ordenar:</label>
      <select name="orden" id="orden" onchange="this.form.submit()">
        <option value="">Relevancia</option>
        <option value="precio_asc"  <?= $orden==='precio_asc'  ?'selected':'' ?>>Precio: menor a mayor</option>
        <option value="precio_desc" <?= $orden==='precio_desc' ?'selected':'' ?>>Precio: mayor a menor</option>
        <option value="nuevos"      <?= $orden==='nuevos'      ?'selected':'' ?>>Más recientes</option>
        <option value="antiguos"    <?= $orden==='antiguos'    ?'selected':'' ?>>Más antiguos</option>
      </select>
    </form>
  </div>

  <?php if (!empty($productos)): ?>
    <div class="products-grid">
      <?php foreach ($productos as $p): ?>
        <a href="javascript:void(0)" onclick="loadView('detalle', <?= (int)$p['id'] ?>)" class="product-card">
          <div class="product-card-img">
            <img src="<?= $p['imagen'] ? htmlspecialchars($p['imagen']) : 'public/img/default-producto.png' ?>"
                 alt="<?= htmlspecialchars($p['nombre']) ?>">
          </div>
          <div class="product-card-body">
            <span class="product-category"><?= htmlspecialchars($p['categoria']) ?></span>
            <h3 class="product-name"><?= htmlspecialchars($p['nombre']) ?></h3>
            <p class="product-desc"><?= htmlspecialchars($p['descripcion']) ?></p>
            <div class="product-footer">
              <span class="product-price"><?= number_format($p['precio'],2,',','.') ?> €</span>
              <span class="btn-card">Ver más</span>
            </div>
          </div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-state">
      <i class="fas fa-search"></i>
      <p>No encontramos productos<?= $termino ? ' para "'.htmlspecialchars($termino).'"' : '' ?>.</p>
      <button class="btn-secondary" onclick="loadView('productos')">Ver todos</button>
    </div>
  <?php endif; ?>
</section>

<?php $conn->close(); ?>
