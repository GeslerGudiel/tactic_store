<?php
session_start();

// Incluir archivo de configuración y base de datos
include_once '../../src/config/database.php';
include_once '../../src/config/config.php';

// Inicializar la conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

// Verificar si la conexión se ha establecido correctamente
if (!$db) {
    echo "Error al establecer la conexión con la base de datos";
    exit;
}

// Verificar que el usuario está autenticado
if (!isset($_SESSION['id_emprendedor'])) {
    header("Location: login.php");
    exit;
}

$id_emprendedor = $_SESSION['id_emprendedor'];

// Verificar que el formulario fue enviado correctamente
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar datos del negocio
    $nombre_negocio = htmlspecialchars(strip_tags($_POST['nombre_negocio']));
    $direccion_existente = $_POST['direccion_existente'];
    $direccion_nueva = isset($_POST['agregar_direccion']) && $_POST['agregar_direccion'] === 'on';
    $tienda_fisica = htmlspecialchars(strip_tags($_POST['tienda_fisica']));
    $patente_comercio = ''; // Inicia vacío, se llenará después de la subida del archivo
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

    // Manejar la subida del archivo de patente de comercio
    if (isset($_FILES['patente_comercio']) && $_FILES['patente_comercio']['error'] == 0) {
        $fileTmpPath = $_FILES['patente_comercio']['tmp_name'];
        $fileName = $_FILES['patente_comercio']['name'];
        $fileSize = $_FILES['patente_comercio']['size'];
        $fileType = $_FILES['patente_comercio']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Verificar que el archivo sea PDF
        if ($fileExtension == "pdf" && $fileType == "application/pdf") {
            $newFileName = $nombre_negocio . 'patente_' . time() . '.' . $fileExtension;
            $uploadFileDir = '../../uploads/patente_docs/';
            // Verificar que la carpeta de destino exista
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);  // Crear la carpeta si no existe
            }
            $dest_path = $uploadFileDir . $newFileName;

            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $patente_comercio = $newFileName;
            } else {
                $_SESSION['message'] = [
                    'type' => 'error',
                    'text' => 'Error al mover el archivo subido. Verifica los permisos de la carpeta.'
                ];
                header("Location: completar_registro.php");
                exit;
            }
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Solo se permiten archivos PDF para la patente de comercio.'
            ];
            header("Location: completar_registro.php");
            exit;
        }
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Por favor, sube el archivo de patente de comercio.'
        ];
        header("Location: completar_registro.php");
        exit;
    }

    // Capturar y manejar los datos bancarios
    $banco = htmlspecialchars(strip_tags($_POST['banco']));
    $no_cuenta_bancaria = htmlspecialchars(strip_tags($_POST['no_cuenta_bancaria']));
    $tipo_cuenta_bancaria = htmlspecialchars(strip_tags($_POST['tipo_cuenta_bancaria']));
    $nombre_cuenta_bancaria = htmlspecialchars(strip_tags($_POST['nombre_cuenta_bancaria']));

    try {
        // Iniciar la transacción
        $db->beginTransaction();

        // Insertar la información del negocio en la tabla negocio
        $query_negocio = "INSERT INTO negocio (id_emprendedor, nombre_negocio, id_direccion, patente_comercio, tienda_fisica) 
                VALUES (:id_emprendedor, :nombre_negocio, :id_direccion, :patente_comercio, :tienda_fisica)";
        $stmt_negocio = $db->prepare($query_negocio);
        $stmt_negocio->bindParam(':id_emprendedor', $id_emprendedor);
        $stmt_negocio->bindParam(':nombre_negocio', $nombre_negocio);
        $stmt_negocio->bindParam(':id_direccion', $id_direccion);
        $stmt_negocio->bindParam(':patente_comercio', $patente_comercio);
        $stmt_negocio->bindParam(':tienda_fisica', $tienda_fisica);
        $stmt_negocio->execute();

        // Obtener el ID del negocio recién creado
        $id_negocio = $db->lastInsertId();

        // Actualizar la información del emprendedor
        $query_emprendedor = "UPDATE emprendedor SET 
                                id_banco = :id_banco, 
                                no_cuenta_bancaria = :no_cuenta_bancaria, 
                                tipo_cuenta_bancaria = :tipo_cuenta_bancaria, 
                                nombre_cuenta_bancaria = :nombre_cuenta_bancaria,
                                id_negocio = :id_negocio,
                                id_estado_usuario = '3'
                              WHERE id_emprendedor = :id_emprendedor";
        $stmt_emprendedor = $db->prepare($query_emprendedor);
        $stmt_emprendedor->bindParam(':id_banco', $banco);
        $stmt_emprendedor->bindParam(':no_cuenta_bancaria', $no_cuenta_bancaria);
        $stmt_emprendedor->bindParam(':tipo_cuenta_bancaria', $tipo_cuenta_bancaria);
        $stmt_emprendedor->bindParam(':nombre_cuenta_bancaria', $nombre_cuenta_bancaria);
        $stmt_emprendedor->bindParam(':id_negocio', $id_negocio);
        $stmt_emprendedor->bindParam(':id_emprendedor', $id_emprendedor);
        $stmt_emprendedor->execute();

        // Actualizar el campo registro_completo
        $query = "UPDATE emprendedor SET registro_completo = 1 WHERE id_emprendedor = :id_emprendedor";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_emprendedor', $id_emprendedor);
        $stmt->execute();

        //actualizar el estado del emprendedor a 3. Pendiente de validación
        /*$query_update = "UPDATE EMPRENDEDOR SET id_estado_usuario = 3 WHERE id_emprendedor = :id_emprendedor";
        $stmt_update = $db->prepare($query_update);
        $stmt_update->bindParam(':id_emprendedor', $row['id_emprendedor']);*/

        // Confirmar la transacción
        $db->commit();

        // Mensaje de éxito
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Registro completado con éxito. Por favor espera la validación del administrador.'
        ];

        // Redirigir a una página de éxito o dashboard
        header("Location: login.php ");
        exit;
    } catch (PDOException $e) {
        // Revertir la transacción en caso de error
        $db->rollBack();

        // Manejar errores de base de datos
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => "Error: " . $e->getMessage()
        ];
        header("Location: completar_registro.php");
        exit;
    }
} else {
    // Manejar accesos no permitidos al script
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Método no permitido.'
    ];
    header("Location: completar_registro.php");
    exit;
}
