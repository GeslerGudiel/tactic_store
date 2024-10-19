<?php
include_once '../src/config/database.php';
session_start();

if (isset($_GET['token'])) {
    $token_activacion = $_GET['token'];

    try {
        $database = new Database();
        $db = $database->getConnection();

        // Verificar si el token es válido y el usuario está pendiente de activación
        $query = "SELECT id_emprendedor, id_estado_usuario FROM emprendedor WHERE token_activacion = :token_activacion";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':token_activacion', $token_activacion);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row['id_estado_usuario'] == 1) {  // 1 = Pendiente de activación
                // Activar la cuenta
                $query_update = "UPDATE EMPRENDEDOR SET id_estado_usuario = 2 WHERE id_emprendedor = :id_emprendedor";
                $stmt_update = $db->prepare($query_update);
                $stmt_update->bindParam(':id_emprendedor', $row['id_emprendedor']);

                if ($stmt_update->execute()) {
                    $_SESSION['message'] = [
                        'type' => 'success',
                        'text' => 'Cuenta activada con éxito. Ahora puedes iniciar sesión.'
                    ];
                } else {
                    $_SESSION['message'] = [
                        'type' => 'error',
                        'text' => 'Error al activar la cuenta. Inténtalo de nuevo.'
                    ];
                }
            } else {
                $_SESSION['message'] = [
                    'type' => 'warning',
                    'text' => 'Cuenta ya activada o en un estado que no permite la activación.'
                ];
            }
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Token inválido o cuenta ya activada.'
            ];
        }

        header("Location: login.php");
        exit;

    } catch (PDOException $e) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => "Error: " . $e->getMessage()
        ];
        header("Location: login.php");
        exit;
    }
} else {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Token no proporcionado.'
    ];
    header("Location: login.php");
    exit;
}
?>
