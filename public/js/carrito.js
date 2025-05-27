document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.inputCantidad').forEach(input => {
      input.addEventListener('change', e => {
        const id = e.target.dataset.id;
        let cantidad = parseInt(e.target.value);
        if (isNaN(cantidad) || cantidad < 1) {
          cantidad = 1;
          e.target.value = 1;
        }
        actualizarCantidad(id, cantidad);
      });
    });
  
    document.querySelectorAll('.btnEliminar').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        eliminarProducto(id);
      });
    });
  
    const btnVaciar = document.getElementById('vaciarCarrito');
    if (btnVaciar) {
      btnVaciar.addEventListener('click', () => {
        if (confirm('¿Seguro que quieres vaciar el carrito?')) {
          vaciarCarrito();
        }
      });
    }
  });
  
  function actualizarCantidad(id, cantidad) {
    fetch('/proyecto/app/controladores/controladorCarrito.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({
        accion: 'actualizarCantidad',
        id: id,
        cantidad: cantidad
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Error al actualizar la cantidad');
      }
    })
    .catch(() => alert('Error en la comunicación con el servidor'));
  }
  
  function eliminarProducto(id) {
    fetch('/proyecto/app/controladores/controladorCarrito.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({
        accion: 'eliminarProducto',
        id: id
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Error al eliminar el producto');
      }
    })
    .catch(() => alert('Error en la comunicación con el servidor'));
  }
  
  function vaciarCarrito() {
    fetch('/proyecto/app/controladores/controladorCarrito.php', {
      method: 'POST',
      headers: {'Content-Type': 'application/x-www-form-urlencoded'},
      body: new URLSearchParams({
        accion: 'vaciarCarrito'
      })
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        location.reload();
      } else {
        alert('Error al vaciar el carrito');
      }
    })
    .catch(() => alert('Error en la comunicación con el servidor'));
  }
  