<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/modelos/Producto.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    echo "<p style='color:red;text-align:center'>Acceso denegado.</p>"; exit;
}

$productoModel   = new Producto($conn);
$editando        = false;
$productoEditado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id             = $_POST['id'] ?? '';
    $nombre         = trim($_POST['nombre']);
    $descripcion    = trim($_POST['descripcion']);
    $precio         = floatval($_POST['precio']);
    $categoria      = trim($_POST['categoria']);
    $personalizable = isset($_POST['personalizable']) ? 1 : 0;

    // Campos de personalización → JSON
    $campos_raw = $_POST['campos'] ?? [];
    $campos     = json_encode(array_values(array_filter(array_map('trim', $campos_raw))));

    $imagen = '';
    if (!empty($_FILES['imagen']['name'])) {
        $ruta = 'public/img/' . basename($_FILES['imagen']['name']);
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], __DIR__ . '/../../../' . $ruta)) {
            $imagen = $ruta;
        }
    }

    if ($id) {
        $productoModel->actualizarProducto($id,$nombre,$descripcion,$precio,$categoria,$imagen,$personalizable,$campos);
    } else {
        $productoModel->agregarProducto($nombre,$descripcion,$precio,$categoria,$imagen,$personalizable,$campos);
    }
    echo '<script>window.location.href="index.php?view=admin_productos"</script>'; exit;
}

if (isset($_GET['editar'])) {
    $editando        = true;
    $productoEditado = $productoModel->obtenerProductoPorId((int)$_GET['editar']);
}

if (isset($_GET['eliminar'])) {
    $productoModel->eliminarProducto((int)$_GET['eliminar']);
    echo '<script>window.location.href="index.php?view=admin_productos"</script>'; exit;
}

$productos       = $productoModel->obtenerTodosLosProductos();
$campos_actuales = [];
if ($editando && !empty($productoEditado['campos_personalizacion'])) {
    $campos_actuales = json_decode($productoEditado['campos_personalizacion'], true) ?? [];
}
?>

<style>
.campos-editor { margin-top:1rem; }
.campo-row { display:flex; gap:0.5rem; align-items:center; margin-bottom:0.5rem; }
.campo-row input { flex:1; }
.btn-remove-campo { background:none; border:none; color:var(--rose); font-size:1.1rem; cursor:pointer; padding:0 0.3rem; }
#btn-add-campo { background:none; border:1.5px dashed var(--gold); color:var(--ink-soft); border-radius:var(--radius-sm); padding:0.45rem 0.9rem; font-size:0.82rem; cursor:pointer; font-family:var(--font-body); margin-top:0.3rem; transition:all var(--transition); }
#btn-add-campo:hover { background:var(--gold-pale); color:var(--ink); }
#campos-wrap { display:none; }
#campos-wrap.visible { display:block; }
</style>

