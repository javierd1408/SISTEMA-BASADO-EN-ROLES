<?php
session_start();
require_once 'includes/db.php';

// Verificación de sesión
if (!isset($_SESSION['username']) || $_SESSION['rol'] !== 'admin') {
    header('Location: denied.php');
    exit();
}

// Parámetros de paginación
$limite = 10;
$pagina = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina - 1) * $limite;

// Total de registros
$total_query = $db->query("SELECT COUNT(*) as total FROM auditoria");
$total_result = $total_query->fetchArray(SQLITE3_ASSOC);
$total_registros = $total_result['total'];
$total_paginas = ceil($total_registros / $limite);

// Obtener registros con orden descendente por fecha
$stmt = $db->prepare("SELECT * FROM auditoria ORDER BY datetime(fecha) DESC LIMIT :limite OFFSET :offset");
$stmt->bindValue(':limite', $limite, SQLITE3_INTEGER);
$stmt->bindValue(':offset', $offset, SQLITE3_INTEGER);
$resultado = $stmt->execute();

// Guardar registros
$auditorias = [];
while ($row = $resultado->fetchArray(SQLITE3_ASSOC)) {
    $auditorias[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Auditoría del Sistema</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #ecf0f1;
            padding: 40px;
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 14px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #34495e;
            color: white;
        }
        tr:hover {
            background-color: #f2f2f2;
        }
        .pagination {
            text-align: center;
            margin-top: 20px;
        }
        .pagination a {
            display: inline-block;
            padding: 10px 14px;
            margin: 0 4px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
        .pagination a:hover {
            background-color: #2980b9;
        }
        .back-btn {
            display: block;
            margin: 30px auto 0;
            width: fit-content;
            padding: 10px 20px;
            background-color: #2ecc71;
            color: white;
            text-decoration: none;
            border-radius: 8px;
        }
        .back-btn:hover {
            background-color: #27ae60;
        }
    </style>
</head>
<body>

<h1>Auditoría del Sistema</h1>

<?php if (!empty($auditorias)): ?>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Acción</th>
                <th>Fecha</th>
                <th>Descripción</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($auditorias as $row): ?>
                <tr>
                    <td><?= htmlspecialchars($row['id']) ?></td>
                    <td><?= htmlspecialchars($row['usuario']) ?></td>
                    <td><?= htmlspecialchars($row['accion']) ?></td>
                    <td><?= htmlspecialchars($row['fecha']) ?></td>
                    <td><?= htmlspecialchars($row['descripcion']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="pagination">
        <?php if ($pagina > 1): ?>
            <a href="?pagina=<?= $pagina - 1 ?>">&laquo; Anterior</a>
        <?php endif; ?>
        <?php if ($pagina < $total_paginas): ?>
            <a href="?pagina=<?= $pagina + 1 ?>">Siguiente &raquo;</a>
        <?php endif; ?>
    </div>
<?php else: ?>
    <p style="text-align:center;">No hay registros de auditoría disponibles.</p>
<?php endif; ?>

<a href="dashboard.php" class="back-btn">Volver al Panel</a>

</body>
</html>
