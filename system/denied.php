<?php
session_start();

$loggedIn = isset($_SESSION['username'], $_SESSION['rol']);
$username = $loggedIn ? htmlspecialchars($_SESSION['username']) : null;
$rol = $loggedIn ? $_SESSION['rol'] : null;

$reason = $_GET['reason'] ?? ''; // Puede venir 'rol', 'login', o vacío
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Acceso Denegado</title>
<style>
    body {
        margin: 0;
        padding: 0;
        font-family: 'Poppins', sans-serif;
        background: linear-gradient(135deg, #ff4e50, #f9d423);
        min-height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }
    .card {
        background: #fff;
        padding: 2rem;
        border-radius: 16px;
        box-shadow: 0 8px 24px rgba(0,0,0,.2);
        max-width: 420px;
        width: 90%;
        text-align: center;
    }
    h1 {
        color: #c00;
        margin-bottom: 1rem;
    }
    p {
        color: #555;
        margin-bottom: 1.5rem;
    }
    .btn {
        display: inline-block;
        margin: 0.25rem;
        padding: 0.75rem 1.25rem;
        border-radius: 8px;
        text-decoration: none;
        font-size: 1rem;
        cursor: pointer;
        transition: 0.2s;
        user-select: none;
    }
    .btn-home {
        background: #4a90e2;
        color: #fff;
    }
    .btn-home:hover {
        background: #357ABD;
    }
    .btn-login {
        background: #28a745;
        color: #fff;
    }
    .btn-login:hover {
        background: #218838;
    }
</style>
</head>
<body>
<div class="card">
    <h1>Acceso Denegado</h1>
    <?php if ($reason === 'rol' && $loggedIn): ?>
        <p>Lo sentimos, <strong><?= $username ?></strong>. Tu rol (<strong><?= $rol ?></strong>) no tiene permiso para ver esta página.</p>
    <?php elseif ($reason === 'login' && !$loggedIn): ?>
        <p>Debes iniciar sesión para acceder a esta página.</p>
    <?php else: ?>
        <p>No tienes acceso a este recurso.</p>
    <?php endif; ?>

    <?php if ($loggedIn): ?>
        <a class="btn btn-home" href="dashboard.php">Volver al Panel</a>
        <a class="btn btn-home" href="home.php">Inicio</a>
    <?php else: ?>
        <a class="btn btn-login" href="login.php">Iniciar Sesión</a>
        <a class="btn btn-home" href="home.php">Inicio</a>
    <?php endif; ?>
</div>
</body>
</html>
