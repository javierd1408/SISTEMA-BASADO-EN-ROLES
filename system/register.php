<?php
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];
    $role = $_POST['rol'];

    if (!preg_match('/^[a-zA-Z0-9_]{4,20}$/', $username)) {
        $msg = "El nombre de usuario debe tener entre 4 y 20 caracteres alfanuméricos.";
    } elseif (strlen($password) < 6) {
        $msg = "La contraseña debe tener al menos 6 caracteres.";
    } else {
        $checkStmt = $db->prepare("SELECT COUNT(*) as total FROM usuarios WHERE username = :username");
        $checkStmt->bindValue(':username', $username, SQLITE3_TEXT);
        $exists = $checkStmt->execute()->fetchArray(SQLITE3_ASSOC)['total'];

        if ($exists > 0) {
            $msg = "Ese nombre de usuario ya existe.";
        } else {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("INSERT INTO usuarios (username, password, rol) VALUES (:username, :password, :rol)");
            $stmt->bindValue(':username', $username, SQLITE3_TEXT);
            $stmt->bindValue(':password', $hashedPassword, SQLITE3_TEXT);
            $stmt->bindValue(':rol', $role, SQLITE3_TEXT);
            $result = $stmt->execute();

            if ($result) {
                $msg = "Usuario registrado correctamente.";

                // Insertar en tabla auditoria
                $accion = "Registro de nuevo usuario";
                $descripcion = "Se registró el usuario $username con rol $role";
                $log = $db->prepare("INSERT INTO auditoria (usuario, accion, descripcion) VALUES (:usuario, :accion, :descripcion)");
                $log->bindValue(':usuario', $username, SQLITE3_TEXT);
                $log->bindValue(':accion', $accion, SQLITE3_TEXT);
                $log->bindValue(':descripcion', $descripcion, SQLITE3_TEXT);
                $log->execute();
            } else {
                $msg = "Error al registrar el usuario.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Quicksand:wght@400;600&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Quicksand', sans-serif;
            background: linear-gradient(to right, #00c6ff, #0072ff);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .container {
            background: #fff;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 500px;
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 1rem;
        }
        form input, form select {
            width: 100%;
            padding: 12px;
            margin-bottom: 15px;
            border-radius: 8px;
            border: 1px solid #ccc;
            font-size: 1rem;
        }
        form button {
            width: 100%;
            background-color: #0072ff;
            color: white;
            padding: 12px;
            font-size: 1rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }
        form button:hover {
            background-color: #005edb;
        }
        .message {
            text-align: center;
            color: #c0392b;
            margin-bottom: 15px;
        }
        .login-link {
            text-align: center;
            font-size: 0.9rem;
            margin-top: 1rem;
        }
        .login-link a {
            color: #0072ff;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Registro de Usuario</h2>
        <?php if (isset($msg)) echo "<p class='message'>$msg</p>"; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Nombre de usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <select name="rol">
                <option value="user">Usuario</option>
                <option value="admin">Administrador</option>
            </select>
            <button type="submit">Registrarse</button>
        </form>
        <div class="login-link">
            <p>¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
        </div>
    </div>
</body>
</html>
