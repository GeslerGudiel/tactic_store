<?php
session_start();
include_once '../../src/config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $NIT = htmlspecialchars(strip_tags($_POST['NIT']));
    $nombre1 = htmlspecialchars(strip_tags($_POST['nombre1']));
    $nombre2 = htmlspecialchars(strip_tags($_POST['nombre2']));
    $apellido1 = htmlspecialchars(strip_tags($_POST['apellido1']));
    $apellido2 = htmlspecialchars(strip_tags($_POST['apellido2']));
    $correo = htmlspecialchars(strip_tags($_POST['correo']));
    $contrasena = $_POST['contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];
    $telefono1 = htmlspecialchars(strip_tags($_POST['telefono1']));
    $telefono2 = htmlspecialchars(strip_tags($_POST['telefono2']));
    $id_direccion = htmlspecialchars(strip_tags($_POST['id_direccion']));
    $token_activacion = bin2hex(random_bytes(50)); // Genera un token único para la activación
    $fecha_creacion = date("Y-m-d H:i:s");

    // Validar que las contraseñas coincidan
    if ($contrasena !== $confirmar_contrasena) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Las contraseñas no coinciden. Por favor, inténtelo de nuevo.'
        ];
        header("Location: register_cliente.php");
        exit;
    }

    // Hashear la contraseña antes de almacenarla en la base de datos
    $hashed_password = password_hash($contrasena, PASSWORD_DEFAULT);

    try {
        $database = new Database();
        $db = $database->getConnection();

        // Manejar la creación de una nueva dirección si es necesario
        if (isset($_POST['agregar_direccion']) && $_POST['agregar_direccion'] === 'on') {
            $departamento = htmlspecialchars(strip_tags($_POST['departamento']));
            $municipio = htmlspecialchars(strip_tags($_POST['municipio']));
            $localidad = htmlspecialchars(strip_tags($_POST['localidad']));

            // Insertar la nueva dirección en la base de datos
            $query_direccion = "INSERT INTO direccion (departamento, municipio, localidad) 
                                VALUES (:departamento, :municipio, :localidad)";
            $stmt_direccion = $db->prepare($query_direccion);
            $stmt_direccion->bindParam(':departamento', $departamento);
            $stmt_direccion->bindParam(':municipio', $municipio);
            $stmt_direccion->bindParam(':localidad', $localidad);
            $stmt_direccion->execute();

            // Obtener el ID de la nueva dirección
            $id_direccion = $db->lastInsertId();
        }

        // Insertar datos del cliente en la base de datos
        $query = "INSERT INTO cliente (NIT, nombre1, nombre2, apellido1, apellido2, correo, contrasena, telefono1, telefono2, id_direccion, token_activacion, fecha_creacion) 
                  VALUES (:NIT, :nombre1, :nombre2, :apellido1, :apellido2, :correo, :contrasena, :telefono1, :telefono2, :id_direccion, :token_activacion, :fecha_creacion)";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':NIT', $NIT);
        $stmt->bindParam(':nombre1', $nombre1);
        $stmt->bindParam(':nombre2', $nombre2);
        $stmt->bindParam(':apellido1', $apellido1);
        $stmt->bindParam(':apellido2', $apellido2);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':contrasena', $hashed_password);
        $stmt->bindParam(':telefono1', $telefono1);
        $stmt->bindParam(':telefono2', $telefono2);
        $stmt->bindParam(':id_direccion', $id_direccion);
        $stmt->bindParam(':token_activacion', $token_activacion);
        $stmt->bindParam(':fecha_creacion', $fecha_creacion);

        if ($stmt->execute()) {
            $_SESSION['message'] = [
                'type' => 'success',
                'text' => 'Registro exitoso. Por favor, revise su correo electrónico para activar su cuenta.'
            ];
            header("Location: principal_cliente.php");
            exit;
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Hubo un problema al registrarse. Por favor, inténtelo de nuevo más tarde.'
            ];
            header("Location: register_cliente.php");
            exit;
        }

    } catch (PDOException $e) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Error: ' . $e->getMessage()
        ];
        header("Location: register_cliente.php");
        exit;
    }
}