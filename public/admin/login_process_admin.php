<?php
session_start();

// Incluir la conexión a la base de datos
include_once '../../src/config/database.php';

// Verificar si se ha enviado el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'];
    $contrasena = $_POST['contrasena'];

    // Validar los campos
    if (!empty($correo) && !empty($contrasena)) {
        $database = new Database();
        $db = $database->getConnection();

        // Consulta para verificar el correo
        $query = "SELECT * FROM administrador WHERE correo = :correo";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':correo', $correo);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verificar si se encontró el administrador y la contraseña es correcta
        if ($admin && password_verify($contrasena, $admin['contrasena'])) {
            $_SESSION['usuario_id'] = $admin['id_administrador'];
            $_SESSION['usuario_rol'] = $admin['rol'];
            header("Location: dashboard_admin.php");
            exit;
        } else {
            $message = "Correo o contraseña incorrectos.";
        }
    } else {
        $message = "Por favor, complete todos los campos.";
    }

    // Redirigir de nuevo al formulario con el mensaje de error
    header("Location: login_admin.php?message=" . urlencode($message));
    exit;
}
