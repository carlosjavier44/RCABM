<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$conn = new mysqli("localhost", "root", "", "mi_tienda");
if ($conn->connect_error) {
    $_SESSION['error_login'] = "Error de conexión.";
    header('Location: /RCABM/?view=login');
    exit();
}

if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    session_destroy();
    header('Location: /RCABM/?view=login');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if ($_POST['accion'] === 'register') {
        $nombre = $conn->real_escape_string($_POST["nombre"] ?? '');
        $email = $conn->real_escape_string($_POST["email"] ?? '');
        $contraseña = $_POST["contraseña"] ?? '';
        $confirmar = $_POST["confirmar_contraseña"] ?? '';

        // Verificar que las contraseñas coincidan
        if ($contraseña !== $confirmar) {
            $_SESSION['error_register'] = "Las contraseñas no coinciden.";
            header('Location: /RCABM/?view=register');
            exit();
        }

        // Validación de contraseña
        if (strlen($contraseña) < 8 ||
            !preg_match('/[A-Z]/', $contraseña) ||
            !preg_match('/[0-9]/', $contraseña) ||
            !preg_match('/[\W]/', $contraseña)) {
            $_SESSION['error_register'] = "La contraseña debe tener al menos 8 caracteres, una mayúscula, un número y un símbolo.";
            header('Location: /RCABM/?view=register');
            exit();
        }

        // Verificar si el correo ya existe
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $_SESSION['error_register'] = "El correo electrónico ya está registrado.";
            $stmt->close();
            header('Location: /RCABM/?view=register');
            exit();
        }
        $stmt->close();

        // Insertar nuevo usuario
        $hash = password_hash($contraseña, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, contraseña) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $email, $hash);

        if ($stmt->execute()) {
            $_SESSION['registro_exitoso'] = "Registro completado. Ya puedes iniciar sesión.";
            header('Location: /RCABM/?view=login');
        } else {
            $_SESSION['error_register'] = "Error al registrar usuario.";
            header('Location: /RCABM/?view=register');
        }

        $stmt->close();
        $conn->close();
        exit();

    } elseif ($_POST['accion'] === 'login') {
        $email = $conn->real_escape_string($_POST["email"] ?? '');
        $contraseña = $_POST["contraseña"] ?? '';

        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($usuario = $resultado->fetch_assoc()) {
            if (password_verify($contraseña, $usuario['contraseña'])) {
                $_SESSION['usuario'] = [
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'],
                    'email' => $usuario['email'],
                    'rol' => $usuario['rol']
                ];
                header('Location: /RCABM/?view=productos');
                exit();
            } else {
                $_SESSION['error_login'] = "Email o contraseña incorrectos";
                header('Location: /RCABM/?view=login');
                exit();
            }
        } else {
            $_SESSION['error_login'] = "Email o contraseña incorrectos";
            header('Location: /RCABM/?view=login');
            exit();
        }

        $stmt->close();
        $conn->close();

    } elseif ($_POST['accion'] === 'logout') {
        session_destroy();
        header('Location: /RCABM/?view=login');
        exit();
    }
}
?>
