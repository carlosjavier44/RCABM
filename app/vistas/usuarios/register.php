<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Registro de usuario</title>
    <link rel="stylesheet" href="public/css/estilos.css" />
</head>

<body>

    <div class="auth-container">
        <form class="auth-form" action="/RCABM/app/controladores/controladorUsuario.php" method="POST">
            <h2>Registro de usuario</h2>

            <?php
            if (isset($_SESSION['error_register'])) {
                echo '<p style="color:red;">' . htmlspecialchars($_SESSION['error_register']) . '</p>';
                unset($_SESSION['error_register']);
            }
            if (isset($_SESSION['registro_exitoso'])) {
                echo '<p style="color:green;">' . htmlspecialchars($_SESSION['registro_exitoso']) . '</p>';
                unset($_SESSION['registro_exitoso']);
            }
            ?>

            <input type="hidden" name="accion" value="register" />

            <input type="text" id="nombre" name="nombre" placeholder="Nombre completo" required />

            <input type="email" id="email" name="email" placeholder="Correo electrónico" required />

            <input type="password" id="contraseña" name="contraseña" placeholder="Contraseña" required />
            <small>La contraseña debe tener mínimo 8 caracteres, al menos una mayúscula, un número y un símbolo.</small><br /><br />

            <!-- Aceptación de términos -->
            <label style="font-size: 0.9em;">
                <input type="checkbox" name="acepto_politica" required />
                Acepto la <a href="/RCABM/public/politica.php" target="_blank">Política de Privacidad</a> y los Términos de uso.
            </label><br /><br />

            <button type="submit">Registrarme</button>

            <p style="text-align: center; margin-top: 15px;">
                ¿Ya tienes cuenta? <a href="?view=login">Inicia sesión aquí</a>
            </p>
        </form>
    </div>

</body>

</html>
