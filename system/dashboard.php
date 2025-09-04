<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['rol'])) {
    header('Location: login.php');
    exit();
}


$username = htmlspecialchars($_SESSION['username']);
$rol = $_SESSION['rol'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
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
            text-align: center;
            width: 90%;
            max-width: 600px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.2);
        }
        h1 {
            font-size: 28px;
            margin-bottom: 10px;
        }
        .rol {
            font-weight: bold;
            color: #f1c40f;
        }
        .btn {
            display: block;
            margin: 15px auto;
            padding: 12px 20px;
            background-color: #1abc9c;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: background-color 0.3s ease;
            width: 80%;
            max-width: 300px;
        }
        .btn:hover {
            background-color: #16a085;
        }
        .logout {
            margin-top: 20px;
            background-color: crimson;
        }
        .logout:hover {
            background-color: #b30000;
        }
    </style>
</head>
<body>
    <div class="panel">
        <h1>¡Hola, <span class="rol"><?php echo $username; ?></span>!</h1>
        <p>Has iniciado sesión como <strong class="rol"><?php echo $rol === 'admin' ? 'Administrador' : 'Usuario'; ?></strong>.</p>

        <?php if ($rol === 'admin'): ?>
            <a class="btn" href="admin.php">Panel de Administración</a>
            <a class="btn" href="empleados.php">Gestión de Empleados</a>
            <a class="btn" href="departamento.php">Gestión de Departamentos</a>
            <a class="btn" href="auditoria.php">Auditoría del Sistema</a>
            <a class="btn" href="proyectos.php">Gestión de Proyectos</a> <!-- Nuevo botón -->
        <?php else: ?>
            <a class="btn" href="perfil.php">Ver mi Perfil</a>
            <a class="btn" href="mis_proyectos.php">Mis Proyectos</a>
            <a class="btn" href="soporte.php">Soporte Técnico</a>
        <?php endif; ?>

        <a class="btn logout" href="logout.php">Cerrar Sesión</a>
    </div>
</body>
</html>



