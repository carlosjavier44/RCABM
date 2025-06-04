<?php
require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../app/modelos/Producto.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    echo "<p class='text-center text-danger'>Acceso denegado.</p>";
    exit;
}

$productoModel = new Producto($conn);

$editando = false;
$productoEditado = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = floatval($_POST['precio']);
    $categoria = trim($_POST['categoria']);
    $stock = intval($_POST['stock']);
    $imagen = '';

    if (!empty($_FILES['imagen']['name'])) {
        $rutaDestino = 'public/img/' . basename($_FILES['imagen']['name']);
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], __DIR__ . '/../../../' . $rutaDestino)) {
            $imagen = $rutaDestino;
        }
    }

    if ($id) {
        $productoModel->actualizarProducto($id, $nombre, $descripcion, $precio, $categoria, $stock, $imagen);
    } else {
        $productoModel->agregarProducto($nombre, $descripcion, $precio, $categoria, $stock, $imagen);
    }
    header("Location: index.php?view=admin_productos");
    exit;
}

if (isset($_GET['editar'])) {
    $editando = true;
    $productoEditado = $productoModel->obtenerProductoPorId($_GET['editar']);
}

if (isset($_GET['eliminar'])) {
    $productoModel->eliminarProducto($_GET['eliminar']);
    header("Location: index.php?view=admin_productos");
    exit;
}

$productos = $productoModel->obtenerTodosLosProductos();
?>

<h2 class="text-center my-4"><?= $editando ? 'Editar producto' : 'Agregar nuevo producto' ?></h2>

<form method="post" enctype="multipart/form-data" class="mx-auto" style="max-width: 600px;">
    <?php if ($editando): ?>
        <input type="hidden" name="id" value="<?= $productoEditado['id'] ?>">
    <?php endif; ?>

    <div class="mb-3">
        <label for="nombre" class="form-label">Nombre:</label>
        <input type="text" name="nombre" id="nombre" class="form-control" required value="<?= $editando ? htmlspecialchars($productoEditado['nombre']) : '' ?>">
    </div>

    <div class="mb-3">
        <label for="descripcion" class="form-label">Descripción:</label>
        <textarea name="descripcion" id="descripcion" class="form-control" required><?= $editando ? htmlspecialchars($productoEditado['descripcion']) : '' ?></textarea>
    </div>

    <div class="mb-3">
        <label for="precio" class="form-label">Precio (€):</label>
        <input type="number" step="0.01" name="precio" id="precio" class="form-control" required value="<?= $editando ? $productoEditado['precio'] : '' ?>">
    </div>

    <div class="mb-3">
        <label for="categoria" class="form-label">Categoría:</label>
        <input type="text" name="categoria" id="categoria" class="form-control" required value="<?= $editando ? htmlspecialchars($productoEditado['categoria']) : '' ?>">
    </div>

    <div class="mb-3">
        <label for="stock" class="form-label">Stock:</label>
        <input type="number" name="stock" id="stock" class="form-control" required value="<?= $editando ? $productoEditado['stock'] : '' ?>">
    </div>

    <div class="mb-3">
        <label for="imagen" class="form-label">Imagen:</label>
        <input type="file" name="imagen" id="imagen" class="form-control">
        <?php if ($editando && $productoEditado['imagen']): ?>
            <div class="mt-2">
                <img src="<?= htmlspecialchars($productoEditado['imagen']) ?>" alt="Imagen actual" width="100">
            </div>
        <?php endif; ?>
    </div>

    <div class="text-center">
        <button type="submit" class="btn btn-success"><?= $editando ? 'Actualizar' : 'Agregar' ?></button>
        <?php if ($editando): ?>
            <a href="index.php?view=admin_productos" class="btn btn-secondary ms-2">Cancelar</a>
        <?php endif; ?>
    </div>
</form>

<hr class="my-5">

<h3 class="text-center">Listado de productos</h3>

<div class="table-responsive">
    <table class="table table-striped table-bordered text-center align-middle">
        <thead class="table-dark">
            <tr>
                <th>Imagen</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Precio (€)</th>
                <th>Categoría</th>
                <th>Stock</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($productos as $producto): ?>
                <tr>
                    <td>
                        <?php if ($producto['imagen']): ?>
                            <img src="<?= htmlspecialchars($producto['imagen']) ?>" width="60">
                        <?php else: ?>
                            <img src="public/img/default-producto.png" width="60">
                        <?php endif; ?>
                    </td>
                    <td><?= htmlspecialchars($producto['nombre']) ?></td>
                    <td><?= htmlspecialchars($producto['descripcion']) ?></td>
                    <td><?= number_format($producto['precio'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($producto['categoria']) ?></td>
                    <td><?= $producto['stock'] ?></td>
                    <td>
                        <a href="index.php?view=admin_productos&editar=<?= $producto['id'] ?>" class="btn btn-sm btn-warning">Editar</a>
                        <a href="index.php?view=admin_productos&eliminar=<?= $producto['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('¿Seguro que deseas eliminar este producto?')">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php $conn->close(); ?>
