<?php

class Producto {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function obtenerProductos() {
        $sql = "SELECT * FROM productos";
        $result = $this->conn->query($sql);
        $productos = [];

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $productos[] = $row;
            }
        }

        return $productos;
    }
}
?>