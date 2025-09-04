<?php
require 'includes/db.php';

if ($db) {
    echo "✅ Conexión exitosa a la base de datos.";
} else {
    echo "❌ Error al conectar.";
}
?>
