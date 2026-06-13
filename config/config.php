<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
$conn = new mysqli("localhost", "root", "", "mi_tienda");
if ($conn->connect_error) die("Error de conexión: " . $conn->connect_error);
$conn->set_charset("utf8mb4");
