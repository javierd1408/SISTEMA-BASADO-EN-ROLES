<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['username']) || $_SESSION['rol'] !== 'admin') {
    header('Location: denied.php');
    exit();
}


// Crear nuevo proyecto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_proyecto'])) {
    $nombre = trim($_POST['nombre_proyecto']);
    $cliente = trim($_POST['cliente']);
    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin = $_POST['fecha_fin'];

    if ($nombre) {
        $stmt = $db->prepare("INSERT INTO proyecto (nombre_proyecto, cliente, fecha_inicio, fecha_fin) VALUES (?, ?, ?, ?)");
        $stmt->bindValue(1, $nombre);
        $stmt->bindValue(2, $cliente);
        $stmt->bindValue(3, $fecha_inicio);
        $stmt->bindValue(4, $fecha_fin);
        $stmt->execute();
    }
}

// Asignar técnico o administrativo a proyecto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['asignar_persona'])) {
    $proyecto = $_POST['cod_proyecto'];
    $persona = $_POST['persona'];
    $tipo = $_POST['tipo'];
    $fecha = date('Y-m-d');

    if ($tipo === 'tecnico') {
        $stmt = $db->prepare("INSERT OR IGNORE INTO trabaja_en (id_tecnico, cod_proyecto, fecha_asignacion) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $persona);
        $stmt->bindValue(2, $proyecto);
        $stmt->bindValue(3, $fecha);
        $stmt->execute();
    } elseif ($tipo === 'administrativo') {
        // Como en la tabla trabaja_en solo está id_tecnico, se reutiliza ese campo
        $stmt = $db->prepare("INSERT OR IGNORE INTO trabaja_en (id_tecnico, cod_proyecto, fecha_asignacion) VALUES (?, ?, ?)");
        $stmt->bindValue(1, $persona);
        $stmt->bindValue(2, $proyecto);
        $stmt->bindValue(3, $fecha);
        $stmt->execute();
    }
}

// Eliminar proyecto
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_proyecto'], $_POST['delete_proyecto'])) {
    $cod_proyecto = intval($_POST['delete_proyecto']);
    // Primero eliminar asignaciones en trabaja_en
    $stmt1 = $db->prepare("DELETE FROM trabaja_en WHERE cod_proyecto = ?");
    $stmt1->bindValue(1, $cod_proyecto, SQLITE3_INTEGER);
    $stmt1->execute();

    // Luego eliminar el proyecto
    $stmt2 = $db->prepare("DELETE FROM proyecto WHERE cod_proyecto = ?");
    $stmt2->bindValue(1, $cod_proyecto, SQLITE3_INTEGER);
    $stmt2->execute();
}

// Obtener proyectos
$proyectos = $db->query("SELECT * FROM proyecto ORDER BY cod_proyecto DESC");

// Técnicos y administrativos
$tecnicos = $db->query("SELECT t.id_tecnico, e.nombre FROM tecnico t JOIN empleado e ON t.id_tecnico = e.num_matricula");
$admins = $db->query("SELECT a.id_administrativo, e.nombre FROM administrativo a JOIN empleado e ON a.id_administrativo = e.num_matricula");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Proyectos</title>
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f0f2f5;
            padding: 30px;
        }
        h1, h2 {
            color: #2c3e50;
        }
        form {
            background: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin: 10px 0 5px;
        }
        input, select {
            padding: 8px;
            width: 100%;
            margin-bottom: 10px;
        }
        button {
            padding: 10px 15px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        button:hover {
            background-color: #2980b9;
        }
        .proyecto {
            background: white;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            position: relative;
        }
        .delete-btn {
            position: absolute;
            top: 10px;
            right: 10px;
            background: none;
            border: none;
            color: #e74c3c;
            font-weight: bold;
            font-size: 20px;
            cursor: pointer;
            padding: 0 8px;
            border-radius: 6px;
            transition: color 0.3s ease;
        }
        .delete-btn:hover {
            color: #c0392b;
        }
        .volver-btn {
            display: inline-block;
            background-color: #3498db;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
            margin-top: 20px;
        }
        .volver-btn:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <h1>Gestión de Proyectos</h1>

    <form method="POST">
        <h2>Crear Nuevo Proyecto</h2>
        <label>Nombre del Proyecto:</label>
        <input type="text" name="nombre_proyecto" required>

        <label>Cliente:</label>
        <input type="text" name="cliente">

        <label>Fecha Inicio:</label>
        <input type="date" name="fecha_inicio">

        <label>Fecha Fin:</label>
        <input type="date" name="fecha_fin">

        <button type="submit" name="crear_proyecto">Crear Proyecto</button>
    </form>

    <form method="POST">
        <h2>Asignar a Proyecto</h2>
        <label>Proyecto:</label>
        <select name="cod_proyecto">
            <?php
            $proy = $db->query("SELECT cod_proyecto, nombre_proyecto FROM proyecto");
            while ($p = $proy->fetchArray(SQLITE3_ASSOC)) {
                echo "<option value='{$p['cod_proyecto']}'>" . htmlspecialchars($p['nombre_proyecto']) . "</option>";
            }
            ?>
        </select>

        <label>Técnico o Administrativo:</label>
        <select name="persona">
            <optgroup label="Técnicos">
                <?php while ($t = $tecnicos->fetchArray(SQLITE3_ASSOC)): ?>
                    <option value="<?= $t['id_tecnico'] ?>">[T] <?= htmlspecialchars($t['nombre']) ?></option>
                <?php endwhile; ?>
            </optgroup>
            <optgroup label="Administrativos">
                <?php while ($a = $admins->fetchArray(SQLITE3_ASSOC)): ?>
                    <option value="<?= $a['id_administrativo'] ?>">[A] <?= htmlspecialchars($a['nombre']) ?></option>
                <?php endwhile; ?>
            </optgroup>
        </select>

        <label>Tipo:</label>
        <select name="tipo">
            <option value="tecnico">Técnico</option>
            <option value="administrativo">Administrativo</option>
        </select>

        <button type="submit" name="asignar_persona">Asignar</button>
    </form>

    <h2>Proyectos Actuales</h2>
    <?php while ($row = $proyectos->fetchArray(SQLITE3_ASSOC)): ?>
        <div class="proyecto">
            <strong><?= htmlspecialchars($row['nombre_proyecto']) ?></strong><br>
            Cliente: <?= htmlspecialchars($row['cliente']) ?><br>
            Inicio: <?= $row['fecha_inicio'] ?> - Fin: <?= $row['fecha_fin'] ?><br>
            Código: <?= $row['cod_proyecto'] ?>

            <!-- Botón eliminar -->
            <form method="POST" style="display:inline; position:absolute; top:10px; right:10px;">
                <input type="hidden" name="delete_proyecto" value="<?= $row['cod_proyecto'] ?>">
                <button 
                    type="submit" 
                    name="eliminar_proyecto" 
                    title="Eliminar proyecto" 
                    class="delete-btn"
                    onclick="return confirm('¿Seguro que quieres eliminar este proyecto?');"
                >&times;</button>
            </form>
        </div>
    <?php endwhile; ?>

    <a href="dashboard.php" class="volver-btn">Volver al Dashboard</a>
</body>
</html>


