document.addEventListener('DOMContentLoaded', function() {
    // Eliminar producto del carrito
    const botonesEliminar = document.querySelectorAll('.btnEliminar');
    botonesEliminar.forEach(boton => {
        boton.addEventListener('click', function() {
            const id = boton.getAttribute('data-id');
            eliminarDelCarrito(id);
        });
    });

    // Vaciar el carrito
    const botonVaciarCarrito = document.getElementById('vaciarCarrito');
    if (botonVaciarCarrito) {
        botonVaciarCarrito.addEventListener('click', function() {
            vaciarCarrito();
        });
    }

    function eliminarDelCarrito(id) {
        fetch('app/controladores/controladorCarrito.php', {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ id: id })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Producto eliminado del carrito');
                location.reload(); // Recargar la página para actualizar el carrito
            } else {
                alert('Error al eliminar el producto del carrito');
            }
        })
        .catch(error => console.error('Error:', error));
    }

    function vaciarCarrito() {
        fetch('app/controladores/controladorCarrito.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ action: 'vaciar' })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Carrito vaciado');
                location.reload(); // Recargar la página para actualizar el carrito
            } else {
                alert('Error al vaciar el carrito');
            }
        })
        .catch(error => console.error('Error:', error));
    }
});