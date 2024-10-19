<?php
session_start();
include_once '../../src/config/funciones.php';
include_once '../../src/config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensaje = htmlspecialchars(strip_tags($_POST['mensaje']));
    $id_emprendedor = $_POST['id_emprendedor'];
    $enviado_por = $_POST['enviado_por'];  // 'admin' o 'emprendedor'

    $database = new Database();
    $db = $database->getConnection();
    $id_administrador = isset($_SESSION['id_administrador']) ? $_SESSION['id_administrador'] : null;

    // Manejar la imagen si se sube
    $nombre_imagen = null;
    if (!empty($_FILES['imagen']['name'])) {
        $target_dir = "../../uploads/chat_imagenes/"; // Carpeta donde se guardarán las imágenes
        $nombre_imagen = time() . '_' . basename($_FILES["imagen"]["name"]);
        $target_file = $target_dir . $nombre_imagen;

        // Verificar que el archivo es una imagen
        $tipo_archivo = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $validos = array('jpg', 'jpeg', 'png', 'gif');
        if (in_array($tipo_archivo, $validos)) {
            move_uploaded_file($_FILES["imagen"]["tmp_name"], $target_file); // Guardar imagen
        } else {
            echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido']);
            exit;
        }
    }

    // Insertar mensaje en la base de datos
    $query = "INSERT INTO mensajes_chat (id_emprendedor, id_administrador, mensaje, enviado_por, imagen, leido) 
              VALUES (:id_emprendedor, :id_administrador, :mensaje, :enviado_por, :imagen, 0)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor);
    $stmt->bindParam(':id_administrador', $id_administrador);  // Si el administrador es quien envía
    $stmt->bindParam(':mensaje', $mensaje);
    $stmt->bindParam(':enviado_por', $enviado_por);
    $stmt->bindParam(':imagen', $nombre_imagen);  // Guardar el nombre de la imagen

    if ($stmt->execute()) {
        // Enviar notificación
        if ($enviado_por === 'admin') {
            agregarNotificacion($db, $id_emprendedor, null, "Nuevo mensaje", "Has recibido un nuevo mensaje del administrador.");
        } else {
            agregarNotificacion($db, null, $id_admin, "Nuevo mensaje", "Has recibido un nuevo mensaje del emprendedor.");
        }

        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
