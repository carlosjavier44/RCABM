<?php
class Carrito {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function añadirProducto($usuario_id, $producto_id, $cantidad = 1) {
        $stmt = $this->conn->prepare("SELECT cantidad FROM carrito WHERE usuario_id = ? AND producto_id = ?");
        $stmt->bind_param("ii", $usuario_id, $producto_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $nuevaCantidad = $row['cantidad'] + $cantidad;
            $stmt = $this->conn->prepare("UPDATE carrito SET cantidad = ? WHERE usuario_id = ? AND producto_id = ?");
            $stmt->bind_param("iii", $nuevaCantidad, $usuario_id, $producto_id);
            return $stmt->execute();
        } else {
            $stmt = $this->conn->prepare("INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)");
            $stmt->bind_param("iii", $usuario_id, $producto_id, $cantidad);
            return $stmt->execute();
        }
    }

    public function obtenerProductos($usuario_id) {
        $stmt = $this->conn->prepare(
            "SELECT c.producto_id, p.nombre, p.precio, c.cantidad 
             FROM carrito c 
             JOIN productos p ON c.producto_id = p.id 
             WHERE c.usuario_id = ?"
        );
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $productos = [];
        while ($row = $result->fetch_assoc()) {
            $productos[] = $row;
        }
        return $productos;
    }

    public function actualizarCantidad($usuario_id, $producto_id, $cantidad) {
        if ($cantidad < 1) return false;
        $stmt = $this->conn->prepare("UPDATE carrito SET cantidad = ? WHERE usuario_id = ? AND producto_id = ?");
        $stmt->bind_param("iii", $cantidad, $usuario_id, $producto_id);
        return $stmt->execute();
    }

    public function eliminarProducto($usuario_id, $producto_id) {
        $stmt = $this->conn->prepare("DELETE FROM carrito WHERE usuario_id = ? AND producto_id = ?");
        $stmt->bind_param("ii", $usuario_id, $producto_id);
        return $stmt->execute();
    }

    public function vaciarCarrito($usuario_id) {
        $stmt = $this->conn->prepare("DELETE FROM carrito WHERE usuario_id = ?");
        $stmt->bind_param("i", $usuario_id);
        return $stmt->execute();
    }
}
