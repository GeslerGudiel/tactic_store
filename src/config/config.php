<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include_once 'database.php';  // Incluir la clase Database

// Configurar la zona horaria
date_default_timezone_set('America/Guatemala');

function getDBConnection() {
    $database = new Database();
    return $database->getConnection();
}

function showAlert($type, $message) {
    echo '<script>
        Swal.fire({
            icon: "' . $type . '",
            title: "' . ucfirst($type) . '",
            text: "' . $message . '"
        });
    </script>';
}
