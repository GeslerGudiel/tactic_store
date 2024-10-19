<?php
session_start();

include_once '../../src/config/database.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = htmlspecialchars(strip_tags($_POST['correo']));
    $contrasena = htmlspecialchars(strip_tags($_POST['contrasena']));

    try {
        $database = new Database();
        $db = $database->getConnection();

        $query = "SELECT * FROM cliente WHERE correo = :correo";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($contrasena, $cliente['contrasena'])) {
                $_SESSION['id_cliente'] = $cliente['id_cliente'];
                $_SESSION['nombre_cliente'] = $cliente['nombre1'];
                $_SESSION['apellido_cliente'] = $cliente['apellido1'];
                $_SESSION['usuario_rol'] = 'cliente';
                header("Location: principal_cliente.php");
                exit;
            } else {
                $error = "Correo o contraseña incorrectos.";
            }
        } else {
            $error = "No se encontró una cuenta con ese correo electrónico.";
        }
    } catch (PDOException $e) {
        $error = "Ocurrió un error al intentar iniciar sesión: " . $e->getMessage();
    }

    if ($error) {
        $_SESSION['login_error'] = $error;
        header("Location: login_cliente.php");
        exit;
    }
}
