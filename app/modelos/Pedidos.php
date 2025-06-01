<?php

class Pedidos {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    public function crearPedido($usuario_id, $productos) {
        $stmt = $this->conn->prepare("INSERT INTO pedidos (usuario_id, estado) VALUES (?, 'pagado')");
        $stmt->bind_param("i", $usuario_id);
        if ($stmt->execute()) {
            $pedido_id = $stmt->insert_id;
            $stmt->close();

            $stmt_item = $this->conn->prepare("INSERT INTO pedido_productos (pedido_id, producto_id, cantidad, precio) VALUES (?, ?, ?, ?)");
            foreach ($productos as $producto) {
                $stmt_item->bind_param("iiid", $pedido_id, $producto['producto_id'], $producto['cantidad'], $producto['precio']);
                $stmt_item->execute();
            }
            $stmt_item->close();

            return $pedido_id;
        }
        return false;
    }
}
?>
