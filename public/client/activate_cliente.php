<?php
session_start();
include_once '../../src/config/database.php';

if (isset($_GET['token'])) {
    $token_activacion = $_GET['token'];

    try {
        $database = new Database();
        $db = $database->getConnection();

        // Verificar si el token de activación es válido
        $query = "SELECT id_cliente, nombre1, correo, id_estado_usuario 
                  FROM cliente 
                  WHERE token_activacion = :token AND id_estado_usuario = 1"; // Estado 1 = Pendiente de Activación
        $stmt = $db->prepare($query);
        $stmt->bindParam(':token', $token_activacion);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Actualizar el estado del cliente a "Activado" (Estado 2)
            $query_update = "UPDATE cliente SET id_estado_usuario = 2, token_activacion = NULL WHERE id_cliente = :id_cliente";
            $stmt_update = $db->prepare($query_update);
            $stmt_update->bindParam(':id_cliente', $row['id_cliente']);

            if ($stmt_update->execute()) {
                // Enviar correo de bienvenida
                $to = $row['correo'];
                $subject = "¡Bienvenido a nuestra tienda!";
                $message = "Hola " . $row['nombre1'] . ",\n\n"
                         . "Tu cuenta ha sido activada exitosamente. Ahora puedes iniciar sesión y disfrutar de nuestros productos.\n"
                         . "Visítanos en: http://localhost/comercio_electronico/public/login_cliente.php\n\n"
                         . "¡Gracias por ser parte de nosotros!\n"
                         . "Atentamente, \nEl equipo de Tactic Store";

                $headers = "From: no-reply@tacticstore.com\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8";

                if (mail($to, $subject, $message, $headers)) {
                    $_SESSION['message'] = [
                        'type' => 'success',
                        'text' => '¡Cuenta activada con éxito! Te hemos enviado un correo de bienvenida.'
                    ];
                } else {
                    $_SESSION['message'] = [
                        'type' => 'warning',
                        'text' => 'Cuenta activada, pero no se pudo enviar el correo de bienvenida.'
                    ];
                }

                header("Location: login_cliente.php");
                exit;
            } else {
                $_SESSION['message'] = [
                    'type' => 'error',
                    'text' => 'Hubo un problema al activar tu cuenta. Inténtalo más tarde.'
                ];
                header("Location: register_cliente.php");
                exit;
            }
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'El token de activación es inválido o la cuenta ya está activada.'
            ];
            header("Location: register_cliente.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Error en la base de datos: ' . $e->getMessage()
        ];
        header("Location: register_cliente.php");
        exit;
    }
} else {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Token no proporcionado. Verifica tu enlace.'
    ];
    header("Location: register_cliente.php");
    exit;
}
?>
