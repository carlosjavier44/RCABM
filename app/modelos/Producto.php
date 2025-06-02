<?php

class Producto {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    public function obtenerProductos($termino = '', $categoria = '', $orden = '') {
        $sql = "SELECT * FROM productos WHERE 1";

        if (!empty($termino)) {
            $sql .= " AND (nombre LIKE ? OR descripcion LIKE ?)";
        }

        if (!empty($categoria)) {
            $sql .= " AND categoria = ?";
        }

        if ($orden === 'precio_asc') {
            $sql .= " ORDER BY precio ASC";
        } elseif ($orden === 'precio_desc') {
            $sql .= " ORDER BY precio DESC";
        } elseif ($orden === 'nuevos') {
            $sql .= " ORDER BY id DESC";
        } elseif ($orden === 'antiguos') {
            $sql .= " ORDER BY id ASC";
        }

        $stmt = $this->conn->prepare($sql);

        $paramTypes = '';
        $params = [];

        if (!empty($termino)) {
            $like = '%' . $termino . '%';
            $paramTypes .= 'ss';
            $params[] = &$like;
            $params[] = &$like;
        }

        if (!empty($categoria)) {
            $paramTypes .= 's';
            $params[] = &$categoria;
        }

        if (!empty($params)) {
            $stmt->bind_param($paramTypes, ...$params);
        }

        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerTodosLosProductos() {
        $sql = "SELECT * FROM productos ORDER BY id DESC";
        $result = $this->conn->query($sql);
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function obtenerProductoPorId($id) {
        $sql = "SELECT * FROM productos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    public function agregarProducto($nombre, $descripcion, $precio, $categoria, $stock, $imagen) {
        $sql = "INSERT INTO productos (nombre, descripcion, precio, categoria, stock, imagen) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('ssdsss', $nombre, $descripcion, $precio, $categoria, $stock, $imagen);
        return $stmt->execute();
    }

    public function actualizarProducto($id, $nombre, $descripcion, $precio, $categoria, $stock, $imagen = '') {
        if (!empty($imagen)) {
            $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, categoria = ?, stock = ?, imagen = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ssdsssi', $nombre, $descripcion, $precio, $categoria, $stock, $imagen, $id);
        } else {
            $sql = "UPDATE productos SET nombre = ?, descripcion = ?, precio = ?, categoria = ?, stock = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param('ssdssi', $nombre, $descripcion, $precio, $categoria, $stock, $id);
        }
        return $stmt->execute();
    }

    public function eliminarProducto($id) {
        $sql = "DELETE FROM productos WHERE id = ?";
        $stmt = $this->conn->prepare($sql);
        $stmt->bind_param('i', $id);
        return $stmt->execute();
    }
}
