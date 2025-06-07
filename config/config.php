<?php

define('BASE_URL', '/RCABM');

$host = "localhost";
$usuario = "root";
$contrasena = "";
$nombre_bd = "mi_tienda";

$conn = new mysqli($host, $usuario, $contrasena, $nombre_bd);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
