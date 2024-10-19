<?php
session_start();

// Incluir archivo de configuración y base de datos
include_once '../../src/config/database.php';
include_once '../../src/config/config.php';

// Inicializar la conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

if (!$db) {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Error al establecer la conexión con la base de datos.'
    ];
    header("Location: register.php");
    exit;
}

ob_start(); // Iniciar almacenamiento en buffer

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre1 = htmlspecialchars(strip_tags($_POST['nombre1']));
    $nombre2 = htmlspecialchars(strip_tags($_POST['nombre2']));
    $nombre3 = htmlspecialchars(strip_tags($_POST['nombre3'] ?? ''));
    $apellido1 = htmlspecialchars(strip_tags($_POST['apellido1']));
    $apellido2 = htmlspecialchars(strip_tags($_POST['apellido2']));
    $telefono1 = htmlspecialchars(strip_tags($_POST['telefono1']));
    $telefono2 = htmlspecialchars(strip_tags($_POST['telefono2'] ?? ''));
    $correo = htmlspecialchars(strip_tags($_POST['correo']));
    $contrasena = password_hash($_POST['contrasena'], PASSWORD_BCRYPT);
    $dpi = htmlspecialchars(strip_tags($_POST['dpi']));
    $token_activacion = bin2hex(random_bytes(50));  // Generar un token de activación
    $direccion_existente = htmlspecialchars(strip_tags($_POST['direccion_existente']));
    $direccion_nueva = isset($_POST['agregar_direccion']) && $_POST['agregar_direccion'] === 'on';
    $id_direccion = null;

    // Manejar la creación de una nueva dirección si es necesario
    if ($direccion_nueva) {
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
    } else {
        $id_direccion = $direccion_existente;
    }   

    // Manejar la subida del archivo PDF
    if (isset($_FILES['documento_identificacion']) && $_FILES['documento_identificacion']['error'] == 0) {
        $fileTmpPath = $_FILES['documento_identificacion']['tmp_name'];
        $fileName = $_FILES['documento_identificacion']['name'];
        $fileSize = $_FILES['documento_identificacion']['size'];
        $fileType = $_FILES['documento_identificacion']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Verificar que el archivo sea un PDF
        if ($fileExtension == "pdf" && $fileType == "application/pdf") {
            // Ruta donde se guardará el archivo
            $newFileName = $dpi . "_" . time() . "." . $fileExtension;
            $uploadFileDir = '../../uploads/dpi_docs/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0777, true);  // Crea el directorio si no existe
            }
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $documento_identificacion = $newFileName;
            } else {
                $_SESSION['message'] = [
                    'type' => 'error',
                    'text' => 'Hubo un error al subir el archivo. Inténtalo de nuevo.'
                ];
                header("Location: register.php");
                ob_end_flush();
                exit;
            }
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Por favor sube un archivo PDF válido.'
            ];
            header("Location: register.php");
            ob_end_flush();
            exit;
        }
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Por favor sube el archivo de identificación.'
        ];
        header("Location: register.php");
        ob_end_flush();
        exit;
    }

    try {
        // Asigna el estado "Pendiente de activación"
        $estado_pendiente = 1;

        // Insertar Emprendedor
        $query_emprendedor = "INSERT INTO EMPRENDEDOR (nombre1, nombre2, nombre3, apellido1, apellido2, telefono1, telefono2, correo, contrasena, dpi, documento_identificacion, token_activacion, id_direccion, id_estado_usuario) 
                              VALUES (:nombre1, :nombre2, :nombre3, :apellido1, :apellido2, :telefono1, :telefono2, :correo, :contrasena, :dpi, :documento_identificacion, :token_activacion, :id_direccion, :estado_pendiente)";
        $stmt_emprendedor = $db->prepare($query_emprendedor);

        // Vinculación de parámetros
        $stmt_emprendedor->bindParam(':nombre1', $nombre1);
        $stmt_emprendedor->bindParam(':nombre2', $nombre2);
        $stmt_emprendedor->bindParam(':nombre3', $nombre3);
        $stmt_emprendedor->bindParam(':apellido1', $apellido1);
        $stmt_emprendedor->bindParam(':apellido2', $apellido2);
        $stmt_emprendedor->bindParam(':telefono1', $telefono1);
        $stmt_emprendedor->bindParam(':telefono2', $telefono2);
        $stmt_emprendedor->bindParam(':correo', $correo);
        $stmt_emprendedor->bindParam(':contrasena', $contrasena);
        $stmt_emprendedor->bindParam(':dpi', $dpi);
        $stmt_emprendedor->bindParam(':documento_identificacion', $documento_identificacion);
        $stmt_emprendedor->bindParam(':id_direccion', $id_direccion);
        $stmt_emprendedor->bindParam(':token_activacion', $token_activacion);
        $stmt_emprendedor->bindParam(':estado_pendiente', $estado_pendiente);

        // Ejecución de la consulta
        if ($stmt_emprendedor->execute()) {
            // Enviar correo de activación
            $activation_link = "http://localhost/comercio_electronico/public/auth/activate.php?token=" . $token_activacion;
            $to = $correo;
            $subject = "Activa tu cuenta";
            $message = "Hola $nombre1,\n\nHaz clic en el siguiente enlace para activar tu cuenta:\n$activation_link";
            $headers = "From: no-reply@tacticstore.com";

            if (mail($to, $subject, $message, $headers)) {
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Correo de activación enviado. Por favor, revisa tu correo.'
                ];
                header("Location: ../index.php");
            } else {
                $_SESSION['message'] = [
                    'type' => 'warning',
                    'text' => 'Registro exitoso, pero hubo un error al enviar el correo de activación.'
                ];
                header("Location: register.php");
            }
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Error al registrar. Inténtalo de nuevo.'
            ];
            header("Location: register.php");
        }

    } catch (PDOException $e) {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => "Error: " . $e->getMessage()
        ];
        header("Location: register.php");
    }

    ob_end_flush(); // Enviar la salida almacenada en buffer
} else {
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Método no permitido.'
    ];
    header("Location: register.php");
    ob_end_flush(); // Enviar la salida almacenada en buffer
}
