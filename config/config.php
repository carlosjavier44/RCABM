<?php

define('BASE_URL', '/proyecto');

$host = "localhost";
$usuario = "root";
$contraseña = "";
$nombre_bd = "mi_tienda";

$conn = new mysqli($host, $usuario, $contraseña, $nombre_bd);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
