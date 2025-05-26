<?php
require_once __DIR__ . '/../../../config/config.php';

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Verificar si se ha enviado una búsqueda
$productos = [];
$termino = '';

if (isset($_GET['q']) && !empty(trim($_GET['q']))) {
    $termino = trim($_GET['q']);
    $sql = "SELECT * FROM productos WHERE stock > 0 AND nombre LIKE ?";
    $stmt = $conn->prepare($sql);
    $like = '%' . $termino . '%';
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $sql = "SELECT * FROM productos WHERE stock > 0";
    $result = $conn->query($sql);
}
?>

<h1 class="mb-4 text-center">Productos disponibles</h1>

<?php if (!empty($termino)): ?>
    <p class="text-center">Mostrando resultados para: <strong><?= htmlspecialchars($termino) ?></strong></p>
<?php endif; ?>

<div class="row row-cols-1 row-cols-md-3 g-4">
    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($producto = $result->fetch_assoc()): ?>
            <div class="col">
                <div class="card h-100 d-flex justify-content-center align-items-centers">
                    <?php if ($producto['imagen']): ?>
                        <img src="<?php echo htmlspecialchars($producto['imagen']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                    <?php else: ?>
                        <img src="public/img/default-product.png" class="card-img-top" alt="Imagen no disponible">
                    <?php endif; ?>
                    <div class="card-body text-center">
                        <h5 class="card-title"><?php echo htmlspecialchars($producto['nombre']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                        <p class="card-text"><strong>Precio: </strong><?php echo number_format($producto['precio'], 2, ',', '.'); ?> €</p>
                    </div>
                    <div class="d-grid col-6 mx-auto mb-4">
                        <button class="btn btn-primary mx-auto"><small>Añadir al carrito</small></button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="text-center">No se encontraron productos disponibles.</p>
    <?php endif; ?>
</div>

<?php
$conn->close();
?>
