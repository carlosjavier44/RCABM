<?php
// app/modelos/Chat.php

require_once __DIR__ . '/../../config/config.php';

class Chat {
    private $conn;

    public function __construct() {
        $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($this->conn->connect_error) {
            die("Conexión fallida: " . $this->conn->connect_error);
        }
        $this->conn->set_charset("utf8");
    }

    public function enviarMensaje($emisor_id, $receptor_id, $mensaje) {
        $stmt = $this->conn->prepare("INSERT INTO mensajes (emisor_id, receptor_id, mensaje) VALUES (?, ?, ?)");
        if (!$stmt) {
            error_log("Error prepare: " . $this->conn->error);
            return false;
        }
        $stmt->bind_param("iis", $emisor_id, $receptor_id, $mensaje);
        $result = $stmt->execute();
        if (!$result) {
            error_log("Error execute: " . $stmt->error);
        }
        $stmt->close();
        return $result;
    }

    public function obtenerMensajesEntre($usuario1, $usuario2) {
        $stmt = $this->conn->prepare("SELECT * FROM mensajes WHERE (emisor_id = ? AND receptor_id = ?) OR (emisor_id = ? AND receptor_id = ?) ORDER BY fecha ASC");
        $stmt->bind_param("iiii", $usuario1, $usuario2, $usuario2, $usuario1);
        $stmt->execute();
        $result = $stmt->get_result();
        $mensajes = [];
        while ($row = $result->fetch_assoc()) {
            $mensajes[] = $row;
        }
        $stmt->close();
        return $mensajes;
    }

    public function obtenerUsuariosConChat() {
        // Devuelve lista de usuarios distintos que han enviado mensajes al admin o recibidos
        $stmt = $this->conn->prepare("SELECT DISTINCT u.id, u.nombre FROM usuarios u JOIN mensajes m ON (u.id = m.emisor_id OR u.id = m.receptor_id) WHERE u.rol = 'cliente'");
        $stmt->execute();
        $result = $stmt->get_result();
        $usuarios = [];
        while ($row = $result->fetch_assoc()) {
            $usuarios[] = $row;
        }
        $stmt->close();
        return $usuarios;
    }
}
