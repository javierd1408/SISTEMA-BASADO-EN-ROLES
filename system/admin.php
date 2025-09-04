<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['username']) || $_SESSION['rol'] !== 'admin') {
    header('Location: denied.php');
    exit();
}

$usuario = $_SESSION['username'];

// --- Obtener métricas ---
$total_empleados = $db->querySingle("SELECT COUNT(*) FROM empleado");
$total_tecnicos = $db->querySingle("SELECT COUNT(*) FROM empleado WHERE tipo_empleado = 'tecnico'");
$total_admins = $db->querySingle("SELECT COUNT(*) FROM empleado WHERE tipo_empleado = 'administrativo'");
$total_deptos = $db->querySingle("SELECT COUNT(*) FROM departamento");

// --- Auditoría del usuario actual ---
$acciones = $db->prepare("SELECT * FROM auditoria WHERE usuario = :usuario ORDER BY fecha DESC LIMIT 5");
$acciones->bindValue(':usuario', $usuario);
$acciones = $acciones->execute();

// --- Empleados por departamento ---
$depto_data = $db->query("SELECT d.nombre_depto, COUNT(e.num_matricula) as total FROM departamento d LEFT JOIN empleado e ON d.cod_depto = e.cod_depto GROUP BY d.cod_depto");
$depto_labels = [];
$depto_values = [];
while ($row = $depto_data->fetchArray(SQLITE3_ASSOC)) {
    $depto_labels[] = $row['nombre_depto'];
    $depto_values[] = $row['total'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel Administrativo</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            padding: 30px;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 40px;
        }

        .cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }

        .card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            width: 220px;
            text-align: center;
        }

        .card h2 {
            color: #3498db;
            font-size: 32px;
        }

        .card p {
            color: #555;
        }

        .section {
            margin-top: 40px;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .section h3 {
            margin-bottom: 20px;
            color: #2c3e50;
        }

        .acciones ul {
            list-style: none;
            padding: 0;
        }

        .acciones li {
            margin-bottom: 10px;
            border-bottom: 1px solid #eee;
            padding-bottom: 6px;
            font-size: 15px;
        }

        .links {
            margin-top: 40px;
            text-align: center;
        }

        .links a {
            background-color: #2980b9;
            padding: 10px 20px;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin: 5px;
            display: inline-block;
        }

        .links a:hover {
            background-color: #2471a3;
        }

        canvas {
            max-width: 100%;
        }
    </style>
</head>
<body>
    <h1>Panel Administrativo</h1>

    <div class="cards">
        <div class="card">
            <h2><?= $total_empleados ?></h2>
            <p>Empleados</p>
        </div>
        <div class="card">
            <h2><?= $total_tecnicos ?></h2>
            <p>Técnicos</p>
        </div>
        <div class="card">
            <h2><?= $total_admins ?></h2>
            <p>Administrativos</p>
        </div>
        <div class="card">
            <h2><?= $total_deptos ?></h2>
            <p>Departamentos</p>
        </div>
    </div>

    <div class="section">
        <h3>Empleados por Departamento</h3>
        <canvas id="graficoDepto"></canvas>
    </div>

    <div class="section acciones">
        <h3>Últimas acciones de <?= htmlspecialchars($usuario) ?></h3>
        <ul>
            <?php while ($a = $acciones->fetchArray(SQLITE3_ASSOC)): ?>
                <li><strong><?= htmlspecialchars($a['accion']) ?></strong> (<?= $a['fecha'] ?>)</li>
            <?php endwhile; ?>
        </ul>
    </div>

    <div class="links">
        <a href="departamento.php">Departamentos</a>
        <a href="empleados.php">Empleados</a>
        <a href="auditoria.php">Auditoría</a>
        <a href="logout.php">Cerrar Sesión</a>
    </div>

    <script>
        const ctx = document.getElementById('graficoDepto').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($depto_labels) ?>,
                datasets: [{
                    label: 'Empleados',
                    data: <?= json_encode($depto_values) ?>,
                    backgroundColor: '#3498db',
                    barThickness: 100
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 10,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    </script>
</body>
</html>
