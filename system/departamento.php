<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['username']) || $_SESSION['rol'] !== 'admin') {
    header('Location: denied.php');
    exit();
}

$usuario = $_SESSION['username'];
$mensaje = "";

// --- Agregar departamento ---
if (isset($_POST['accion']) && $_POST['accion'] === 'agregar' && !empty($_POST['nombre_depto'])) {
    $nombre = trim($_POST['nombre_depto']);
    $stmt = $db->prepare("INSERT INTO departamento (nombre_depto) VALUES (:nombre)");
    $stmt->bindValue(':nombre', $nombre);
    if ($stmt->execute()) {
        $db->exec("INSERT INTO auditoria (usuario, accion, fecha, descripcion)
                   VALUES ('$usuario', 'Agregó departamento', datetime('now'), 'Nombre: $nombre')");
        $mensaje = "Departamento agregado con éxito.";
    }
}

// --- Editar departamento ---
if (isset($_POST['accion']) && $_POST['accion'] === 'editar' && isset($_POST['cod_depto']) && !empty($_POST['nombre_depto'])) {
    $cod = $_POST['cod_depto'];
    $nombre = trim($_POST['nombre_depto']);
    $stmt = $db->prepare("UPDATE departamento SET nombre_depto = :nombre WHERE cod_depto = :cod");
    $stmt->bindValue(':nombre', $nombre);
    $stmt->bindValue(':cod', $cod);
    if ($stmt->execute()) {
        $db->exec("INSERT INTO auditoria (usuario, accion, fecha, descripcion)
                   VALUES ('$usuario', 'Editó departamento', datetime('now'), 'ID: $cod, Nuevo nombre: $nombre')");
        $mensaje = "Departamento actualizado.";
    }
}

// --- Eliminar departamento ---
if (isset($_POST['eliminar']) && isset($_POST['cod_depto'])) {
    $cod = $_POST['cod_depto'];
    $check = $db->prepare("SELECT COUNT(*) as total FROM empleado WHERE cod_depto = :cod");
    $check->bindValue(':cod', $cod);
    $res = $check->execute()->fetchArray(SQLITE3_ASSOC);
    if ($res['total'] == 0) {
        $db->exec("DELETE FROM departamento WHERE cod_depto = $cod");
        $db->exec("INSERT INTO auditoria (usuario, accion, fecha, descripcion)
                   VALUES ('$usuario', 'Eliminó departamento', datetime('now'), 'ID: $cod')");
        $mensaje = "Departamento eliminado.";
    } else {
        $mensaje = "No se puede eliminar: hay empleados asignados.";
    }
}

// --- Obtener todos los departamentos con estadísticas ---
$query = "
    SELECT d.cod_depto, d.nombre_depto,
           COUNT(e.num_matricula) as total_empleados,
           SUM(CASE WHEN e.tipo_empleado = 'tecnico' THEN 1 ELSE 0 END) as total_tecnicos,
           SUM(CASE WHEN e.tipo_empleado = 'administrativo' THEN 1 ELSE 0 END) as total_admins
    FROM departamento d
    LEFT JOIN empleado e ON d.cod_depto = e.cod_depto
    GROUP BY d.cod_depto
    ORDER BY d.nombre_depto ASC
";
$departamentos = $db->query($query);

// --- Modo edición ---
$modo_editar = false;
$datos_editar = [];

if (isset($_GET['editar']) && is_numeric($_GET['editar'])) {
    $id = $_GET['editar'];
    $stmt = $db->prepare("SELECT * FROM departamento WHERE cod_depto = :id");
    $stmt->bindValue(':id', $id);
    $datos_editar = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
    if ($datos_editar) {
        $modo_editar = true;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Departamentos</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 40px 20px;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 40px;
        }

        .mensaje {
            text-align: center;
            background-color: #d4edda;
            color: #155724;
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 25px;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
            font-weight: bold;
        }

        .form-card {
            background-color: #ffffff;
            border-radius: 16px;
            padding: 30px;
            box-shadow: 0 6px 14px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: auto;
            margin-bottom: 50px;
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .form-card h3 {
            text-align: center;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 5px;
            color: #34495e;
        }

        .form-group input[type="text"] {
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 8px;
            background-color: #ecf0f1;
            font-size: 16px;
        }

        .form-card button {
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            background-color: #27ae60;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .form-card button:hover {
            background-color: #219150;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 12px rgba(0,0,0,0.1);
        }

        th {
            background-color: #3498db;
            color: white;
            padding: 14px;
            position: sticky;
            top: 0;
        }

        td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid #e1e4e8;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .nombre-depto {
            max-width: 180px;
            word-wrap: break-word;
        }

        .action-cell {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
        }

        .editar-btn, .eliminar-btn {
            padding: 8px 14px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            color: white;
            cursor: pointer;
            transition: background-color 0.3s ease;
            text-decoration: none;
        }

        .editar-btn {
            background-color: #f39c12;
        }

        .editar-btn:hover {
            background-color: #d68910;
        }

        .eliminar-btn {
            background-color: #e74c3c;
        }

        .eliminar-btn:hover {
            background-color: #c0392b;
        }

        .no-eliminable {
            font-size: 13px;
            background-color: #bdc3c7;
            color: white;
            padding: 6px 10px;
            border-radius: 6px;
        }

        .volver {
            text-align: center;
            margin-top: 40px;
        }

        .volver a {
            background-color: #2980b9;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 10px;
            font-size: 16px;
            transition: background-color 0.3s ease;
        }

        .volver a:hover {
            background-color: #2471a3;
        }
    </style>
</head>
<body>

    <h1>Gestión de Departamentos</h1>

    <?php if ($mensaje): ?>
        <div class="mensaje"><?= htmlspecialchars($mensaje) ?></div>
    <?php endif; ?>

    <form method="POST" class="form-card">
        <h3><?= $modo_editar ? 'Editar Departamento' : 'Agregar Departamento' ?></h3>
        <input type="hidden" name="accion" value="<?= $modo_editar ? 'editar' : 'agregar' ?>">
        <?php if ($modo_editar): ?>
            <input type="hidden" name="cod_depto" value="<?= $datos_editar['cod_depto'] ?>">
        <?php endif; ?>
        <div class="form-group">
            <label for="nombre_depto">Nombre del Departamento:</label>
            <input type="text" name="nombre_depto" required value="<?= $modo_editar ? htmlspecialchars($datos_editar['nombre_depto']) : '' ?>">
        </div>
        <button type="submit"><?= $modo_editar ? 'Actualizar' : 'Agregar' ?></button>
    </form>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Empleados</th>
                <th>Técnicos</th>
                <th>Administrativos</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $departamentos->fetchArray(SQLITE3_ASSOC)): ?>
                <tr>
                    <td><?= $row['cod_depto'] ?></td>
                    <td class="nombre-depto"><?= htmlspecialchars($row['nombre_depto']) ?></td>
                    <td><?= $row['total_empleados'] ?></td>
                    <td><?= $row['total_tecnicos'] ?></td>
                    <td><?= $row['total_admins'] ?></td>
                    <td class="action-cell">
                        <a href="?editar=<?= $row['cod_depto'] ?>" class="editar-btn">✏️ Editar</a>
                        <?php if ($row['total_empleados'] == 0): ?>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="cod_depto" value="<?= $row['cod_depto'] ?>">
                                <button type="submit" name="eliminar" class="eliminar-btn">🗑️ Eliminar</button>
                            </form>
                        <?php else: ?>
                            <div class="no-eliminable">No eliminable</div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <div class="volver">
        <a href="dashboard.php">← Volver al Panel</a>
    </div>

</body>
