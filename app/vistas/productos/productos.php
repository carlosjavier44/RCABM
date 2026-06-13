<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/modelos/Producto.php';

$productoModel = new Producto($conn);
$termino   = trim($_GET['q'] ?? '');
$orden     = $_GET['orden'] ?? '';
$categoria = $_GET['categoria'] ?? '';
$productos = $productoModel->obtenerProductos($termino, $categoria, $orden);
$loggedIn  = isset($_SESSION['usuario']);
?>

<!-- Popup personalización dinámico -->
<div id="popup-personalizacion" style="display:none;position:fixed;inset:0;background:rgba(44,31,26,0.45);z-index:999;align-items:center;justify-content:center;padding:1rem">
  <div style="background:var(--white);border-radius:var(--radius-lg);padding:2rem 1.5rem;max-width:440px;width:100%;box-shadow:var(--shadow-card);max-height:90vh;overflow-y:auto">
    <h3 style="font-family:var(--font-display);font-style:italic;margin-bottom:0.25rem" id="popup-titulo">Personaliza tu producto</h3>
    <p style="font-size:0.85rem;color:var(--ink-soft);margin-bottom:1.25rem" id="popup-desc"></p>
    <div id="popup-campos"></div>
    <p id="popup-error" style="color:var(--rose);font-size:0.8rem;margin-top:0.5rem;display:none">
      <i class="fas fa-exclamation-circle"></i> Por favor rellena todos los campos antes de continuar.
    </p>
    <div style="display:flex;gap:0.75rem;margin-top:1.25rem;flex-wrap:wrap">
      <button class="btn-primary" id="popup-confirmar"><i class="fas fa-shopping-bag"></i> Añadir a la cesta</button>
      <button class="btn-secondary" onclick="cerrarPopup()">Cancelar</button>
    </div>
  </div>
</div>

<!-- Toast -->
<div id="cart-toast" style="display:none;position:fixed;bottom:5rem;left:50%;transform:translateX(-50%);background:var(--ink);color:white;padding:0.7rem 1.4rem;border-radius:99px;font-size:0.85rem;z-index:500;box-shadow:0 4px 16px rgba(0,0,0,0.2);align-items:center;gap:0.5rem;white-space:nowrap">
  <i class="fas fa-check-circle" style="color:#A8E6CF"></i><span id="cart-toast-msg"></span>
</div>

<section class="products-section">
  <div class="section-header">
    <h2 class="section-title">
      <?php
        if ($categoria)   echo htmlspecialchars($categoria);
        elseif ($termino) echo 'Resultados para "' . htmlspecialchars($termino) . '"';
        else              echo 'Todos los productos';
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
        <div class="product-card">
          <a href="index.php?view=detalle&id=<?= (int)$p['id'] ?>" class="product-card-img">
            <img src="<?= $p['imagen'] ? htmlspecialchars($p['imagen']) : 'public/img/default-producto.png' ?>"
                 alt="<?= htmlspecialchars($p['nombre']) ?>">
          </a>
          <div class="product-card-body">
            <span class="product-category">
              <?= htmlspecialchars($p['categoria']) ?>
              <?php if (!empty($p['personalizable'])): ?>
                <span style="color:var(--gold)" title="Personalizable">✏️</span>
              <?php endif; ?>
            </span>
            <a href="index.php?view=detalle&id=<?= (int)$p['id'] ?>" class="product-name" style="text-decoration:none;color:inherit">
              <?= htmlspecialchars($p['nombre']) ?>
            </a>
            <p class="product-desc"><?= htmlspecialchars($p['descripcion']) ?></p>
            <div class="product-footer">
              <span class="product-price"><?= number_format($p['precio'],2,',','.') ?> €</span>
              <div style="display:flex;gap:0.4rem">
                <a href="index.php?view=detalle&id=<?= (int)$p['id'] ?>" class="btn-card">Ver más</a>
                <?php if ($loggedIn): ?>
                  <button class="btn-card btn-add-cart"
                          style="background:var(--rose);color:white;border-color:var(--rose)"
                          data-id="<?= (int)$p['id'] ?>"
                          data-nombre="<?= htmlspecialchars($p['nombre'], ENT_QUOTES) ?>"
                          data-personalizable="<?= !empty($p['personalizable']) ? '1' : '0' ?>"
                          data-campos="<?= htmlspecialchars($p['campos_personalizacion'] ?? '[]', ENT_QUOTES) ?>"
                          title="Añadir a la cesta">
                    <i class="fas fa-shopping-bag"></i>
                  </button>
                <?php else: ?>
                  <a href="index.php?view=login" class="btn-card" style="background:var(--gold-pale);color:var(--ink-soft);border-color:var(--gold)" title="Inicia sesión">
                    <i class="fas fa-lock"></i>
                  </a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="empty-state">
      <i class="fas fa-search"></i>
      <p>No encontramos productos<?= $termino ? ' para "'.htmlspecialchars($termino).'"' : '' ?>.</p>
      <a href="index.php?view=productos" class="btn-secondary">Ver todos</a>
    </div>
  <?php endif; ?>
