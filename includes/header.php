<?php
ob_start();
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<header>
    <div id="header" class="d-flex justify-content-between align-items-center p-3 w-100">
        <div id="logo-titulo" class="d-flex align-items-center me-3">
            <img src="public/img/logo.png" alt="Logo" id="logo" style="width: 100px; height: 100px;">
            <h1 id="titulo" class="ms-3 mb-0">Regalos con Amor by Moni</h1>
        </div>

        <div class="d-flex align-items-center justify-content-end">
            <form method="GET" action="index.php">
                <input type="hidden" name="view" value="productos">
                <input type="text" id="busqueda" name="q" placeholder="Buscar productos..." required>
                <button id="buscar" type="submit">
                    <i class="fas fa-search"></i> <span>Buscar</span>
                </button>

            </form>

            <div class="dropdown">
                <button class="btn btn-link p-0" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown"
                    aria-expanded="false">
                    <?php
                    $fotoPerfil = isset($_SESSION['usuario']) ? 'userOn.png' : 'userOff.png';
                    ?>
                    <img src="public/img/<?= $fotoPerfil ?>" alt="Perfil" id="fotoPerfil" class="rounded-circle"
                        style="width:75px; height: 75px;">

                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownMenuButton">
                    <li><button class="dropdown-item" id="btnCarrito">Carrito</button></li>
                    <li><button class="dropdown-item" id="btnPedidos">Seguimiento Pedidos</button></li>
                    <?php if (isset($_SESSION['usuario']) && $_SESSION['usuario']['rol'] === 'admin'): ?>
                        <a href="index.php?view=admin_pedidos" class="btn btn-sm btn-warning" id="btnAdminPedidos">Gestionar
                            Pedidos</a>
                        <a href="?view=admin_productos" class="btn btn-sm btn-warning">
                            Gestionar productos
                        </a>
                    <?php endif; ?>

                    <li>
                        <hr class="dropdown-divider">
                    </li>

                    <?php if (isset($_SESSION['usuario'])): ?>
                        <li class="dropdown-item text-center fw-bold">
                            <?= 'Hola, ' . htmlspecialchars($_SESSION['usuario']['nombre']) ?>
                        </li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <form method="POST" action="/RCABM/app/controladores/controladorUsuario.php"
                                class="text-center">
                                <input type="hidden" name="accion" value="logout">
                                <button type="submit" class="dropdown-item text-danger">Salir</button>
                            </form>
                        </li>
                    <?php else: ?>
                        <li><button class="dropdown-item" id="btnLogin">Login</button></li>
                        <li><button class="dropdown-item" id="btnRegister">Register</button></li>
                    <?php endif; ?>

                </ul>
            </div>
        </div>
    </div>

    <nav class="navbar navbar-expand-lg bg-body-tertiary" id="menu">
        <div class="container-fluid">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup"
                aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>Menú
            </button>
            <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
                <div class="navbar-nav mx-auto">
                    <button class="nav-link text-dark" id="btnInicio">Inicio</button>

                    <?php
                    if (isset($_SESSION['usuario']['id'])) {
                        if ($_SESSION['usuario']['rol'] === 'admin'): ?>
                            <a href="index.php?view=chat_admin" class="nav-link text-dark">Chats</a>
                        <?php else: ?>
                            <a href="index.php?view=chat" class="nav-link text-dark">Chat con admin</a>
                        <?php endif;
                    }
                    ?>



                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark" data-bs-toggle="dropdown" href="#" role="button"
                            aria-expanded="false">Categorías</a>
                        <ul class="dropdown-menu" id="desplegable">
                            <li><button class="dropdown-item btn-categoria" data-categoria="San Valentin">San
                                    Valentín</button></li>
                            <li><button class="dropdown-item btn-categoria" data-categoria="Eventos">Eventos</button>
                            </li>
                            <li><button class="dropdown-item btn-categoria" data-categoria="Navidad">Navidad</button>
                            </li>
                            <li><button class="dropdown-item btn-categoria" data-categoria="Bebés">Bebés</button>
                            <li><button class="dropdown-item btn-categoria" data-categoria="Niños">Niño</button>
                            <li><button class="dropdown-item btn-categoria" data-categoria="Hombre">Hombre</button>
                            <li><button class="dropdown-item btn-categoria" data-categoria="Mujer">Mujer</button>
                            <li><button class="dropdown-item btn-categoria" data-categoria="Unisex">Unisex</button>
                            <li><button class="dropdown-item btn-categoria" data-categoria="Lámparas">Lámparas</button>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><button class="dropdown-item btn-categoria" data-categoria="">Todos</button></li>
                        </ul>
                    </li>
                </div>
            </div>
        </div>
    </nav>
</header>