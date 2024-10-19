<?php
session_start();
include_once '../../src/config/funciones.php';
include_once '../../src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = htmlspecialchars(strip_tags($_POST['mensaje']));
    $id_cliente = $_POST['id_cliente'];
    $enviado_por = 'cliente';  // Se asegura que es 'cliente'
    $imagen = null;  // Se inicia el campo imagen como null

    $database = new Database();
    $db = $database->getConnection();

    // Verificar si se subió una imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        // Generar un nombre único para la imagen y moverla a la carpeta de destino
        $nombreImagen = uniqid('img_') . '.' . pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $rutaDestino = '../../uploads/chat_imagenes/' . $nombreImagen;

        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
            $imagen = $nombreImagen;  // Si se cargó correctamente, se asigna el nombre
        }
    }

    // Obtener el id del cliente desde la sesión
    $id_cliente = isset($_SESSION['id_cliente']) ? $_SESSION['id_cliente'] : null;

    // Verificar que el mensaje y el cliente existan o que haya imagen
    if (!empty($id_cliente) && (!empty($mensaje) || $imagen)) {
        // Preparar la consulta para insertar el mensaje en la base de datos
        $query = "INSERT INTO mensajes_chat (id_cliente, mensaje, imagen, enviado_por, leido) 
                  VALUES (:id_cliente, :mensaje, :imagen, :enviado_por, 0)";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_cliente', $id_cliente);
        $stmt->bindParam(':mensaje', $mensaje);
        $stmt->bindParam(':imagen', $imagen);
        $stmt->bindParam(':enviado_por', $enviado_por);

        if ($stmt->execute()) {
            // Enviar notificación al administrador
            agregarNotificacion($db, null, $id_cliente, "Nuevo mensaje", "Has recibido un nuevo mensaje de un cliente.");

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Faltan datos']);
    }
}
?>
