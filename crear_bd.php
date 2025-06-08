<?php
$host = "localhost";
$usuario = "root";
$contrasena = "";
$nombre_bd = "mi_tienda";

$conn = new mysqli($host, $usuario, $contrasena);
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
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('cliente', 'admin') DEFAULT 'cliente',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Crear tabla productos
$conn->query("CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    imagen VARCHAR(255) DEFAULT NULL,
    categoria VARCHAR(100) NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Crear tabla pedidos
$conn->query("CREATE TABLE IF NOT EXISTS pedidos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('en espera', 'en proceso', 'en envío', 'completado') DEFAULT 'en espera',
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
    observaciones VARCHAR(255),
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
    leido TINYINT(1) DEFAULT 0,
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
    $stmt_insert = $conn->prepare("INSERT INTO usuarios (id, nombre, email, contrasena) VALUES (?, ?, ?, ?)");
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
    $stmt_insert = $conn->prepare("INSERT INTO usuarios (id, nombre, email, contrasena, rol) VALUES (?, ?, ?, ?, ?)");
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
    [1, 'Abridor Personalizado', 'Abridor con puño de madera personalizado.', 9.99, 'public\\img\\abridor.jpg', 'Unisex'],
    [2, 'Abridor de Bambú', 'Abridor de bambú personalizado para tu evento.', 8.99, 'public\\img\\abridor_bambu.jpg', 'Eventos'],
    [3, 'Bandeja Papá Noel', 'Bandeja papá Noel y vaso personalizada.', 16.50, 'public\\img\\bandeja_papa_noel.jpg', 'Navidad'],
    [4, 'Bandeja Reyes Magos', 'Bandeja reyes magos con tres vasos personalizada.', 18.99, 'public\\img\\bandeja_reyes_magos.jpg', 'Navidad'],
    [5, 'Bola para Árbol', 'Bola personalizada para árbol de navidad.', 6.99, 'public\\img\\bola_arbol.jpg', 'Navidad'],
    [6, 'Bolígrafo 8 Colores', 'Bolígrafo 8 colores personalizado con nombre y temática.', 4.50, 'public\\img\\boligrafo.jpg', 'Niños'],
    // [7, 'Botella San Valentín', 'Botella personalizada San Valentín Mickey.', 11.99, 'public\\img\\botella_san_valentin.jpg', 'San Valentín'],
    // [8, 'Calcetín de Navidad', 'Calcetín árbol de navidad personalizado.', 7.99, 'public\\img\\calcetin_arbol.jpg', 'Navidad'],
    // [9, 'Caja Natalicio', 'Caja natalicio, un regalo ideal para un recién nacido.', 19.50, 'public\\img\\caja_natalicio.jpg', 'Bebés'],
    [10, 'Comba Personalizada', 'Combas personalizadas con puños de madera.', 5.99, 'public\\img\\comba.jpg', 'Niños'],
    [11, 'Elefante Natalicio', 'Elefante natalicio, tres colores disponibles.', 13.99, 'public\\img\\elefante_natalicio.jpg', 'Bebés'],
    [12, 'Lámpara Fútbol', 'Lámpara balón de fútbol personalizada y control remoto o táctil, carga USB cable incluido.', 24.99, 'public\\img\\lampara_futbol.jpg', 'Lámparas'],
    [13, 'Joyero Pequeño', 'Joyeros personalizados 10*10*5 cm.', 14.50, 'public\\img\\joyero_pequeño.jpg', 'Mujer'],
    [14, 'Lámpara Spiderman', 'Lámpara Spiderman personalizada. 16 colores, control mando a distancia y táctil.', 26.99, 'public\\img\\lampara_spideman.jpg', 'Lámparas'],
    [15, 'Lámpara Stich', 'Lámpara Stich 16 colores, con control remoto y táctil.', 26.99, 'public\\img\\lampara_stich.jpg', 'Lámparas'],
    [16, 'Llavero Polipiel', 'Llaveros de polipiel personalizados con nombre e inicial.', 6.99, 'public\\img\\llavero_polipiel.jpg', 'Unisex'],
    [17, 'Neceser Hombre', 'Neceser personalizado con nombre e inicial.', 12.99, 'public\\img\\neceser_hombre.jpg', 'Hombre'],
    [18,'Pulsera Perlas Inicial', 'Pulsera de perlas con inicial y tarjetón con el diseño que más te guste.', 9.50, 'public\\img\\pulsera_perlas_inicial.jpg', 'Eventos'],
    [19, 'Neceser Mujer', 'Neceser personalizado, de fieltro reciclado.', 11.50, 'public\\img\\neceser_mujer.jpg', 'Mujer'],
    // [20, 'Taza San Valentín', 'San Valentín. Taza personalizada.', 8.99, 'public\\img\\taza_san_valentin.jpg', 'San Valentín'],
    [21, 'Tres en Raya', 'Juego de tres en raya personalizado.', 10.99, 'public\\img\\tres_en_raya.jpg', 'Niños'],
    [22, 'Trompos de Colores', 'Trompos de madera en varios colores, personalizados con el nombre de cada niño.', 5.99, 'public\\img\\trompos.jpg', 'Niños'],
    [23, 'Vasos 250ml', 'Vasos de 250 ml personalizados a tu gusto, en colores vivos.', 4.99, 'public\\img\\vasos_250.jpg', 'Niños'],
];


foreach ($productos as $producto) {
    [$id, $nombre, $descripcion, $precio, $imagen, $categoria] = $producto;

    $stmt_check = $conn->prepare("SELECT id FROM productos WHERE id = ?");
    $stmt_check->bind_param("i", $id);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows === 0) {
        $stmt_insert = $conn->prepare("INSERT INTO productos (id, nombre, descripcion, precio, imagen, categoria) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt_insert->bind_param("issdss", $id, $nombre, $descripcion, $precio, $imagen, $categoria);
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
