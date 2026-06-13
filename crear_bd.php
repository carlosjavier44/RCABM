<?php
$conn = new mysqli("localhost","root","","");
if($conn->connect_error) die("Error: ".$conn->connect_error);
$conn->query("CREATE DATABASE IF NOT EXISTS mi_tienda CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
echo "Base de datos creada.<br>";
$conn->select_db("mi_tienda");
$conn->query("CREATE TABLE IF NOT EXISTS usuarios(id INT AUTO_INCREMENT PRIMARY KEY,nombre VARCHAR(100) NOT NULL,email VARCHAR(100) NOT NULL UNIQUE,contrasena VARCHAR(255) NOT NULL,rol ENUM('cliente','admin') DEFAULT 'cliente',fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$conn->query("CREATE TABLE IF NOT EXISTS productos(id INT AUTO_INCREMENT PRIMARY KEY,nombre VARCHAR(100) NOT NULL,descripcion TEXT NOT NULL,precio DECIMAL(10,2) NOT NULL,imagen VARCHAR(255) DEFAULT NULL,categoria VARCHAR(100) NOT NULL,personalizable TINYINT(1) DEFAULT 0,
    campos_personalizacion TEXT DEFAULT NULL,fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
$conn->query("CREATE TABLE IF NOT EXISTS pedidos(id INT AUTO_INCREMENT PRIMARY KEY,usuario_id INT NOT NULL,fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,estado ENUM('en espera','en proceso','en envío','completado') DEFAULT 'en espera',total DECIMAL(10,2) NOT NULL,FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE)");
$conn->query("CREATE TABLE IF NOT EXISTS detalles_pedido(id INT AUTO_INCREMENT PRIMARY KEY,pedido_id INT NOT NULL,producto_id INT NOT NULL,cantidad INT NOT NULL,subtotal DECIMAL(10,2) NOT NULL,observaciones VARCHAR(500) DEFAULT NULL,FOREIGN KEY(pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,FOREIGN KEY(producto_id) REFERENCES productos(id) ON DELETE CASCADE)");
$conn->query("CREATE TABLE IF NOT EXISTS carrito(id INT AUTO_INCREMENT PRIMARY KEY,usuario_id INT NOT NULL,producto_id INT NOT NULL,cantidad INT NOT NULL DEFAULT 1,observacion VARCHAR(500) DEFAULT NULL,FOREIGN KEY(usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,FOREIGN KEY(producto_id) REFERENCES productos(id) ON DELETE CASCADE)");
$conn->query("CREATE TABLE IF NOT EXISTS mensajes(id INT AUTO_INCREMENT PRIMARY KEY,emisor_id INT NOT NULL,receptor_id INT NOT NULL,mensaje TEXT NOT NULL,fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,leido TINYINT(1) DEFAULT 0,visto TINYINT(1) DEFAULT 0,FOREIGN KEY(emisor_id) REFERENCES usuarios(id),FOREIGN KEY(receptor_id) REFERENCES usuarios(id))");
echo "Tablas creadas.<br>";

function insertUser($conn,$nombre,$email,$pass,$rol){
    $s=$conn->prepare("SELECT id FROM usuarios WHERE email=?");
    $s->bind_param("s",$email);$s->execute();$s->store_result();
    if($s->num_rows===0){$h=password_hash($pass,PASSWORD_DEFAULT);$conn->query("INSERT INTO usuarios(nombre,email,contrasena,rol) VALUES('$nombre','$email','$h','$rol')");echo "Usuario $nombre insertado.<br>";}
    else echo "Usuario $nombre ya existe.<br>";
    $s->close();
}
insertUser($conn,'admin','admin@gmail.com','Carloscajar4@','admin');
insertUser($conn,'Prueba','prueba@gmail.com','Carloscajar4@','cliente');

$prods=[
    [1,'Abridor Personalizado','Abridor con puño de madera personalizado.',9.99,'public/img/abridor.jpg','Unisex',1],
    [2,'Abridor de Bambú','Abridor de bambú personalizado para tu evento.',8.99,'public/img/abridor_bambu.jpg','Eventos',1],
    [3,'Bandeja Papá Noel','Bandeja papá Noel y vaso personalizada.',16.50,'public/img/bandeja_papa_noel.jpg','Navidad',1],
    [4,'Bandeja Reyes Magos','Bandeja reyes magos con tres vasos personalizada.',18.99,'public/img/bandeja_reyes_magos.jpg','Navidad',1],
    [5,'Bola para Árbol','Bola personalizada para árbol de navidad.',6.99,'public/img/bola_arbol.jpg','Navidad',1],
    [6,'Bolígrafo 8 Colores','Bolígrafo 8 colores personalizado con nombre y temática.',4.50,'public/img/boligrafo.jpg','Niños',1],
    [10,'Comba Personalizada','Combas personalizadas con puños de madera.',5.99,'public/img/comba.jpg','Niños',1],
    [11,'Elefante Natalicio','Elefante natalicio, tres colores disponibles.',13.99,'public/img/elefante_natalicio.jpg','Bebés',1],
    [12,'Lámpara Fútbol','Lámpara balón de fútbol personalizada y control remoto o táctil.',24.99,'public/img/lampara_futbol.jpg','Lámparas',1],
    [13,'Joyero Pequeño','Joyeros personalizados 10*10*5 cm.',14.50,'public/img/joyero_pequeño.jpg','Mujer',1],
    [14,'Lámpara Spiderman','Lámpara Spiderman 16 colores, mando a distancia y táctil.',26.99,'public/img/lampara_spideman.jpg','Lámparas',0],
    [15,'Lámpara Stich','Lámpara Stich 16 colores, control remoto y táctil.',26.99,'public/img/lampara_stich.jpg','Lámparas',0],
    [16,'Llavero Polipiel','Llaveros de polipiel personalizados con nombre e inicial.',6.99,'public/img/llavero_polipiel.jpg','Unisex',1],
    [17,'Neceser Hombre','Neceser personalizado con nombre e inicial.',12.99,'public/img/neceser_hombre.jpg','Hombre',1],
    [18,'Pulsera Perlas Inicial','Pulsera de perlas con inicial y tarjetón.',9.50,'public/img/pulsera_perlas_inicial.jpg','Eventos',1],
    [19,'Neceser Mujer','Neceser personalizado de fieltro reciclado.',11.50,'public/img/neceser_mujer.jpg','Mujer',1],
    [21,'Tres en Raya','Juego de tres en raya personalizado.',10.99,'public/img/tres_en_raya.jpg','Niños',1],
    [22,'Trompos de Colores','Trompos de madera personalizados con el nombre de cada niño.',5.99,'public/img/trompos.jpg','Niños',1],
    [23,'Vasos 250ml','Vasos de 250 ml personalizados a tu gusto.',4.99,'public/img/vasos_250.jpg','Niños',1],
];
foreach($prods as $p){
    [$id,$n,$d,$pr,$img,$cat,$pers]=$p;
    $c=$conn->prepare("SELECT id FROM productos WHERE id=?");
    $c->bind_param("i",$id);$c->execute();$c->store_result();
    if($c->num_rows===0){
        $conn->query("INSERT INTO productos(id,nombre,descripcion,precio,imagen,categoria,personalizable) VALUES($id,'".addslashes($n)."','".addslashes($d)."',$pr,'$img','$cat',$pers)");
        echo "Producto '$n' insertado.<br>";
    }
    $c->close();
}
echo "<br><strong>✓ Todo listo. <a href='index.php'>Ir a la tienda</a></strong>";
$conn->close();
