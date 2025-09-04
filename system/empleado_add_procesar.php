<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['username']) || $_SESSION['rol'] !== 'admin') {
    header('Location: denied.php');
    exit();
}

// Recolectar datos del POST
$nombre           = $_POST['nombre'] ?? '';
$apellido         = $_POST['apellido'] ?? '';
$direccion        = $_POST['direccion'] ?? '';
$telefono         = $_POST['telefono'] ?? '';
$usuario_asociado = $_POST['usuario_asociado'] ?? '';
$departamento     = $_POST['departamento'] ?? '';
$tipo_empleado    = $_POST['tipo_empleado'] ?? '';
$nivel            = $_POST['nivel'] ?? null;

// Validar campos requeridos
if (!$nombre || !$apellido || !$usuario_asociado) {
    die("Faltan campos obligatorios.");
}

// Insertar en la base de datos
$stmt = $db->prepare("INSERT INTO empleado 
    (nombre, apellido, direccion, telefono, usuario_asociado, cod_depto, tipo_empleado, nivel)
    VALUES (:nombre, :apellido, :direccion, :telefono, :usuario_asociado, :departamento, :tipo_empleado, :nivel)");

$stmt->bindValue(':nombre', $nombre);
$stmt->bindValue(':apellido', $apellido);
$stmt->bindValue(':direccion', $direccion);
$stmt->bindValue(':telefono', $telefono);
$stmt->bindValue(':usuario_asociado', $usuario_asociado);
$stmt->bindValue(':departamento', $departamento);
$stmt->bindValue(':tipo_empleado', $tipo_empleado);
$stmt->bindValue(':nivel', $nivel);

if ($stmt->execute()) {
    $numMatricula = $db->lastInsertRowID();

    // Insertar en tabla técnico o administrativo
    if ($tipo_empleado === 'tecnico') {
        $insertTecnico = $db->prepare("INSERT INTO tecnico (id_tecnico, nivel) VALUES (:id_tecnico, :nivel)");
        $insertTecnico->bindValue(':id_tecnico', $numMatricula);
        $insertTecnico->bindValue(':nivel', $nivel);
        $insertTecnico->execute();
    } elseif ($tipo_empleado === 'administrativo') {
        $insertAdmin = $db->prepare("INSERT INTO administrativo (id_administrativo) VALUES (:id_administrativo)");
        $insertAdmin->bindValue(':id_administrativo', $numMatricula);
        $insertAdmin->execute();
    }

    // Auditoría
    $usuario = $_SESSION['username'];
    $accion = "Agregó empleado ID $numMatricula: $nombre $apellido";
    $fecha = date('Y-m-d H:i:s');
    $descripcion = "Empleado agregado con éxito. ID: $numMatricula, Nombre: $nombre, Apellido: $apellido";

    $auditoria = $db->prepare("INSERT INTO auditoria (usuario, accion, fecha, descripcion) VALUES (:usuario, :accion, :fecha, :descripcion)");
    $auditoria->bindValue(':usuario', $usuario);
    $auditoria->bindValue(':accion', $accion);
    $auditoria->bindValue(':fecha', $fecha);
    $auditoria->bindValue(':descripcion', $descripcion);
    $auditoria->execute();

    header("Location: empleados.php?mensaje=exito");
} else {
    echo "Error al guardar empleado: " . $db->lastErrorMsg();
}
?>



