<?php
// Conecta a la base de datos .db
$db = new SQLite3(__DIR__ . '/../bd/database.db');

$db->exec('PRAGMA foreign_keys = ON');

?>


