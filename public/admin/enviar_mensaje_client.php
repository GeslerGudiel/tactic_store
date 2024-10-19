<?php
session_start();
include_once '../../src/config/funciones.php';
include_once '../../src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = htmlspecialchars(strip_tags($_POST['mensaje']));
    $id_cliente = $_POST['id_cliente'];
    $enviado_por = 'admin';  // Se asegura que es 'admin'
    
    $imagen = null; // Inicialización del campo de imagen

    // Manejo de imagen si se sube
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $nombreArchivo = $_FILES['imagen']['name'];
        $rutaDestino = '../../uploads/chat_imagenes/' . $nombreArchivo;

        // Mover la imagen subida a la carpeta de destino
        if (move_uploaded_file($_FILES['imagen']['tmp_name'], $rutaDestino)) {
            $imagen = $nombreArchivo;
        }
    }

    $database = new Database();
    $db = $database->getConnection();

    // Preparar la consulta para insertar el mensaje en la base de datos
    $query = "INSERT INTO mensajes_chat (id_cliente, id_administrador, mensaje, enviado_por, leido, imagen) 
              VALUES (:id_cliente, :id_administrador, :mensaje, :enviado_por, 0, :imagen)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_cliente', $id_cliente);
    $stmt->bindParam(':id_administrador', $_SESSION['id_administrador']);
    $stmt->bindParam(':mensaje', $mensaje);
    $stmt->bindParam(':enviado_por', $enviado_por);
    $stmt->bindParam(':imagen', $imagen); // Se añade la imagen

    if ($stmt->execute()) {
        // Enviar notificación al cliente
        agregarNotificacion($db, null, $id_cliente, "Nuevo mensaje", "Has recibido un nuevo mensaje del administrador.");

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al ejecutar la consulta']);
    }
}
?>
