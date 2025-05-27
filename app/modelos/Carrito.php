<?php
class Carrito {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function añadirProducto($usuario_id, $producto_id, $cantidad) {
        $sql = "SELECT * FROM carrito WHERE usuario_id = ? AND producto_id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("ii", $usuario_id, $producto_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $sql = "UPDATE carrito SET cantidad = cantidad + ? WHERE usuario_id = ? AND producto_id = ?";
        } else {
            $sql = "INSERT INTO carrito (usuario_id, producto_id, cantidad) VALUES (?, ?, ?)";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param("iii", $cantidad, $usuario_id, $producto_id);
        return $stmt->execute();
    }
}
?>