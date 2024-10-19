<?php
include_once '../../src/config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        redirectWithMessage('forgot_password.php', 'error', 'Correo electrónico no válido.');
    }

    try {
        $db = getDBConnection();

        // Verificar si el correo existe en la base de datos
        $query = "SELECT id FROM usuarios WHERE correo = :correo";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Generar un token de recuperación y almacenarlo
            $token = bin2hex(random_bytes(50));
            $query = "UPDATE usuarios SET token_recuperacion = :token WHERE id = :id";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->bindParam(':id', $usuario['id'], PDO::PARAM_INT);
            $stmt->execute();

            // Enviar el enlace de recuperación por correo electrónico
            $enlace = "http://localhost/comercio_electronico/public/auth/reset_password.php?token=" . $token;
            $mensaje = "Haz clic en el siguiente enlace para restablecer tu contraseña: " . $enlace;

            if (mail($correo, "Recuperación de Contraseña", $mensaje, "From: no-reply@localshop.com")) {
                redirectWithMessage('forgot_password.php', 'success', 'Correo de recuperación enviado. Por favor, revisa tu correo.');
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
