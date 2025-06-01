<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Definir variable para evitar warnings
$usuarioSeleccionadoId = isset($_GET['user']) ? (int)$_GET['user'] : null;
?>

<div class="chat-admin-container d-flex">
    <div class="usuarios-lista border-end pe-3" style="min-width: 250px;">
        <h4>Usuarios</h4>
        <ul class="list-group">
            <?php if (!empty($usuariosConConversacion)): ?>
                <?php foreach ($usuariosConConversacion as $usuario): ?>
                    <li class="list-group-item <?= $usuarioSeleccionadoId == $usuario['id'] ? 'active' : '' ?>">
                        <a href="index.php?view=chat&user=<?= $usuario['id'] ?>" class="text-decoration-none <?= $usuarioSeleccionadoId == $usuario['id'] ? 'text-white' : '' ?>">
                            <?= htmlspecialchars($usuario['nombre']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            <?php else: ?>
                <li class="list-group-item">No hay conversaciones</li>
            <?php endif; ?>
        </ul>
    </div>

    <div class="chat-box-container flex-grow-1 ps-3">
        <h4>Conversación</h4>
        <div class="chat-box border p-3 mb-3" id="chat-box" style="height: 400px; overflow-y: auto; background-color: #f9f9f9;">
            <?php if (!empty($mensajes)): ?>
                <?php foreach ($mensajes as $mensaje): ?>
                    <div class="mensaje mb-2 <?= $mensaje['emisor_id'] == $_SESSION['usuario']['id'] ? 'text-end' : 'text-start' ?>">
                        <div class="p-2 d-inline-block <?= $mensaje['emisor_id'] == $_SESSION['usuario']['id'] ? 'bg-primary text-white' : 'bg-light' ?>" style="border-radius: 10px; max-width: 75%;">
                            <?= htmlspecialchars($mensaje['mensaje']) ?><br>
                            <small class="text-muted"><?= $mensaje['fecha'] ?></small>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted">No hay mensajes aún</p>
            <?php endif; ?>
        </div>

        <?php if ($usuarioSeleccionadoId): ?>
            <form method="POST" id="form-chat">
                <input type="hidden" name="accion" value="enviar">
                <input type="hidden" name="receptor_id" value="<?= $usuarioSeleccionadoId ?>">
                <div class="input-group">
                    <input type="text" name="mensaje" class="form-control" placeholder="Escribe un mensaje..." required>
                    <button class="btn btn-primary" type="submit">Enviar</button>
                </div>
            </form>
        <?php else: ?>
            <p class="text-muted">Selecciona un usuario para chatear</p>
        <?php endif; ?>
    </div>
</div>

<script>
    const form = document.getElementById('form-chat');
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const datos = new FormData(form);
            const resp = await fetch('/app/controladores/controladorChat.php', {
                method: 'POST',
                body: datos
            });
            if (resp.ok) location.reload();
        });
    }
</script>
