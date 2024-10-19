<?php
session_start();

// Incluir la conexión a la base de datos
include_once '../../src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar la entrada
    $nombre1 = htmlspecialchars($_POST['nombre1']);
    $apellido1 = htmlspecialchars($_POST['apellido1']);
    $correo = filter_var($_POST['correo'], FILTER_VALIDATE_EMAIL);
    $contrasena = $_POST['contrasena'];

    if ($nombre1 && $apellido1 && $correo && !empty($contrasena)) {
        // Conectar a la base de datos
        $database = new Database();
        $db = $database->getConnection();

        // Cifrar la contraseña
        $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

        // Insertar los datos en la base de datos
        $query = "INSERT INTO administrador (nombre1, apellido1, correo, contrasena) 
                  VALUES (:nombre1, :apellido1, :correo, :contrasena)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nombre1', $nombre1);
        $stmt->bindParam(':apellido1', $apellido1);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':contrasena', $hashed_password);

        if ($stmt->execute()) {
            $_SESSION['message'] = "Administrador registrado exitosamente.";
            header("Location: register_admin.php");
            exit;
        } else {
            $_SESSION['message'] = "Error al registrar administrador.";
            header("Location: register_admin.php");
            exit;
        }
    } else {
        $_SESSION['message'] = "Por favor, complete todos los campos correctamente.";
        header("Location: register_admin.php");
        exit;
    }
}
