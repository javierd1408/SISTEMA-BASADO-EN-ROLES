<?php
session_start();
require_once 'includes/db.php';

// Verificar sesión y rol
if (!isset($_SESSION['username']) || $_SESSION['rol'] !== 'admin') {
    header('Location: denied.php');
    exit();
}

// Procesar eliminación de empleado si se envía POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_id'])) {
    $id = $_POST['eliminar_id'];

    // Eliminar de técnico y administrativo
    $db->exec("DELETE FROM tecnico WHERE id_tecnico = '$id'");
    $db->exec("DELETE FROM administrativo WHERE id_administrativo = '$id'");
    $db->exec("DELETE FROM empleado WHERE num_matricula = '$id'");

    // Registrar en auditoría
    $usuario = $_SESSION['username'];
    $fecha = date('Y-m-d H:i:s');
    $accion = "Eliminó empleado con ID $id";
    $descripcion = "Empleado eliminado permanentemente.";

    $stmt = $db->prepare("INSERT INTO auditoria (usuario, accion, fecha, descripcion) VALUES (:usuario, :accion, :fecha, :descripcion)");
    $stmt->bindValue(':usuario', $usuario);
    $stmt->bindValue(':accion', $accion);
    $stmt->bindValue(':fecha', $fecha);
    $stmt->bindValue(':descripcion', $descripcion);
    $stmt->execute();

    header("Location: empleados.php?mensaje=eliminado");
    exit();
}

// Obtener lista de empleados
$query = "
    SELECT
        e.num_matricula,
        e.nombre,
        e.direccion,
        d.nombre_depto,
        CASE
            WHEN t.id_tecnico IS NOT NULL THEN 'Técnico'
            WHEN a.id_administrativo IS NOT NULL THEN 'Administrativo'
            ELSE 'Desconocido'
        END AS tipo,
        t.nivel
    FROM empleado e
    LEFT JOIN tecnico t ON e.num_matricula = t.id_tecnico
    LEFT JOIN administrativo a ON e.num_matricula = a.id_administrativo
    LEFT JOIN departamento d ON e.cod_depto = d.cod_depto
";

$result = $db->query($query);
$empleados = [];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $empleados[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Empleados</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f8;
            padding: 40px;
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #2c3e50;
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            min-width: 600px;
        }

        th, td {
            padding: 14px;
            text-align: center;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .back-btn, .action-btn {
            background-color: #2980b9;
            color: white;
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            cursor: pointer;
        }

        .back-btn:hover, .action-btn:hover {
            background-color: #2471a3;
        }

        .delete-btn {
            background-color: #e74c3c;
        }

        .delete-btn:hover {
            background-color: #c0392b;
        }

        .no-data {
    text-align: center;
    padding: 40px;
    font-size: 18px;
    color: #7f8c8d;
    background-color: white;
    border-radius: 8px;
}

        form {
            display: inline;
        }

        .mensaje {
            background-color: #2ecc71;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            text-align: center;
            margin-bottom: 20px;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }
    </style>
    <script>
        function confirmarEliminar(nombre) {
            return confirm("¿Estás seguro de que deseas eliminar a " + nombre + "? Esta acción no se puede deshacer.");
        }
    </script>
</head>
<body>
    <h1>Listado de Empleados</h1>

    <?php if (isset($_GET['mensaje']) && $_GET['mensaje'] === 'eliminado'): ?>
        <div class="mensaje">Empleado eliminado correctamente.</div>
    <?php endif; ?>

    <div style="text-align: center; margin-bottom: 20px;">
        <a href="empleado_add.php" class="back-btn" style="background-color: #27ae60;">Agregar nuevo empleado</a>
    </div>

    <?php if (!empty($empleados)): ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Matrícula</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Departamento</th>
                        <th>Tipo</th>
                        <th>Nivel (si aplica)</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empleados as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['num_matricula']) ?></td>
                            <td><?= htmlspecialchars($row['nombre']) ?></td>
                            <td><?= htmlspecialchars($row['direccion']) ?></td>
                            <td><?= htmlspecialchars($row['nombre_depto'] ?? 'No asignado') ?></td>
                            <td><?= htmlspecialchars($row['tipo']) ?></td>
                            <td><?= $row['tipo'] === 'Técnico' ? htmlspecialchars($row['nivel']) : 'N/A' ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirmarEliminar('<?= htmlspecialchars($row['nombre']) ?>')">
                                    <input type="hidden" name="eliminar_id" value="<?= htmlspecialchars($row['num_matricula']) ?>">
                                    <button type="submit" class="action-btn delete-btn">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="table-container">
            <table>
                <tr>
                    <td colspan="6" class="no-data">No hay empleados registrados en este momento.</td>
                </tr>
            </table>
        </div>
    <?php endif; ?>

    <div style="text-align:center; margin-top: 40px;">
        <a href="dashboard.php" class="back-btn">Volver al Panel</a>
    </div>
</body>
</html>

