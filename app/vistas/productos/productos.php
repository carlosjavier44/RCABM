<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/modelos/Producto.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$productoModel = new Producto($conn);

$termino = isset($_GET['q']) ? trim($_GET['q']) : '';
$orden = isset($_GET['orden']) ? $_GET['orden'] : '';
$categoria = isset($_GET['categoria']) ? $_GET['categoria'] : '';

$productos = $productoModel->obtenerProductos($termino, $categoria, $orden);
?>

<h1 class="mb-4 text-center">Productos disponibles</h1>

<!-- Mensaje que indica qué productos se están mostrando -->
<div class="mb-3 text-center">
    <?php
    if ($categoria !== '' && $termino !== '') {
        echo "Mostrando productos de la categoría <strong>" . htmlspecialchars($categoria) . "</strong> para la búsqueda: <strong>" . htmlspecialchars($termino) . "</strong>";
    } elseif ($categoria !== '') {
        echo "Mostrando productos de la categoría <strong>" . htmlspecialchars($categoria) . "</strong>";
    } elseif ($termino !== '') {
        echo "Mostrando resultados para: <strong>" . htmlspecialchars($termino) . "</strong>";
    }
    ?>
</div>

<!-- Filtro Categorías -->
<form method="get" class="mb-3 text-center" id="formCategorias" style="display: inline-block;">
    <input type="hidden" name="view" value="productos">
    <?php if ($termino !== ''): ?>
        <input type="hidden" name="q" value="<?= htmlspecialchars($termino) ?>">
    <?php endif; ?>
    <?php if ($orden !== ''): ?>
        <input type="hidden" name="orden" value="<?= htmlspecialchars($orden) ?>">
    <?php endif; ?>

    <?php
    $categorias = ['San Valentín', 'Eventos', 'Navidad'];
    ?>
    <!-- Aquí puedes añadir botones o inputs para seleccionar la categoría si quieres -->
</form>

<!-- Selector para ordenar -->
<form method="get" class="mb-4 text-center">
    <input type="hidden" name="view" value="productos">
    <?php if ($termino !== ''): ?>
        <input type="hidden" name="q" value="<?= htmlspecialchars($termino) ?>">
    <?php endif; ?>
    <?php if ($categoria !== ''): ?>
        <input type="hidden" name="categoria" value="<?= htmlspecialchars($categoria) ?>">
    <?php endif; ?>

    <label for="orden">Ordenar por:</label>
    <select name="orden" id="orden" onchange="this.form.submit()" class="form-select d-inline-block w-auto ms-2">
        <option value="">-- Selecciona --</option>
        <option value="precio_asc" <?= $orden === 'precio_asc' ? 'selected' : '' ?>>Precio: menor a mayor</option>
        <option value="precio_desc" <?= $orden === 'precio_desc' ? 'selected' : '' ?>>Precio: mayor a menor</option>
        <option value="nuevos" <?= $orden === 'nuevos' ? 'selected' : '' ?>>Más nuevos</option>
        <option value="antiguos" <?= $orden === 'antiguos' ? 'selected' : '' ?>>Más antiguos</option>
    </select>
</form>

<div class="row row-cols-1 row-cols-md-3 g-4">
    <?php if (!empty($productos)): ?>
        <?php foreach ($productos as $producto): ?>
            <div class="col">
                <a href="javascript:void(0);" onclick="loadView('detalle', <?= $producto['id'] ?>)" class="text-decoration-none text-dark">
                    <div class="card h-100 d-flex justify-content-center align-items-center">
                        <?php if ($producto['imagen']): ?>
                            <img src="<?= htmlspecialchars($producto['imagen']); ?>" class="card-img-top" alt="<?= htmlspecialchars($producto['nombre']); ?>">
                        <?php else: ?>
                            <img src="public/img/default-product.png" class="card-img-top" alt="Imagen no disponible">
                        <?php endif; ?>
                        <div class="card-body text-center">
                            <h5 class="card-title"><?= htmlspecialchars($producto['nombre']); ?></h5>
                            <p class="card-text"><?= htmlspecialchars($producto['descripcion']); ?></p>
                            <p class="card-text"><strong>Precio: </strong><?= number_format($producto['precio'], 2, ',', '.'); ?> €</p>
                        </div>
                        <div class="d-grid col-6 mx-auto mb-4">
                            <span class="btn btn-outline-primary">Ver más</span>
                        </div>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="text-center">No se encontraron productos disponibles.</p>
    <?php endif; ?>
</div>

<?php
$conn->close();
?>
