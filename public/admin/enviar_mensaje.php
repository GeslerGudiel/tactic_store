<?php
session_start();
include_once '../../src/config/funciones.php';
include_once '../../src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = htmlspecialchars(strip_tags($_POST['mensaje']));
    $id_emprendedor = $_POST['id_emprendedor'];
    $enviado_por = 'admin';  // Se asegura que es 'admin'

    $imagen = null; // Inicializaión de la variable para la imagen

    // Verificar si se subió una imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Generar un nombre único para la imagen y moverla a la carpeta de destino
        $nombreImagen = uniqid('img_') . '.' . pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $rutaDestino = '../../uploads/chat_imagenes/' . $nombreImagen;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
            $imagen = $nombreImagen;  // Si se cargó correctamente, se asigna el nombre de la imagen
        }
    }

    $database = new Database();
    $db = $database->getConnection();

    // Verificar que el mensaje o la imagen existan
    if (!empty($id_emprendedor) && (!empty($mensaje) || $imagen)) {
        // Preparar la consulta para insertar el mensaje en la base de datos
        $query = "INSERT INTO mensajes_chat (id_emprendedor, id_administrador, mensaje, imagen, enviado_por, leido) 
                  VALUES (:id_emprendedor, :id_administrador, :mensaje, :imagen, :enviado_por, 0)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_emprendedor', $id_emprendedor);
        $stmt->bindParam(':id_administrador', $_SESSION['id_administrador']);
        $stmt->bindParam(':mensaje', $mensaje);
        $stmt->bindParam(':imagen', $imagen);
        $stmt->bindParam(':enviado_por', $enviado_por);

        if ($stmt->execute()) {
            // Enviar notificación al emprendedor
            agregarNotificacion($db, $id_emprendedor, null, "Nuevo mensaje", "Has recibido un nuevo mensaje del administrador.");

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    }
}
?>
