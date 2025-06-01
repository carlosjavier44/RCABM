<?php
$host = "localhost";
$usuario = "root";
$contraseña = "";
$nombre_bd = "mi_tienda";

$conn = new mysqli($host, $usuario, $contraseña);
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$sql = "CREATE DATABASE IF NOT EXISTS $nombre_bd CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if ($conn->query($sql) === TRUE) {
    echo "Base de datos creada correctamente.<br>";
} else {
    die("Error al crear la base de datos: " . $conn->error);
}

$conn->select_db($nombre_bd);

// Crear tabla usuarios
$conn->query("CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    contraseña VARCHAR(255) NOT NULL,
    rol ENUM('cliente', 'admin') DEFAULT 'cliente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Crear tabla productos
$conn->query("CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL,
    imagen VARCHAR(255) DEFAULT NULL,
    categoria VARCHAR(100) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Crear tabla pedidos
$conn->query("CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('en espera', 'en proceso', 'en envío') DEFAULT 'en espera',
    total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
)");

// Crear tabla detalles_pedido
$conn->query("CREATE TABLE IF NOT EXISTS detalles_pedido (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
)");

// Crear tabla carrito
$conn->query("CREATE TABLE IF NOT EXISTS carrito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    producto_id INT NOT NULL,
    cantidad INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE
)");

// Crear tabla mensajes
$conn->query("CREATE TABLE IF NOT EXISTS mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    emisor_id INT NOT NULL,
    receptor_id INT NOT NULL,
    mensaje TEXT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (emisor_id) REFERENCES usuarios(id),
    FOREIGN KEY (receptor_id) REFERENCES usuarios(id)
)");

echo "Tablas creadas correctamente.<br>";

// Insertar usuario de prueba Carlos
$email = 'a@gmail.com';
$password = password_hash('Carloscajar4@', PASSWORD_DEFAULT);
$nombre = 'Carlos';

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt_insert = $conn->prepare("INSERT INTO usuarios (id, nombre, email, contraseña) VALUES (?, ?, ?, ?)");
    $id_usuario = 4;
    $stmt_insert->bind_param("isss", $id_usuario, $nombre, $email, $password);
    $stmt_insert->execute();
    echo "Usuario de prueba insertado.<br>";
    $stmt_insert->close();
} else {
    echo "El usuario ya existe, no se insertó.<br>";
}
$stmt->close();

// Insertar usuario admin
$email = 'admin@gmail.com';
$password = password_hash('Carloscajar4@', PASSWORD_DEFAULT);
$nombre = 'admin';
$rol = 'admin';

$stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 0) {
    $stmt_insert = $conn->prepare("INSERT INTO usuarios (id, nombre, email, contraseña, rol) VALUES (?, ?, ?, ?, ?)");
    $id_admin = 1;
    $stmt_insert->bind_param("issss", $id_admin, $nombre, $email, $password, $rol);
    $stmt_insert->execute();
    echo "Usuario administrador insertado.<br>";
    $stmt_insert->close();
} else {
    echo "El usuario admin ya existe, no se insertó.<br>";
}
$stmt->close();

// Insertar productos
$productos = [
    [3, 'Joyero Personalizado', 'Hermoso joyero grabado con el nombre que desees.', 25.99, 10, 'public\\img\\default-product.png', 'San Valentín'],
    [4, 'Lámpara LED Personalizada', 'Lámpara LED con grabado personalizado.', 34.50, 15, 'public\\img\\default-product.png', 'Navidad'],
    [5, 'Trompo Personalizado', 'Trompo de madera personalizado.', 12.99, 20, 'public\\img\\default-product.png', 'Eventos'],
    [6, 'Camiseta Personalizada', 'Camiseta de algodón con estampado personalizado.', 19.99, 30, 'public\\img\\default-product.png', 'Regalo personalizado'],
    [8, 'Vasos Personalizados', 'Set de vasos con grabado personalizado.', 22.50, 25, 'public\\img\\default-product.png', 'Regalo personalizado'],
    [10, 'Agenda Personalizada', 'Agenda con diseño personalizado.', 15.99, 50, 'public\\img\\default-product.png', 'Regalo personalizado'],
    [11, 'Estuche Personalizado', 'Estuche para lapices con diseño personalizado.', 7.99, 75, 'public\\img\\default-product.png', 'Regalo personalizado'],
];

foreach ($productos as $producto) {
    [$id, $nombre, $descripcion, $precio, $stock, $imagen, $categoria] = $producto;

    $stmt_check = $conn->prepare("SELECT id FROM productos WHERE id = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows === 0) {
        $stmt_insert = $conn->prepare("INSERT INTO productos (id, nombre, descripcion, precio, stock, imagen, categoria) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("issdiss", $id, $nombre, $descripcion, $precio, $stock, $imagen, $categoria);
        $stmt_insert->execute();
        echo "Producto '$nombre' insertado.<br>";
        $stmt_insert->close();
    } else {
        echo "Producto con ID $id ya existe, no se insertó.<br>";
    }
    $stmt_check->close();
}

$conn->close();
?>
