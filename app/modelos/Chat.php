<?php
class Chat {
    private $conn;
    public function __construct($c) { $this->conn = $c; }

    public function enviarMensaje($emisor,$receptor,$msg) {
        $s = $this->conn->prepare("INSERT INTO mensajes (emisor_id,receptor_id,mensaje) VALUES (?,?,?)");
        $s->bind_param("iis",$emisor,$receptor,$msg); return $s->execute();
    }
    public function getMensajes($uid1,$uid2) {
        $s = $this->conn->prepare("SELECT m.*,u.nombre AS emisor_nombre FROM mensajes m JOIN usuarios u ON m.emisor_id=u.id WHERE (emisor_id=? AND receptor_id=?) OR (emisor_id=? AND receptor_id=?) ORDER BY fecha ASC");
        $s->bind_param("iiii",$uid1,$uid2,$uid2,$uid1); $s->execute();
        return $s->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    public function marcarLeidos($emisor,$receptor) {
        $s = $this->conn->prepare("UPDATE mensajes SET leido=1 WHERE emisor_id=? AND receptor_id=? AND leido=0");
        $s->bind_param("ii",$emisor,$receptor); return $s->execute();
    }
    public function marcarVistos($emisor,$receptor) {
        $s = $this->conn->prepare("UPDATE mensajes SET visto=1 WHERE emisor_id=? AND receptor_id=? AND leido=1 AND visto=0");
        $s->bind_param("ii",$emisor,$receptor); return $s->execute();
    }
    public function noLeidosPara($receptor) {
        $s = $this->conn->prepare("SELECT COUNT(*) as t FROM mensajes WHERE receptor_id=? AND leido=0");
        $s->bind_param("i",$receptor); $s->execute();
        return (int)$s->get_result()->fetch_assoc()['t'];
    }
    public function conversacionesAdmin($adminId) {
        $s = $this->conn->prepare("SELECT u.id,u.nombre,(SELECT COUNT(*) FROM mensajes WHERE emisor_id=u.id AND receptor_id=? AND leido=0) AS no_leidos,(SELECT MAX(fecha) FROM mensajes WHERE (emisor_id=u.id AND receptor_id=?) OR (emisor_id=? AND receptor_id=u.id)) AS ultima FROM mensajes m JOIN usuarios u ON (u.id=m.emisor_id OR u.id=m.receptor_id) WHERE (m.emisor_id=? OR m.receptor_id=?) AND u.id!=? GROUP BY u.id,u.nombre ORDER BY ultima DESC");
        $s->bind_param("iiiiii",$adminId,$adminId,$adminId,$adminId,$adminId,$adminId);
        $s->execute(); return $s->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