<section class="admin-section">
  <h2 style="font-style:italic;margin-bottom:1.5rem"><?= $editando ? 'Editar producto' : 'Añadir nuevo producto' ?></h2>

  <form method="post" enctype="multipart/form-data" style="max-width:620px;background:var(--white);border:1px solid var(--border);border-radius:var(--radius-lg);padding:2rem;margin-bottom:3rem">
    <?php if ($editando): ?>
      <input type="hidden" name="id" value="<?= $productoEditado['id'] ?>">
    <?php endif; ?>

    <div class="form-group">
      <label class="form-label">Nombre</label>
      <input class="form-input" type="text" name="nombre" required value="<?= $editando ? htmlspecialchars($productoEditado['nombre']) : '' ?>">
    </div>

    <div class="form-group">
      <label class="form-label">Descripción</label>
      <textarea class="form-input" name="descripcion" required style="height:80px;resize:vertical"><?= $editando ? htmlspecialchars($productoEditado['descripcion']) : '' ?></textarea>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:1rem">
      <div class="form-group">
        <label class="form-label">Precio (€)</label>
        <input class="form-input" type="number" step="0.01" name="precio" required value="<?= $editando ? $productoEditado['precio'] : '' ?>">
      </div>
      <div class="form-group">
        <label class="form-label">Categoría</label>
        <input class="form-input" type="text" name="categoria" required value="<?= $editando ? htmlspecialchars($productoEditado['categoria']) : '' ?>">
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Imagen</label>
      <input class="form-input" type="file" name="imagen" accept="image/*">
      <?php if ($editando && !empty($productoEditado['imagen'])): ?>
        <img src="<?= htmlspecialchars($productoEditado['imagen']) ?>" style="width:80px;margin-top:0.5rem;border-radius:var(--radius-sm)">
      <?php endif; ?>
    </div>

    <!-- Personalizable -->
    <div class="form-group" style="background:var(--gold-pale);border:1px solid var(--gold);border-radius:var(--radius-sm);padding:0.85rem 1rem">
      <div style="display:flex;align-items:center;gap:0.75rem">
        <input type="checkbox" name="personalizable" id="personalizable" value="1"
               style="width:18px;height:18px;accent-color:var(--rose);cursor:pointer"
               <?= ($editando && !empty($productoEditado['personalizable'])) ? 'checked' : '' ?>>
        <label for="personalizable" style="cursor:pointer;font-size:0.88rem;color:var(--ink)">
          <strong>Producto personalizable</strong> — el cliente deberá rellenar campos antes de añadir a la cesta
        </label>
      </div>

      <!-- Editor de campos -->
      <div id="campos-wrap" class="campos-editor <?= (!empty($campos_actuales)) ? 'visible' : '' ?>">
        <p style="font-size:0.78rem;color:var(--ink-soft);margin:0.75rem 0 0.5rem">
          <i class="fas fa-info-circle" style="color:var(--gold)"></i>
          Añade los campos que el cliente debe rellenar (ej: Nombre, Color, Mensaje…)
        </p>
        <div id="campos-list">
          <?php if (!empty($campos_actuales)): ?>
            <?php foreach ($campos_actuales as $campo): ?>
              <div class="campo-row">
                <input class="form-input" type="text" name="campos[]" value="<?= htmlspecialchars($campo) ?>" placeholder="Ej: Nombre, Color, Mensaje…">
                <button type="button" class="btn-remove-campo" onclick="this.parentElement.remove()">✕</button>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
        <button type="button" id="btn-add-campo"><i class="fas fa-plus"></i> Añadir campo</button>
      </div>
    </div>

    <div style="display:flex;gap:0.75rem;margin-top:1.5rem">
      <button type="submit" class="btn-primary"><?= $editando ? 'Actualizar producto' : 'Añadir producto' ?></button>
      <?php if ($editando): ?>
        <a href="index.php?view=admin_productos" class="btn-secondary">Cancelar</a>
      <?php endif; ?>
    </div>
  </form>

  <h3 style="font-style:italic;margin-bottom:1rem">Listado de productos</h3>
  <table class="admin-table">
    <thead>
      <tr>
        <th>Imagen</th><th>Nombre</th><th>Precio</th><th>Categoría</th><th>Personalizable</th><th>Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($productos as $p): ?>
        <tr>
          <td><img src="<?= $p['imagen'] ? htmlspecialchars($p['imagen']) : 'public/img/default-producto.png' ?>" style="width:52px;height:52px;object-fit:cover;border-radius:var(--radius-sm)"></td>
          <td><?= htmlspecialchars($p['nombre']) ?></td>
          <td><?= number_format($p['precio'],2,',','.') ?> €</td>
          <td><?= htmlspecialchars($p['categoria']) ?></td>
          <td style="text-align:center">
            <?php if (!empty($p['personalizable'])): ?>
              <?php $c = json_decode($p['campos_personalizacion'] ?? '[]', true) ?? []; ?>
              <span style="color:var(--rose)">✏️</span>
              <?php if (!empty($c)): ?>
                <br><small style="color:var(--ink-soft);font-size:0.72rem"><?= implode(', ', array_map('htmlspecialchars', $c)) ?></small>
              <?php endif; ?>
            <?php else: ?>
              <span style="color:var(--ink-soft);font-size:0.8rem">—</span>
            <?php endif; ?>
          </td>
          <td>
            <div style="display:flex;gap:0.4rem">
              <a href="index.php?view=admin_productos&editar=<?= $p['id'] ?>" class="btn-secondary" style="padding:0.35rem 0.8rem;font-size:0.78rem">Editar</a>
              <a href="index.php?view=admin_productos&eliminar=<?= $p['id'] ?>" class="btn-danger" style="padding:0.35rem 0.8rem;font-size:0.78rem"
                 onclick="return confirm('¿Eliminar <?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>?')">Eliminar</a>
            </div>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>

<script>
const pers    = document.getElementById('personalizable');
const wrap    = document.getElementById('campos-wrap');
const lista   = document.getElementById('campos-list');
const btnAdd  = document.getElementById('btn-add-campo');

// Mostrar/ocultar editor al marcar checkbox
pers.addEventListener('change', function(){
  wrap.classList.toggle('visible', this.checked);
  if (this.checked && lista.children.length === 0) addCampo();
});

if (pers.checked) wrap.classList.add('visible');

function addCampo(val=''){
  const row = document.createElement('div');
  row.className = 'campo-row';
  row.innerHTML = `<input class="form-input" type="text" name="campos[]" value="${val}" placeholder="Ej: Nombre, Color, Mensaje…">
    <button type="button" class="btn-remove-campo" onclick="this.parentElement.remove()">✕</button>`;
  lista.appendChild(row);
  row.querySelector('input').focus();
}

btnAdd.addEventListener('click', () => addCampo());
</script>

<?php $conn->close(); ?>
