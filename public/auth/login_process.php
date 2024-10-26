<?php
include_once '../../src/config/database.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = htmlspecialchars(strip_tags($_POST['correo']));
    $contrasena = $_POST['contrasena'];

    try {
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT e.id_negocio, e.id_emprendedor, e.nombre1, e.apellido1, 
                         e.contrasena, e.id_estado_usuario, e.registro_completo
                  FROM emprendedor e
                  WHERE e.correo = :correo";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':correo', $correo, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Verificar si la contraseña ingresada es correcta
            if (password_verify($contrasena, $row['contrasena'])) {

                // Guardar información básica en la sesión
                $_SESSION['id_emprendedor'] = $row['id_emprendedor'];
                $_SESSION['usuario_rol'] = 'emprendedor';
                $_SESSION['nombre1'] = $row['nombre1'];
                $_SESSION['apellido1'] = $row['apellido1'];
                $_SESSION['id_negocio'] = $row['id_negocio'];
                $_SESSION['estado_emprendedor'] = $row['id_estado_usuario'];  // Almacenar el estado

                // Verificar el estado del emprendedor
                if ($row['id_estado_usuario'] == 2) {  // 2 = Activado
                    if ($row['registro_completo'] == 1) {
                        header("Location: ../emprendedor/dashboard_emprendedor.php");
                    } else {
                        header("Location: completar_registro.php");
                    }
                    exit;
                } elseif ($row['id_estado_usuario'] == 3) {  // 3 = Pendiente de Validación
                    $_SESSION['message'] = [
                        'type' => 'warning',
                        'text' => 'Tu cuenta está pendiente de validación por el administrador.'
                    ];
                    header("Location: ../emprendedor/dashboard_emprendedor.php");
                    exit;
                } else {
                    $_SESSION['message'] = [
                        'type' => 'error',
                        'text' => 'Tu cuenta no está activada.'
                    ];
                    header("Location: login.php");
                    exit;
                }
            } else {
                // Contraseña incorrecta
                $_SESSION['message'] = [
                    'type' => 'error',
                    'text' => 'Correo o contraseña incorrectos.'
                ];
                header("Location: login.php");
                exit;
            }
        } else {
            // No se encontró la cuenta
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'No se encontró una cuenta con ese correo electrónico.'
            ];
            header("Location: login.php");
            exit;
        }
    } catch (PDOException $e) {
        // Error en la base de datos
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Error en la base de datos: ' . $e->getMessage()
        ];
        header("Location: login.php");
        exit;
    }
}
?>
