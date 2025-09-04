<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['rol'] !== 'user') {
    header('Location: login.php');
    exit();
}

$username = htmlspecialchars($_SESSION['username']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Soporte Técnico</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }
        body {
            margin: 0;
            background: linear-gradient(135deg, #2c3e50, #3498db);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: white;
        }
        .panel {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px;
            border-radius: 20px;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
            text-align: center;
        }
        h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        p {
            font-size: 16px;
            line-height: 1.5;
        }
        .btn {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 20px;
            background-color: #1abc9c;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease;
        }
        .btn:hover {
            background-color: #16a085;
        }
    </style>
</head>
<body>
    <div class="panel">
        <h1>Centro de Soporte Técnico</h1>
        <p>
            ¡Hola, <strong><?php echo $username; ?></strong>!<br><br>
            Estamos aquí para ayudarte. Nuestro equipo de soporte está comprometido en brindarte una experiencia segura, fluida y sin complicaciones.<br><br>
            Si tienes dudas, problemas técnicos, o simplemente necesitas orientación, no dudes en comunicarte con nosotros. Tu bienestar digital es nuestra prioridad.
        </p>
        <a href="dashboard.php" class="btn">Volver al Panel</a>
    </div>
</body>
</html>