<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../modelos/Chat.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol']!=='admin') {
    echo json_encode(['error'=>'Acceso denegado']); exit;
}

$adminId = $_SESSION['usuario']['id'];
$chat    = new Chat($conn);

if (isset($_GET['total_no_leidos'])) {
    echo json_encode(['total'=>$chat->noLeidosPara($adminId)]); exit;
}

if (isset($_GET['listar_usuarios'])) {
    echo json_encode($chat->conversacionesAdmin($adminId)); exit;
}

if (isset($_GET['usuario_id'])) {
    $uid = (int)$_GET['usuario_id'];
    $chat->marcarLeidos($uid, $adminId);   // user→admin: marcar leídos
    $chat->marcarVistos($adminId, $uid);   // admin→user: marcar vistos si user los leyó
    $msgs = $chat->getMensajes($adminId, $uid);
    // Añadir campo 'emisor' para el JS
    $adminNombre = $_SESSION['usuario']['nombre'];
    foreach ($msgs as &$m) {
        $m['emisor'] = ($m['emisor_id']==$adminId) ? 'Admin' : $m['emisor_nombre'];
    }
    echo json_encode($msgs); exit;
}

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['mensaje'],$_POST['usuario_id'])) {
    $msg = trim($_POST['mensaje']);
    $rid = (int)$_POST['usuario_id'];
    if ($msg==='') { echo json_encode(['success'=>false]); exit; }
    echo json_encode(['success'=>$chat->enviarMensaje($adminId,$rid,$msg)]);
    exit;
}

echo json_encode(['success'=>false,'error'=>'Solicitud inválida']);
