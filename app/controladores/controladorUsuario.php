<?php
require_once __DIR__ . '/../../config/config.php';

if (isset($_POST['accion'])) {

    if ($_POST['accion'] === 'login') {
        $email     = trim($_POST['email'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email=?");
        $stmt->bind_param("s",$email); $stmt->execute();
        $u = $stmt->get_result()->fetch_assoc();
        if ($u && password_verify($contrasena, $u['contrasena'])) {
            $_SESSION['usuario'] = ['id'=>$u['id'],'nombre'=>$u['nombre'],'email'=>$u['email'],'rol'=>$u['rol']];
            header('Location: /RCABM/index.php'); exit;
        } else {
            $_SESSION['error_login'] = "Email o contraseña incorrectos.";
            header('Location: /RCABM/index.php?view=login'); exit;
        }
    }

    if ($_POST['accion'] === 'register') {
        $nombre    = trim($_POST['nombre'] ?? '');
        $email     = trim($_POST['email'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';
        $confirmar  = $_POST['confirmar_contrasena'] ?? '';
        if ($contrasena !== $confirmar) {
            $_SESSION['error_register'] = "Las contraseñas no coinciden.";
            header('Location: /RCABM/index.php?view=register'); exit;
        }
        if (strlen($contrasena)<8 || !preg_match('/[A-Z]/',$contrasena) || !preg_match('/[0-9]/',$contrasena) || !preg_match('/[\W]/',$contrasena)) {
            $_SESSION['error_register'] = "La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un símbolo.";
            header('Location: /RCABM/index.php?view=register'); exit;
        }
        $chk = $conn->prepare("SELECT id FROM usuarios WHERE email=?");
        $chk->bind_param("s",$email); $chk->execute(); $chk->store_result();
        if ($chk->num_rows > 0) {
            $_SESSION['error_register'] = "El correo ya está registrado.";
            header('Location: /RCABM/index.php?view=register'); exit;
        }
        $hash = password_hash($contrasena, PASSWORD_DEFAULT);
        $ins  = $conn->prepare("INSERT INTO usuarios (nombre,email,contrasena) VALUES (?,?,?)");
        $ins->bind_param("sss",$nombre,$email,$hash);
        if ($ins->execute()) {
            $_SESSION['mensaje'] = "Cuenta creada. Ya puedes iniciar sesión.";
            header('Location: /RCABM/index.php?view=login'); exit;
        }
    }

    if ($_POST['accion'] === 'logout') {
        session_destroy();
        header('Location: /RCABM/index.php'); exit;
    }
}
header('Location: /RCABM/index.php'); exit;
