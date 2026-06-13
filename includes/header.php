<?php
if (session_status() == PHP_SESSION_NONE) session_start();
$esAdmin  = isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'admin';
$loggedIn = isset($_SESSION['usuario']);
$nombre   = $loggedIn ? htmlspecialchars($_SESSION['usuario']['nombre']) : '';
?>

<div id="site-header">
  <div class="header-top">
    <!-- Logo -->
    <a href="index.php" class="header-brand">
      <img src="public/img/logo.png" alt="Logo">
      <div>
        <div class="brand-name">Regalos con Amor</div>
        <div class="brand-tagline">by Moni · Regalos personalizados</div>
      </div>
    </a>

    <!-- Buscador desktop -->
    <form method="GET" action="index.php" class="header-search" id="header-search">
      <input type="hidden" name="view" value="productos">
      <i class="fas fa-search search-icon"></i>
      <input type="text" name="q" placeholder="Buscar regalos…" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>" id="search-input">
      <button type="submit">Buscar</button>
      <button type="button" class="btn-search-close" onclick="closeSearch()" style="display:none;background:none;border:none;color:var(--ink-soft);font-size:1.2rem;cursor:pointer;padding:0 0.5rem">✕</button>
    </form>

    <!-- Acciones -->
    <div class="header-actions">
      <!-- Lupa solo en móvil -->
      <button class="btn-search-toggle" onclick="openSearch()" title="Buscar" style="display:none">
        <i class="fas fa-search"></i>
      </button>

      <a href="index.php?view=carrito" class="header-btn">
        <i class="fas fa-shopping-bag"></i><span>Cesta</span>
      </a>

      <?php if ($loggedIn): ?>
        <a href="index.php?view=pedidos" class="header-btn">
          <i class="fas fa-box"></i><span>Pedidos</span>
        </a>
        <?php if ($esAdmin): ?>
          <a href="index.php?view=chat_admin" class="header-btn" style="position:relative">
            <i class="fas fa-comments"></i><span>Chats</span>
            <span id="badge-admin" style="display:none;position:absolute;top:2px;right:2px;background:var(--rose);color:white;border-radius:99px;font-size:0.6rem;font-weight:700;padding:1px 5px;min-width:16px;text-align:center"></span>
          </a>
        <?php else: ?>
          <a href="index.php?view=chat" class="header-btn" style="position:relative">
            <i class="fas fa-comment"></i><span>Escribir</span>
            <span id="badge-user" style="display:none;position:absolute;top:2px;right:2px;background:var(--rose);color:white;border-radius:99px;font-size:0.6rem;font-weight:700;padding:1px 5px;min-width:16px;text-align:center"></span>
          </a>
        <?php endif; ?>
      <?php endif; ?>

      <!-- Cuenta -->
      <div class="user-dropdown" id="userDropdown">
        <button class="header-btn" onclick="toggleDropdown(event)">
          <i class="fas fa-user-circle"></i>
          <span><?= $loggedIn ? explode(' ', $nombre)[0] : 'Cuenta' ?></span>
        </button>
        <div class="dropdown-panel" id="dropdownPanel">
          <?php if ($loggedIn): ?>
            <div class="user-greeting">Hola, <?= $nombre ?> 👋</div>
            <div class="divider"></div>
            <a href="index.php?view=pedidos">Mis pedidos</a>
            <a href="index.php?view=carrito">Mi cesta</a>
            <?php if ($esAdmin): ?>
              <div class="divider"></div>
              <a href="index.php?view=admin_productos">Gestionar productos</a>
              <a href="index.php?view=admin_pedidos">Gestionar pedidos</a>
              <a href="index.php?view=chat_admin">Chats de clientes</a>
            <?php endif; ?>
            <div class="divider"></div>
            <form method="POST" action="/RCABM/app/controladores/controladorUsuario.php">
              <input type="hidden" name="accion" value="logout">
              <button type="submit" class="danger">Cerrar sesión</button>
            </form>
          <?php else: ?>
            <a href="index.php?view=login">Iniciar sesión</a>
            <a href="index.php?view=register">Crear cuenta</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Nav categorías -->
  <nav id="site-nav">
    <div class="nav-inner">
      <a href="index.php?view=productos" class="btn-categoria">Todos</a>
      <span class="nav-sep">·</span>
      <a href="index.php?view=productos&categoria=San+Valentin">San Valentín</a>
      <span class="nav-sep">·</span>
      <a href="index.php?view=productos&categoria=Eventos">Eventos</a>
      <span class="nav-sep">·</span>
      <a href="index.php?view=productos&categoria=Navidad">Navidad</a>
      <span class="nav-sep">·</span>
      <a href="index.php?view=productos&categoria=Beb%C3%A9s">Bebés</a>
      <span class="nav-sep">·</span>
      <a href="index.php?view=productos&categoria=Ni%C3%B1os">Niños</a>
      <span class="nav-sep">·</span>
      <a href="index.php?view=productos&categoria=Hombre">Hombre</a>
      <span class="nav-sep">·</span>
      <a href="index.php?view=productos&categoria=Mujer">Mujer</a>
      <span class="nav-sep">·</span>
      <a href="index.php?view=productos&categoria=Unisex">Unisex</a>
      <span class="nav-sep">·</span>
      <a href="index.php?view=productos&categoria=L%C3%A1mparas">Lámparas</a>
    </div>
  </nav>
</div>

<script>
// Dropdown
function toggleDropdown(e) {
  e.stopPropagation();
  document.getElementById('userDropdown').classList.toggle('open');
}
document.addEventListener('click', function(e) {
  const dd = document.getElementById('userDropdown');
  if (dd && !dd.contains(e.target)) dd.classList.remove('open');
});

// Buscador móvil
function openSearch() {
  const s = document.getElementById('header-search');
  s.classList.add('open');
  s.querySelector('.btn-search-close').style.display = 'block';
  document.getElementById('search-input').focus();
}
function closeSearch() {
  document.getElementById('header-search').classList.remove('open');
}

// Mostrar lupa solo en móvil
function checkMobile() {
  const toggle = document.querySelector('.btn-search-toggle');
  const search  = document.getElementById('header-search');
  const close   = search ? search.querySelector('.btn-search-close') : null;
  if (window.innerWidth <= 768) {
    if (toggle) toggle.style.display = 'flex';
    if (search && !search.classList.contains('open')) search.style.display = 'none';
    if (close)  close.style.display = search && search.classList.contains('open') ? 'block' : 'none';
  } else {
    if (toggle) toggle.style.display = 'none';
    if (search) { search.style.display = 'flex'; search.classList.remove('open'); }
  }
}
checkMobile();
window.addEventListener('resize', checkMobile);

// Badges notificaciones
<?php if ($loggedIn && $esAdmin): ?>
(function poll(){
  fetch('/RCABM/app/controladores/controladorChatAdmin.php?total_no_leidos=1')
    .then(r=>r.json()).then(d=>{
      const b=document.getElementById('badge-admin');
      if(b){b.textContent=d.total;b.style.display=d.total>0?'inline-block':'none';}
    }).catch(()=>{});
  setTimeout(poll,5000);
})();
<?php elseif ($loggedIn): ?>
(function poll(){
  fetch('/RCABM/app/controladores/controladorChat.php?action=no_leidos')
    .then(r=>r.json()).then(d=>{
      const b=document.getElementById('badge-user');
      if(b){b.textContent=d.total;b.style.display=d.total>0?'inline-block':'none';}
    }).catch(()=>{});
  setTimeout(poll,5000);
})();
<?php endif; ?>
</script>
