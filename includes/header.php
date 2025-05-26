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
                    <span>Buscar</span>
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
                    <li><button class="dropdown-item" id="btnPedidos">Pedidos</button></li>
                    <li><button class="dropdown-item" id="btnCarrito">Carrito</button></li>
                    <li><button class="dropdown-item" id="btnSeguimiento">Seguimiento</button></li>
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
                            <form method="POST" action="/proyecto/app/controladores/controladorUsuario.php"
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
                    <button class="nav-link text-dark" id="btnChat">Chat</button>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-dark" data-bs-toggle="dropdown" href="#" role="button"
                            aria-expanded="false">Categorías</a>
                        <ul class="dropdown-menu" id="desplegable">
                            <li><a class="dropdown-item" href="#">San Valentín</a></li>
                            <li><a class="dropdown-item" href="#">Eventos</a></li>
                            <li><a class="dropdown-item" href="#">Navidad</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li><a class="dropdown-item" id="btnTodos">Todos</a></li>
                        </ul>
                    </li>
                </div>
            </div>
        </div>
    </nav>
</header>