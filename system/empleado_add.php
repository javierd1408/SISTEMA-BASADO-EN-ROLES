<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['username']) || $_SESSION['rol'] !== 'admin') {
    header('Location: denied.php');
    exit();
}

// Obtener departamentos desde SQLite
$deptos = [];
$result = $db->query("SELECT cod_depto, nombre_depto FROM departamento");
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $deptos[] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Nuevo Empleado</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <!-- Estilos de intl-tel-input -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/css/intlTelInput.min.css"/>

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

        .form-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            background-color: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 900px;
            margin: auto;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 6px;
            font-weight: bold;
            color: #2c3e50;
        }

        .form-group input,
        .form-group select {
            padding: 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
            background-color: #ecf0f1;
        }

        .full-width {
            grid-column: 1 / 3;
        }

        .submit-btn {
            grid-column: 1 / 3;
            justify-self: center;
            margin-top: 20px;
            padding: 12px 30px;
            background-color: #27ae60;
            border: none;
            color: white;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .submit-btn:hover {
            background-color: #219150;
        }

        .back-btn {
            display: inline-block;
            margin-top: 40px;
            background-color: #2980b9;
            color: white;
            padding: 10px 20px;
            border-radius: 8px;
            text-decoration: none;
            text-align: center;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        .back-btn:hover {
            background-color: #2471a3;
        }

        @media (max-width: 700px) {
            .form-container {
                grid-template-columns: 1fr;
            }

            .submit-btn {
                grid-column: auto;
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <h1>Agregar Nuevo Empleado</h1>

    <form method="post" action="empleado_add_procesar.php">
        <div class="form-container">
            <div class="form-group">
                <label for="nombre">Nombre</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>

            <div class="form-group">
                <label for="apellido">Apellido</label>
                <input type="text" id="apellido" name="apellido" required>
            </div>

            <div class="form-group">
                <label for="direccion">Dirección</label>
                <input type="text" id="direccion" name="direccion">
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono">
                <input type="hidden" name="codigo_pais" id="codigo_pais">
                <input type="hidden" name="nombre_pais" id="nombre_pais">
            </div>

            <div class="form-group">
                <label for="usuario_asociado">Usuario asociado</label>
                <input type="text" id="usuario_asociado" name="usuario_asociado">
            </div>

            <div class="form-group">
                <label for="departamento">Departamento</label>
                <select id="departamento" name="departamento">
                    <option value="">Seleccione</option>
                    <?php foreach ($deptos as $depto): ?>
                        <option value="<?= htmlspecialchars($depto['cod_depto']) ?>">
                            <?= htmlspecialchars($depto['nombre_depto']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="tipo_empleado">Tipo de Empleado</label>
                <select id="tipo_empleado" name="tipo_empleado">
                    <option value="">Seleccione</option>
                    <option value="tecnico">Técnico</option>
                    <option value="administrativo">Administrativo</option>
                </select>
            </div>
    
            <div class="form-group" id="nivelDiv" style="display: none;">
                <label for="nivel">Nivel (solo técnicos)</label>
                <input type="text" id="nivel" name="nivel" placeholder="Ej: Junior, Senior">
            </div>

            <button type="submit" class="submit-btn">Guardar Empleado</button>
        </div>
    </form>

    <a href="dashboard.php" class="back-btn">Volver al Panel</a>

    <!-- Scripts para mostrar campo de nivel solo si es técnico -->
    <script>
        const tipoEmpleado = document.getElementById("tipo_empleado");
        const nivelDiv = document.getElementById("nivelDiv");

        function toggleNivel() {
            if (tipoEmpleado.value === "tecnico") {
                nivelDiv.style.display = "flex";
            } else {
                nivelDiv.style.display = "none";
            }
        }

        tipoEmpleado.addEventListener("change", toggleNivel);
        toggleNivel(); // Llamado inicial
    </script>

    <!-- Intl Tel Input JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/intlTelInput.min.js"></script>
    <script>
        const input = document.querySelector("#telefono");

        const iti = window.intlTelInput(input, {
            preferredCountries: ["ve", "ar", "co", "cl", "us", "es"],
            separateDialCode: true,
            utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js"
        });

        // Al enviar el formulario, actualizamos los campos ocultos
        document.querySelector("form").addEventListener("submit", function(e) {
    if (tipoEmpleado.value === "tecnico" && !document.querySelector("#nivel").value.trim()) {
        e.preventDefault();
        alert("Por favor, complete el campo 'Nivel' para técnicos.");
    }
});
    </script>

</body>
</html>

