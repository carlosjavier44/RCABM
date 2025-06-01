<?php

class Producto {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Obtener productos con filtro opcional por término, categoría y orden.
     *
     * @param string $termino   Término de búsqueda en nombre (opcional).
     * @param string $categoria Categoría para filtrar (opcional).
     * @param string $orden     Orden de resultados (precio_asc, precio_desc, nuevos, antiguos).
     * @return array            Lista de productos.
     */
    public function obtenerProductos($termino = '', $categoria = '', $orden = '') {
        $sql = "SELECT * FROM productos WHERE stock > 0";
        
        $params = [];
        $types = '';
        
        if ($termino !== '') {
            $sql .= " AND nombre LIKE ?";
            $params[] = '%' . $termino . '%';
            $types .= 's';
        }

        if ($categoria !== '') {
            $sql .= " AND categoria = ?";
            $params[] = $categoria;
            $types .= 's';
        }

        // Mapeamos valores posibles de $orden a cláusulas SQL ORDER BY
        $ordenes_permitidos = [
            'precio_asc' => 'precio ASC',
            'precio_desc' => 'precio DESC',
            'nuevos' => 'fecha DESC',
            'antiguos' => 'fecha ASC'
        ];

        if (isset($ordenes_permitidos[$orden])) {
            $sql .= " ORDER BY " . $ordenes_permitidos[$orden];
        } else {
            $sql .= " ORDER BY id DESC"; // orden por defecto
        }

        if (!empty($params)) {
            $stmt = $this->conn->prepare($sql);
            if ($stmt === false) {
                die("Error en la consulta preparada: " . $this->conn->error);
            }
            $stmt->bind_param($types, ...$params);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $this->conn->query($sql);
        }

        $productos = [];
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $productos[] = $row;
            }
        }

        return $productos;
    }
}
?>
