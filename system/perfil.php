<?php
session_start();
require_once 'includes/db.php';

// Verificar que el usuario ha iniciado sesión
if (!isset($_SESSION['username'])) {
    header('Location: login.php');
    exit;
}

// Obtener el nombre de usuario desde la sesión
$username = $_SESSION['username'];

// Consultar datos del usuario
$stmt = $db->prepare("SELECT * FROM usuarios WHERE username = :username");
$stmt->bindValue(':username', $username, SQLITE3_TEXT);
$result = $stmt->execute();
$user = $result->fetchArray(SQLITE3_ASSOC);

if (!$user) {
    die("Usuario no encontrado.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Perfil de Usuario</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            padding: 20px;
        }
        .perfil {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            max-width: 500px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 {
            color: #333;
            margin-bottom: 20px;
        }
        p {
            font-size: 18px;
        }
        .volver {
            display: inline-block;
            margin-top: 20px;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }
        .volver:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="perfil">
        <h2>Perfil de Usuario</h2>
        <p><strong>Nombre de usuario:</strong> <?= htmlspecialchars($user['username']) ?></p>
        <p><strong>Rol:</strong> <?= htmlspecialchars($user['rol']) ?></p>

        <a href="dashboard.php" class="volver">Volver al Dashboard</a>
    </div>
</body>
</html>
