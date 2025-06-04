<?php
require_once 'app/modelos/Producto.php';

class ControladorProducto {
    private $productoModel;

    public function __construct($conn) {
        $this->productoModel = new Producto($conn);
    }

    public function mostrarProductos() {
        $termino = $_GET['q'] ?? '';
        $categoria = $_GET['categoria'] ?? '';
        $orden = $_GET['orden'] ?? '';

        $productos = $this->productoModel->obtenerProductos($termino, $categoria, $orden);

        require 'app/vistas/productos/productos.php';
    }
}
