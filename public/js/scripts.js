// Navegación: redirige a URLs normales en lugar de AJAX
window.loadView = function(view, parametro) {
    let url = 'index.php?view=' + view;
    if (parametro) url += '&id=' + parametro;
    window.location.href = url;
};

document.addEventListener('DOMContentLoaded', function() {
    // Categorías
    document.querySelectorAll('.btn-categoria').forEach(btn => {
        btn.addEventListener('click', function() {
            const cat = this.getAttribute('data-categoria') || '';
            window.location.href = 'index.php?view=productos' + (cat ? '&categoria=' + encodeURIComponent(cat) : '');
        });
    });
});
