<?php
require_once 'app/modelos/Producto.php';

class ControladorProducto {
    private $productoModel;

    public function __construct($conn) {
        $this->productoModel = new Producto($conn);
    }

    public function mostrarProductos() {
        // Recogemos los parámetros de GET
        $termino = $_GET['q'] ?? '';
        $categoria = $_GET['categoria'] ?? '';
        $orden = $_GET['orden'] ?? '';

        // Obtener productos filtrados
        $productos = $this->productoModel->obtenerProductos($termino, $categoria, $orden);

        // Cargar la vista pasando productos y filtros para mantenerlos
        require 'app/vistas/productos/productos.php';
    }
}
