document.addEventListener('DOMContentLoaded', function () {

  // ——— Navegación AJAX ———
  const mainContent = document.getElementById('main');

  window.loadView = function (view, parametro = '') {
    const routes = {
      productos:       `app/vistas/productos/productos.php`,
      carrito:         `app/vistas/carrito/ver_carrito.php`,
      chat:            `app/vistas/chat/chat.php`,
      chat_admin:      `app/vistas/chat/admin.php`,
      pedidos:         `app/vistas/pedidos/lista.php`,
      detalle:         `app/vistas/productos/detalle.php`,
      finalizar_compra:`app/vistas/carrito/finalizar_compra.php`,
      seguimiento:     `app/vistas/productos/seguimiento.php`,
      register:        `app/vistas/usuarios/register.php`,
      login:           `app/vistas/usuarios/login.php`,
      admin_pedidos:   `app/vistas/pedidos/admin_pedidos.php`,
      detalle_admin:   `app/vistas/pedidos/detalle_admin.php`,
      detalle_pedido:  `app/vistas/pedidos/detalle_pedido.php`,
      admin_productos: `app/vistas/admin/productos.php`,
    };

    if (!routes[view]) return;

    let url = routes[view];
    const params = new URLSearchParams();
    if (parametro) params.set('id', parametro);
    // pass through categoria/q from current URL
    const current = new URLSearchParams(window.location.search);
    if (view === 'productos' && current.get('q'))        params.set('q', current.get('q'));
    if (view === 'productos' && current.get('categoria')) params.set('categoria', current.get('categoria'));
    if ([...params].length) url += '?' + params.toString();

    const historyParams = new URLSearchParams({ view });
    if (parametro) historyParams.set('id', parametro);

    fetch(url)
      .then(r => { if (!r.ok) throw new Error('Error al cargar'); return r.text(); })
      .then(html => {
        mainContent.innerHTML = html;
        history.pushState({ view, parametro }, '', '?' + historyParams.toString());
        window.scrollTo({ top: 0, behavior: 'smooth' });
      })
      .catch(() => {
        mainContent.innerHTML = '<div class="empty-state"><i class="fas fa-exclamation-triangle"></i><p>Error al cargar la página.</p></div>';
      });
  };

  // Popstate (navegación con botones del navegador)
  window.addEventListener('popstate', function (e) {
    if (e.state) loadView(e.state.view, e.state.parametro || '');
  });

  // ——— Categorías ———
  document.querySelectorAll('.btn-categoria').forEach(btn => {
    btn.addEventListener('click', () => {
      const cat = btn.getAttribute('data-categoria') || '';
      let url = 'index.php?view=productos';
      if (cat) url += '&categoria=' + encodeURIComponent(cat);
      window.location.href = url;
    });
  });

  // ——— Botón inicio ———
  const btnInicio = document.getElementById('btnInicio');
  if (btnInicio) btnInicio.addEventListener('click', () => loadView('productos'));

});
