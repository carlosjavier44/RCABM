document.addEventListener('DOMContentLoaded', function () {
    const btnInicio = document.getElementById('btnInicio');
    const btnCarrito = document.getElementById('btnCarrito');
    const btnChat = document.getElementById('btnChat');
    const btnPedidos = document.getElementById('btnPedidos');
    const btnRegister = document.getElementById('btnRegister');
    const btnLogin = document.getElementById('btnLogin');
    const btnTodos = document.getElementById('btnTodos');
    const mainContent = document.getElementById('main');

    if (btnInicio) btnInicio.addEventListener('click', () => loadView('productos'));
    if (btnTodos) btnTodos.addEventListener('click', () => loadView('productos'));
    if (btnLogin) btnLogin.addEventListener('click', () => loadView('login'));
    if (btnCarrito) btnCarrito.addEventListener('click', () => loadView('carrito'));
    if (btnChat) btnChat.addEventListener('click', () => loadView('chat'));
    if (btnPedidos) btnPedidos.addEventListener('click', () => loadView('pedidos'));
    if (btnRegister) btnRegister.addEventListener('click', () => loadView('register'));

    window.addEventListener('popstate', function (event) {
        if (event.state) {
            loadView(event.state.page);
        }
    });

    window.loadView = function(view, parametro = '') {
        let file = '';
        let url = '';

        switch (view) {
            case 'productos':
                file = 'productos/productos';
                url = `app/vistas/${file}.php${parametro ? '?categoria=' + encodeURIComponent(parametro) : ''}`;
                break;
            case 'carrito':
                file = 'carrito/ver_carrito';
                url = `app/vistas/${file}.php`;
                break;
            case 'chat':
                file = 'chat/chat';
                url = `app/vistas/${file}.php`;
                break;
            case 'pedidos':
                file = 'pedidos/lista';
                url = `app/vistas/${file}.php`;
                break;
            case 'detalle':
                file = 'productos/detalle';
                url = `app/vistas/${file}.php?id=${encodeURIComponent(parametro)}`;
                break;
            case 'finalizar_compra':
                file = 'carrito/finalizar_compra';
                url = `app/vistas/${file}.php`;
                break;
            case 'seguimiento':
                file = 'productos/seguimiento';
                url = `app/vistas/${file}.php`;
                break;
            case 'register':
                file = 'usuarios/register';
                url = `app/vistas/${file}.php`;
                break;
            case 'login':
                file = 'usuarios/login';
                url = `app/vistas/${file}.php`;
                break;
            default:
                return;
        }

        fetch(url)
            .then(response => {
                if (!response.ok) throw new Error('Error al cargar la vista');
                return response.text();
            })
            .then(data => {
                mainContent.innerHTML = data;
                history.pushState({ page: view }, '', `?view=${view}${parametro ? `&id=${parametro}` : ''}`);
                mainContent.dataset.loadedView = view;
            })
            .catch(error => {
                mainContent.innerHTML = '<h2 class="text-center text-danger">Error al cargar la vista</h2>';
                console.error(error);
            });
    };
});
