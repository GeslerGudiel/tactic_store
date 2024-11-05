<?php
require_once 'src/config/database.php';

$db = (new Database())->getConnection();
if ($db) {
    echo "Conexión exitosa.";
} else {
    echo "Error en la conexión.";
}