</section>

<script>
(function(){
  const popup    = document.getElementById('popup-personalizacion');
  const toast    = document.getElementById('cart-toast');
  const toastMsg = document.getElementById('cart-toast-msg');
  let pendingId, pendingNombre, pendingBtn, toastTimer;

  function showToast(msg){
    toastMsg.textContent = msg;
    toast.style.display = 'flex';
    clearTimeout(toastTimer);
    toastTimer = setTimeout(()=>{ toast.style.display='none'; }, 2800);
  }

  window.cerrarPopup = function(){
    popup.style.display = 'none';
    if (pendingBtn){ pendingBtn.disabled=false; pendingBtn.querySelector('i').className='fas fa-shopping-bag'; }
  };

  popup.addEventListener('click', e=>{ if(e.target===popup) cerrarPopup(); });

  async function addToCart(id, nombre, obs, btn){
    const fd = new FormData();
    fd.append('accion','añadirProducto');
    fd.append('producto_id', id);
    fd.append('cantidad', 1);
    fd.append('observacion', obs);
    try {
      const r    = await fetch('/RCABM/app/controladores/controladorCarrito.php',{method:'POST',body:fd});
      const data = await r.json();
      if(data.success){
        if(btn){ btn.querySelector('i').className='fas fa-check'; setTimeout(()=>{ btn.querySelector('i').className='fas fa-shopping-bag'; btn.disabled=false; },1500); }
        showToast('✓ '+nombre+' añadido a la cesta');
      } else {
        if(btn){ btn.querySelector('i').className='fas fa-shopping-bag'; btn.disabled=false; }
        showToast('Error al añadir. Inténtalo de nuevo.');
      }
    } catch(e){
      if(btn){ btn.querySelector('i').className='fas fa-shopping-bag'; btn.disabled=false; }
    }
  }

  // Confirmar popup
  document.getElementById('popup-confirmar').addEventListener('click', function(){
    const err = document.getElementById('popup-error');
    const inputs = document.querySelectorAll('#popup-campos input, #popup-campos textarea');
    let allOk = true;
    inputs.forEach(i=>{ if(!i.value.trim()) allOk=false; });
    if(!allOk){ err.style.display='block'; return; }
    err.style.display='none';
    popup.style.display='none';
    // Construir observación con etiquetas
    let obs = '';
    inputs.forEach(i=>{ obs += i.dataset.label+': '+i.value.trim()+'\n'; });
    addToCart(pendingId, pendingNombre, obs.trim(), pendingBtn);
  });

  // Botones añadir
  document.querySelectorAll('.btn-add-cart').forEach(btn=>{
    btn.addEventListener('click', function(){
      const id    = this.dataset.id;
      const nombre = this.dataset.nombre;
      const pers  = this.dataset.personalizable === '1';
      const campos = JSON.parse(this.dataset.campos || '[]');

      this.disabled = true;
      this.querySelector('i').className = 'fas fa-spinner fa-spin';

      if(pers){
        pendingId = id; pendingNombre = nombre; pendingBtn = this;
        document.getElementById('popup-desc').textContent = '"'+nombre+'"';
        document.getElementById('popup-error').style.display = 'none';

        // Construir campos dinámicos
        const contenedor = document.getElementById('popup-campos');
        contenedor.innerHTML = '';

        if(campos.length > 0){
          campos.forEach(campo=>{
            const div = document.createElement('div');
            div.className = 'form-group';
            div.innerHTML = `
              <label class="form-label">${campo} <span style="color:var(--rose)">*</span></label>
              <input class="form-input" type="text" data-label="${campo}" placeholder="Escribe ${campo.toLowerCase()}…">`;
            contenedor.appendChild(div);
          });
        } else {
          // Sin campos definidos → textarea genérico
          contenedor.innerHTML = `
            <div class="form-group">
              <label class="form-label">Personalización <span style="color:var(--rose)">*</span></label>
              <textarea class="form-input" data-label="Personalización" placeholder="Indica cómo quieres personalizarlo…" style="height:80px;resize:vertical"></textarea>
            </div>`;
        }

        popup.style.display = 'flex';
        setTimeout(()=>{ const f=contenedor.querySelector('input,textarea'); if(f) f.focus(); },100);
      } else {
        addToCart(id, nombre, '', this);
      }
    });
  });
})();
</script>

<?php $conn->close(); ?>
