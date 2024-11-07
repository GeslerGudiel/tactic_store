<?php
session_start();
if (!isset($_SESSION['SESSION_EXPIRED'])) {
    header("Location: login.php"); // Redirige si no está marcada como expirada
    exit;
}
unset($_SESSION['SESSION_EXPIRED']); // Limpia el estado de sesión expirada después de mostrar el mensaje
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sesión Expirada</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                icon: 'warning',
                title: 'Sesión Expirada',
                text: 'Tu sesión ha caducado por inactividad. Por favor, inicia sesión nuevamente.',
                confirmButtonText: 'Aceptar'
            }).then(() => {
                window.location.href = "login.php"; // Redirige a login.php después de la alerta
            });
        });
    </script>
</body>
</html>
