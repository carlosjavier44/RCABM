<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../modelos/Chat.php';

header('Content-Type: application/json');

if (!isset($_SESSION['usuario']['id'])) {
    echo json_encode(['success'=>false,'error'=>'No autenticado']); exit;
}

$uid     = $_SESSION['usuario']['id'];
$adminId = 1;
$chat    = new Chat($conn);
$action  = $_GET['action'] ?? ($_POST['action'] ?? '');

if ($action === 'no_leidos') {
    echo json_encode(['total'=>$chat->noLeidosPara($uid)]); exit;
}

if ($action === 'obtener') {
    $chat->marcarLeidos($adminId, $uid);  // admin→user: marcar leídos
    $chat->marcarVistos($uid, $adminId);  // user→admin: marcar vistos si admin los leyó
    $msgs = $chat->getMensajes($uid, $adminId);
    echo json_encode($msgs); exit;
}

if ($action === 'enviar' && $_SERVER['REQUEST_METHOD']==='POST') {
    $msg = trim($_POST['mensaje'] ?? '');
    if ($msg==='') { echo json_encode(['success'=>false,'error'=>'Vacío']); exit; }
    echo json_encode(['success'=>$chat->enviarMensaje($uid,$adminId,$msg)]);
    exit;
}

echo json_encode(['success'=>false,'error'=>'Acción no válida']);
