<?php
class Chat {
    private $conn;

    public function __construct($conexion) {
        $this->conn = $conexion;
    }

    // Obtener mensajes entre dos usuarios con paginación
    public function obtenerMensajes($usuarioId1, $usuarioId2, $limit = 100, $offset = 0) {
        $stmt = $this->conn->prepare("
            SELECT m.*, u.nombre as emisor_nombre 
            FROM mensajes m
            JOIN usuarios u ON m.emisor_id = u.id
            WHERE (m.emisor_id = ? AND m.receptor_id = ?)
               OR (m.emisor_id = ? AND m.receptor_id = ?)
            ORDER BY m.fecha DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iiiiii", $usuarioId1, $usuarioId2, $usuarioId2, $usuarioId1, $limit, $offset);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    // Enviar mensaje con validación
    public function enviarMensaje($emisorId, $receptorId, $mensaje) {
        if (empty(trim($mensaje))) return false;
        
        $mensaje = strip_tags($mensaje); // Basic sanitization
        $stmt = $this->conn->prepare("INSERT INTO mensajes (emisor_id, receptor_id, mensaje) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $emisorId, $receptorId, $mensaje);
        return $stmt->execute(); 
    }

    // Obtener conversaciones para el admin
    public function obtenerConversacionesAdmin($adminId = 1) {
        $stmt = $this->conn->prepare("
            SELECT u.id, u.nombre, MAX(m.fecha) as ultima_fecha,
                   (SELECT mensaje FROM mensajes 
                    WHERE (emisor_id = u.id AND receptor_id = ?) 
                       OR (emisor_id = ? AND receptor_id = u.id)
                    ORDER BY fecha DESC LIMIT 1) as ultimo_mensaje
            FROM usuarios u
            JOIN mensajes m ON (u.id = m.emisor_id OR u.id = m.receptor_id)
            WHERE u.rol = 'cliente' AND u.id != ? AND (m.emisor_id = ? OR m.receptor_id = ?)
            GROUP BY u.id, u.nombre
            ORDER BY ultima_fecha DESC
        ");
        $stmt->bind_param("iiiii", $adminId, $adminId, $adminId, $adminId, $adminId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    // Marcar mensajes como leídos
    public function marcarComoLeidos($emisorId, $receptorId) {
        $stmt = $this->conn->prepare("
            UPDATE mensajes SET leido = 1 
            WHERE emisor_id = ? AND receptor_id = ? AND leido = 0
        ");
        $stmt->bind_param("ii", $emisorId, $receptorId);
        return $stmt->execute();
    }
}
?>