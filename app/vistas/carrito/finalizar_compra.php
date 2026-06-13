<?php
require_once __DIR__ . '/../../../config/config.php';

if (!isset($_SESSION['usuario'])) {
    echo '<script>window.location.href="/index.php?view=login"</script>'; exit;
}
?>

<div class="container mt-5">
    <h2>Finalizar compra</h2>
    <form method="post" action="/app/controladores/controladorPago.php">
        <div class="mb-3">
            <label>Nombre en la tarjeta</label>
            <input type="text" name="nombre_titular" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Número de tarjeta (ficticio)</label>
            <input type="text" name="numero_tarjeta" class="form-control" maxlength="16" required>
        </div>
        <div class="mb-3">
            <label>Fecha de expiración</label>
            <input type="text" name="fecha_exp" class="form-control" placeholder="MM/AA" required>
        </div>
        <div class="mb-3">
            <label>CVC</label>
            <input type="text" name="cvc" class="form-control" maxlength="3" required>
        </div>
        <button type="submit" class="btn btn-success">Pagar</button>
    </form>
</div>
