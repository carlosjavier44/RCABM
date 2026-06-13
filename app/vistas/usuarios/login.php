<?php if (session_status() == PHP_SESSION_NONE) session_start(); ?>

<section class="auth-section">
  <div class="auth-card">
    <h2>Bienvenida de nuevo</h2>
    <p class="auth-subtitle">Inicia sesión para gestionar tus pedidos y tu cesta.</p>

    <?php if (isset($_SESSION['error_login'])): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error_login'] ?></div>
      <?php unset($_SESSION['error_login']); ?>
    <?php endif; ?>

    <form method="POST" action="/RCABM/app/controladores/controladorUsuario.php">
      <input type="hidden" name="accion" value="login">

      <div class="form-group">
        <label class="form-label" for="email">Correo electrónico</label>
        <input class="form-input" type="email" id="email" name="email" placeholder="tu@email.com" required autofocus>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Contraseña</label>
        <input class="form-input" type="password" id="password" name="password" placeholder="••••••••" required>
      </div>

      <button type="submit" class="btn-primary" style="width:100%;justify-content:center;margin-top:0.5rem">
        Entrar <i class="fas fa-arrow-right"></i>
      </button>
    </form>

    <p class="auth-link">
      ¿Aún no tienes cuenta?
      <a href="javascript:void(0)" onclick="loadView('register')">Regístrate gratis</a>
    </p>
  </div>
</section>
