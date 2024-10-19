<?php
include_once '../../src/config/database.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = htmlspecialchars(strip_tags($_POST['correo']));
    $contrasena = $_POST['contrasena'];

    try {
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT e.id_negocio, e.id_emprendedor, e.nombre1, e.apellido1, e.contrasena, e.id_estado_usuario, e.registro_completo 
        FROM emprendedor e
        WHERE e.correo = :correo";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($contrasena, $row['contrasena'])) {
                if ($row['id_estado_usuario'] == 2) {  // 2 = Activado
                    $_SESSION['id_emprendedor'] = $row['id_emprendedor'];  // Guardar ID del emprendedor en la sesión
                    $_SESSION['usuario_rol'] = 'emprendedor';  // Guardar rol del usuario en la sesión
                    $_SESSION['nombre1'] = $row['nombre1'];
                    $_SESSION['apellido1'] = $row['apellido1'];
                    $_SESSION['id_negocio'] = $row['id_negocio'];

                    // Verificar si el registro está completo
                    if ($row['registro_completo'] == 1) {
                        header("Location: ../emprendedor/dashboard_emprendedor.php");  // Redirige al dashboard del emprendedor
                    } else {
                        header("Location: completar_registro.php");  // Redirige a completar_registro.php si el registro no está completo
                    }
                    exit;
                } elseif ($row['id_estado_usuario'] == 3) {  // 3 = Pendiente de Validación
                    $_SESSION['id_emprendedor'] = $row['id_emprendedor'];  // Guardar ID del emprendedor en la sesión
                    $_SESSION['usuario_rol'] = 'emprendedor';  // Guardar rol del usuario en la sesión
                    $_SESSION['nombre1'] = $row['nombre1'];
                    $_SESSION['apellido1'] = $row['apellido1'];
                    $_SESSION['id_negocio'] = $row['id_negocio'];

                    // Redirigir al perfil con mensaje de advertencia
                    $_SESSION['message'] = [
                        'type' => 'warning',
                        'text' => 'Tu cuenta está pendiente de validación por el administrador.'
                    ];
                    header("Location: ../emprendedor/dashboard.php");
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
                $_SESSION['message'] = [
                    'type' => 'error',
                    'text' => 'Correo o contraseña incorrectos.'
                ];
                header("Location: login.php");
                exit;
            }
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'No se encontró una cuenta con ese correo electrónico.'
            ];
            header("Location: login.php");
            exit;
        }
    } catch (PDOException $e) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Error en la base de datos: ' . $e->getMessage()
        ];
        header("Location: login.php");
        exit;
    }
}
?>
