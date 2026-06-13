<?php if (session_status() == PHP_SESSION_NONE) session_start(); ?>

<section class="auth-section">
  <div class="auth-card">
    <h2>Crear cuenta</h2>
    <p class="auth-subtitle">Únete para hacer seguimiento de tus pedidos y comprar fácilmente.</p>

    <?php if (isset($_SESSION['error_register'])): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error_register'] ?></div>
      <?php unset($_SESSION['error_register']); ?>
    <?php endif; ?>

    <form method="POST" action="/RCABM/app/controladores/controladorUsuario.php">
      <input type="hidden" name="accion" value="register">

      <div class="form-group">
        <label class="form-label" for="nombre">Nombre</label>
        <input class="form-input" type="text" id="nombre" name="nombre" placeholder="Tu nombre" required autofocus>
      </div>

      <div class="form-group">
        <label class="form-label" for="email">Correo electrónico</label>
        <input class="form-input" type="email" id="email" name="email" placeholder="tu@email.com" required>
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Contraseña</label>
        <input class="form-input" type="password" id="password" name="password" placeholder="Mínimo 6 caracteres" required minlength="6">
      </div>

      <button type="submit" class="btn-primary" style="width:100%;justify-content:center;margin-top:0.5rem">
        Crear cuenta <i class="fas fa-arrow-right"></i>
      </button>
    </form>

    <p class="auth-link">
      ¿Ya tienes cuenta?
      <a href="javascript:void(0)" onclick="loadView('login')">Inicia sesión</a>
    </p>
  </div>
</section>
