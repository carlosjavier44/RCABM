<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$esAdmin = isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'admin';
$loggedIn = isset($_SESSION['usuario']);
$nombre = $loggedIn ? htmlspecialchars($_SESSION['usuario']['nombre']) : '';
?>

<div id="site-header">
  <div class="header-top">
    <!-- Logo + nombre -->
    <a href="index.php" class="header-brand">
      <img src="public/img/logo.png" alt="Logo Regalos con Amor by Moni">
      <div>
        <div class="brand-name">Regalos con Amor</div>
        <div class="brand-tagline">by Moni · Regalos personalizados</div>
      </div>
    </a>

    <!-- Buscador -->
    <form method="GET" action="index.php" class="header-search">
      <input type="hidden" name="view" value="productos">
      <i class="fas fa-search search-icon"></i>
      <input type="text" name="q" placeholder="Buscar regalos…"
             value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
      <button type="submit">Buscar</button>
    </form>

    <!-- Acciones -->
    <div class="header-actions">
      <button class="header-btn" onclick="loadView('carrito')">
        <i class="fas fa-shopping-bag"></i>
        <span>Cesta</span>
      </button>

      <?php if ($loggedIn): ?>
        <button class="header-btn" onclick="loadView('pedidos')">
          <i class="fas fa-box"></i>
          <span>Pedidos</span>
        </button>
      <?php endif; ?>

      <!-- Dropdown usuario -->
      <div class="user-dropdown" id="userDropdown">
        <button class="header-btn" onclick="toggleDropdown()">
          <i class="fas fa-user-circle"></i>
          <span><?= $loggedIn ? $nombre : 'Cuenta' ?></span>
        </button>
        <div class="dropdown-panel" id="dropdownPanel">
          <?php if ($loggedIn): ?>
            <div class="user-greeting">Hola, <?= $nombre ?> 👋</div>
            <div class="divider"></div>
            <button onclick="loadView('pedidos'); closeDropdown()">Mis pedidos</button>
            <button onclick="loadView('carrito'); closeDropdown()">Mi cesta</button>
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
            <button onclick="loadView('login'); closeDropdown()">Iniciar sesión</button>
            <button onclick="loadView('register'); closeDropdown()">Crear cuenta</button>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Nav categorías -->
  <nav id="site-nav">
    <div class="nav-inner">
      <button onclick="loadView('productos')" class="btn-categoria" data-categoria="">Todos</button>
      <span class="nav-sep">·</span>
      <button class="btn-categoria" data-categoria="San Valentin">San Valentín</button>
      <span class="nav-sep">·</span>
      <button class="btn-categoria" data-categoria="Eventos">Eventos</button>
      <span class="nav-sep">·</span>
      <button class="btn-categoria" data-categoria="Navidad">Navidad</button>
      <span class="nav-sep">·</span>
      <button class="btn-categoria" data-categoria="Bebés">Bebés</button>
      <span class="nav-sep">·</span>
      <button class="btn-categoria" data-categoria="Niños">Niños</button>
      <span class="nav-sep">·</span>
      <button class="btn-categoria" data-categoria="Hombre">Hombre</button>
      <span class="nav-sep">·</span>
      <button class="btn-categoria" data-categoria="Mujer">Mujer</button>
      <span class="nav-sep">·</span>
      <button class="btn-categoria" data-categoria="Unisex">Unisex</button>
      <span class="nav-sep">·</span>
      <button class="btn-categoria" data-categoria="Lámparas">Lámparas</button>
      <?php if ($loggedIn): ?>
        <span class="nav-sep">·</span>
        <?php if ($esAdmin): ?>
          <a href="index.php?view=chat_admin" style="font-size:0.8rem;letter-spacing:0.09em;text-transform:uppercase;font-weight:500;color:var(--ink-soft);padding:0.7rem 0.9rem;text-decoration:none;">
            <i class="fas fa-comments" style="color:var(--gold)"></i> Chats
          </a>
        <?php else: ?>
          <a href="index.php?view=chat" style="font-size:0.8rem;letter-spacing:0.09em;text-transform:uppercase;font-weight:500;color:var(--ink-soft);padding:0.7rem 0.9rem;text-decoration:none;">
            <i class="fas fa-comment" style="color:var(--rose)"></i> Chat
          </a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </nav>
</div>

<script>
function toggleDropdown() {
  const dd = document.getElementById('userDropdown');
  dd.classList.toggle('open');
}
function closeDropdown() {
  document.getElementById('userDropdown').classList.remove('open');
}
document.addEventListener('click', function(e) {
  const dd = document.getElementById('userDropdown');
  if (dd && !dd.contains(e.target)) dd.classList.remove('open');
});
</script>
