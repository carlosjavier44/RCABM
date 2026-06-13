<?php
class Producto {
    private $conn;
    public function __construct($c) { $this->conn = $c; }

    public function obtenerProductos($termino='', $categoria='', $orden='') {
        $sql = "SELECT * FROM productos WHERE 1";
        if (!empty($termino))    $sql .= " AND (nombre LIKE ? OR descripcion LIKE ?)";
        if (!empty($categoria))  $sql .= " AND categoria = ?";
        if ($orden==='precio_asc')      $sql .= " ORDER BY precio ASC";
        elseif ($orden==='precio_desc') $sql .= " ORDER BY precio DESC";
        elseif ($orden==='nuevos')      $sql .= " ORDER BY id DESC";
        elseif ($orden==='antiguos')    $sql .= " ORDER BY id ASC";
        $stmt = $this->conn->prepare($sql);
        $pt = ''; $pa = [];
        if (!empty($termino))   { $l='%'.$termino.'%'; $pt.='ss'; $pa[]=&$l; $pa[]=&$l; }
        if (!empty($categoria)) { $pt.='s'; $pa[]=&$categoria; }
        if (!empty($pa)) $stmt->bind_param($pt,...$pa);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function obtenerTodosLosProductos() {
        return $this->conn->query("SELECT * FROM productos ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
    }
    public function obtenerProductoPorId($id) {
        $stmt = $this->conn->prepare("SELECT * FROM productos WHERE id=?");
        $stmt->bind_param('i',$id); $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    public function agregarProducto($nombre,$desc,$precio,$cat,$img,$pers=0,$campos='') {
        $stmt = $this->conn->prepare("INSERT INTO productos (nombre,descripcion,precio,categoria,imagen,personalizable,campos_personalizacion) VALUES (?,?,?,?,?,?,?)");
        $stmt->bind_param('ssdssis',$nombre,$desc,$precio,$cat,$img,$pers,$campos);
        return $stmt->execute();
    }
    public function actualizarProducto($id,$nombre,$desc,$precio,$cat,$img='',$pers=0,$campos='') {
        if (!empty($img)) {
            $stmt = $this->conn->prepare("UPDATE productos SET nombre=?,descripcion=?,precio=?,categoria=?,imagen=?,personalizable=?,campos_personalizacion=? WHERE id=?");
            $stmt->bind_param('ssdssisi',$nombre,$desc,$precio,$cat,$img,$pers,$campos,$id);
        } else {
            $stmt = $this->conn->prepare("UPDATE productos SET nombre=?,descripcion=?,precio=?,categoria=?,personalizable=?,campos_personalizacion=? WHERE id=?");
            $stmt->bind_param('ssdsisi',$nombre,$desc,$precio,$cat,$pers,$campos,$id);
        }
        return $stmt->execute();
    }
    public function eliminarProducto($id) {
        $stmt = $this->conn->prepare("DELETE FROM productos WHERE id=?");
        $stmt->bind_param('i',$id); return $stmt->execute();
    }
}
