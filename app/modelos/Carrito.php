<?php
class Carrito {
    private $conn;
    public function __construct($c) { $this->conn = $c; }

    public function añadirProducto($uid, $pid, $cant=1, $obs='') {
        $chk = $this->conn->prepare("SELECT id,cantidad FROM carrito WHERE usuario_id=? AND producto_id=?");
        $chk->bind_param("ii",$uid,$pid); $chk->execute();
        $row = $chk->get_result()->fetch_assoc();
        if ($row) {
            $nq = $row['cantidad'] + $cant;
            $s = $this->conn->prepare("UPDATE carrito SET cantidad=?, observacion=? WHERE id=?");
            $s->bind_param("isi",$nq,$obs,$row['id']); return $s->execute();
        } else {
            $s = $this->conn->prepare("INSERT INTO carrito (usuario_id,producto_id,cantidad,observacion) VALUES (?,?,?,?)");
            $s->bind_param("iiis",$uid,$pid,$cant,$obs); return $s->execute();
        }
    }
    public function obtenerProductos($uid) {
        $s = $this->conn->prepare("SELECT c.*,p.nombre,p.precio,p.imagen,p.personalizable FROM carrito c JOIN productos p ON c.producto_id=p.id WHERE c.usuario_id=?");
        $s->bind_param("i",$uid); $s->execute();
        return $s->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function actualizarCantidad($uid,$pid,$cant) {
        $s = $this->conn->prepare("UPDATE carrito SET cantidad=? WHERE usuario_id=? AND producto_id=?");
        $s->bind_param("iii",$cant,$uid,$pid); return $s->execute();
    }
    public function actualizarObservacion($uid,$pid,$obs) {
        $s = $this->conn->prepare("UPDATE carrito SET observacion=? WHERE usuario_id=? AND producto_id=?");
        $s->bind_param("sii",$obs,$uid,$pid); return $s->execute();
    }
    public function eliminarProducto($uid,$pid) {
        $s = $this->conn->prepare("DELETE FROM carrito WHERE usuario_id=? AND producto_id=?");
        $s->bind_param("ii",$uid,$pid); return $s->execute();
    }
    public function vaciarCarrito($uid) {
        $s = $this->conn->prepare("DELETE FROM carrito WHERE usuario_id=?");
        $s->bind_param("i",$uid); return $s->execute();
    }
}
