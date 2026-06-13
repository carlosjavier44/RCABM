<?php if (session_status() == PHP_SESSION_NONE) session_start(); ?>

<section class="auth-section">
  <div class="auth-card">
    <h2>Crear cuenta</h2>
    <p class="auth-subtitle">Únete para hacer seguimiento de tus pedidos y comprar fácilmente.</p>

    <?php if (isset($_SESSION['error_register'])): ?>
      <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error_register'] ?></div>
      <?php unset($_SESSION['error_register']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['registro_exitoso'])): ?>
      <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $_SESSION['registro_exitoso'] ?></div>
      <?php unset($_SESSION['registro_exitoso']); ?>
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
        <label class="form-label" for="contrasena">Contraseña</label>
        <input class="form-input" type="password" id="contrasena" name="contrasena" placeholder="Mín. 8 caracteres, mayúscula, número y símbolo" required minlength="8">
      </div>

      <div class="form-group">
        <label class="form-label" for="confirmar_contrasena">Confirmar contraseña</label>
        <input class="form-input" type="password" id="confirmar_contrasena" name="confirmar_contrasena" placeholder="Repite la contraseña" required minlength="8">
      </div>

      <button type="submit" class="btn-primary" style="width:100%;justify-content:center;margin-top:0.5rem">
        Crear cuenta <i class="fas fa-arrow-right"></i>
      </button>
    </form>

    <p class="auth-link">
      ¿Ya tienes cuenta?
      <a href="javascript:void(0)" href="index.php?view=login">Inicia sesión</a>
    </p>
  </div>
</section>
