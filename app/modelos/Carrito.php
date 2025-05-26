<?php
class Carrito {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    // Añadir un producto al carrito
    public function añadirProducto($usuario_id, $producto_id, $cantidad) {
        // Verificar si el producto ya está en el carrito
        $sql = "SELECT * FROM carrito WHERE usuario_id = ? AND producto_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $usuario_id, $producto_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Si el producto ya está en el carrito, actualizar la cantidad
            $sql = "UPDATE carrito SET cantidad = cantidad + ? WHERE usuario_id = ? AND producto_id = ?";
        } else {
            // Si no está en el carrito, insertar un nuevo registro
            $sql = "INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $cantidad, $usuario_id, $producto_id);
        return $stmt->execute();
    }
}
?>