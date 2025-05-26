document.addEventListener('DOMContentLoaded', function () {
    const btnInicio = document.getElementById('btnInicio');
    const btnCarrito = document.getElementById('btnCarrito');
    const btnChat = document.getElementById('btnChat');
    const btnPedidos = document.getElementById('btnPedidos');
    const btnRegister = document.getElementById('btnRegister');
    const btnLogin = document.getElementById('btnLogin');
    const btnTodos = document.getElementById('btnTodos');
    const mainContent = document.getElementById('main');

    btnInicio.addEventListener('click', function () {
        loadView('productos');
    });

    btnTodos.addEventListener('click', function () {
        loadView('productos');
    });

    btnLogin.addEventListener('click', function () {
        loadView('login');
    });

    btnCarrito.addEventListener('click', function () {
        loadView('carrito');
    });

    btnChat.addEventListener('click', function () {
        loadView('chat');
    });

    btnPedidos.addEventListener('click', function () {
        loadView('pedidos');
    });

    btnRegister.addEventListener('click', function () {
        loadView('register');
    });

    window.addEventListener('popstate', function (event) {
        if (event.state) {
            loadView(event.state.page);
        }
    });

    function loadView(view, categoria = '') {
        let file = '';
        let url = '';
    
        switch (view) {
            case 'productos':
                file = 'productos/productos';
                url = `app/vistas/${file}.php${categoria ? '?categoria=' + encodeURIComponent(categoria) : ''}`;
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
                file = 'pedidos/detalle';
                url = `app/vistas/${file}.php`;
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
            .then(response => response.text())
            .then(data => {
                mainContent.innerHTML = data;
                history.pushState({ page: view }, view, `?view=${view}${categoria ? '&categoria=' + encodeURIComponent(categoria) : ''}`);
            })
            .catch(error => {
                console.error(`Error al cargar la vista de ${view}:`, error);
            });
    }
    
});
