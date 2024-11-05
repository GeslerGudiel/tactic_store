<?php
session_start();
include_once '../../src/config/database.php'; // Conexión a la base de datos

function redirectWithMessage($url, $type, $text) {
    $_SESSION['message'] = [
        'type' => $type,
        'text' => $text
    ];
    header("Location: $url");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);

    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        redirectWithMessage('forgot_password.php', 'error', 'Correo electrónico no válido.');
    }

    try {
        $database = new Database();
        $db = $database->getConnection();

        // Verificar si el correo existe en la base de datos
        $query = "SELECT id_emprendedor FROM emprendedor WHERE correo = :correo";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();

        $emprendedor = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($emprendedor) {
            // Generar un token de recuperación y almacenar la fecha de generación
            $token = bin2hex(random_bytes(50));
            $fecha_token = date('Y-m-d H:i:s');  // Hora actual

            $query = "UPDATE emprendedor SET token_recuperacion = :token, fecha_token = :fecha_token WHERE id_emprendedor = :id_emprendedor";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':fecha_token', $fecha_token);
            $stmt->bindParam(':id_emprendedor', $emprendedor['id_emprendedor'], PDO::PARAM_INT);
            $stmt->execute(); 

            // Enviar el enlace de recuperación por correo electrónico
            $enlace = "http://localhost/comercio_electronico/public/auth/reset_password.php?token=" . $token;
            $mensaje = "
                <h1>Recuperación de Contraseña</h1>
                <p>Haz clic en el siguiente enlace para restablecer tu contraseña (válido por 30 minutos):</p>
                <a href='$enlace'>Restablecer Contraseña</a>";

            // Configurar encabezados para HTML
            $headers = "From: no-reply@localshop.com\r\n";
            $headers .= "MIME-Version: 1.0\r\n";
            $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

            // Enviar correo
            if (mail($correo, "Recuperación de Contraseña", $mensaje, $headers)) {
                redirectWithMessage('login.php', 'success', 'Correo de recuperación enviado. Revisa tu bandeja de entrada.');
            } else {
                redirectWithMessage('forgot_password.php', 'error', 'Error al enviar el correo de recuperación.');
            }
        } else {
            redirectWithMessage('forgot_password.php', 'error', 'El correo no está registrado.');
        }
    } catch (PDOException $e) {
        redirectWithMessage('forgot_password.php', 'error', 'Error: ' . $e->getMessage());
    }
} else {
    redirectWithMessage('forgot_password.php', 'error', 'Método no permitido.');
}
?>
