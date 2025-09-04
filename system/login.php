<?php
require_once 'includes/db.php';
session_start();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = htmlspecialchars(trim($_POST['username']));
    $password = $_POST['password'];

    $stmt = $db->prepare("SELECT * FROM usuarios WHERE username = :username");
    $stmt->bindValue(':username', $username, SQLITE3_TEXT);
    $result = $stmt->execute();
    $user = $result->fetchArray(SQLITE3_ASSOC);

    $fecha = date('Y-m-d H:i:s');

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['username'] = $user['username'];
        $_SESSION['rol'] = $user['rol'];
        $_SESSION['id_usuario'] = $user['id'];

        // Registrar acceso exitoso en auditoría
        $log = $db->prepare("INSERT INTO auditoria (usuario, accion, fecha, descripcion) VALUES (:usuario, 'login', :fecha, 'Inicio de sesión exitoso')");
        $log->bindValue(':usuario', $username, SQLITE3_TEXT);
        $log->bindValue(':fecha', $fecha, SQLITE3_TEXT);
        $log->execute();

        header('Location: dashboard.php');
        exit;
    } else {
        // Registrar intento fallido
        $log = $db->prepare("INSERT INTO auditoria (usuario, accion, fecha, descripcion) VALUES (:usuario, 'login fallido', :fecha, 'Intento de inicio de sesión fallido')");
        $log->bindValue(':usuario', $username, SQLITE3_TEXT);
        $log->bindValue(':fecha', $fecha, SQLITE3_TEXT);
        $log->execute();

        $error = "Usuario o contraseña incorrectos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap');
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Nunito', sans-serif;
            background: linear-gradient(to right, #1CB5E0, #000851);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background-color: #fff;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
        }
        h2 {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #222;
        }
        input {
            width: 100%;
            padding: 0.75rem;
            margin-bottom: 1rem;
            border: 1px solid #ccc;
            border-radius: 8px;
            font-size: 1rem;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background-color: #1CB5E0;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #1489b0;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 1rem;
        }
        .register-link {
            text-align: center;
            margin-top: 1rem;
        }
        .register-link a {
            color: #1CB5E0;
            text-decoration: none;
        }
        .register-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Iniciar Sesión</h2>
        <?php if ($error): ?>
            <p class="error"><?php echo $error; ?></p>
        <?php endif; ?>
        <form method="post">
            <input type="text" name="username" placeholder="Usuario" required>
            <input type="password" name="password" placeholder="Contraseña" required>
            <button type="submit">Entrar</button>
        </form>
        <p class="register-link">¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
    </div>
</body>
</html>
