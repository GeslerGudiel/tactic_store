<?php
if (session_status() == PHP_SESSION_NONE) {// Verificar si una sesión ya está iniciada
    session_start();
}

include_once 'database.php';  // Incluir la clase Database



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

function redirectWithMessage($url, $type, $message) {
    $_SESSION['message'] = ['type' => $type, 'text' => $message];
    header("Location: $url");
    exit;
}