<?php 
include __DIR__ . '/../../../config/config.php';
include __DIR__ . '/../../controladores/controladorUsuario.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="public/css/estilos.css">
</head>
<body>

<div class="auth-container">
    <form class="auth-form" action="/proyecto/app/controladores/controladorUsuario.php" method="POST">
        <?php
        if (isset($_SESSION['error_login'])) {
            echo '<div class="error">' . htmlspecialchars($_SESSION['error_login']) . '</div>';
            unset($_SESSION['error_login']);
        }
        ?>
        <h2>Iniciar Sesión</h2>

        <input type="hidden" name="accion" value="login" />

        <input type="email" name="email" placeholder="Correo electrónico" required />
        <input type="password" name="contraseña" placeholder="Contraseña" required />

        <button type="submit">Entrar</button>

        <p style="text-align: center; margin-top: 15px;">
            ¿No tienes cuenta? <a href="?view=register">Regístrate aquí</a>
        </p>
    </form>
</div>

</body>
</html>
