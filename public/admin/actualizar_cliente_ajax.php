<?php
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Verificación si se recibieron todos los datos necesarios
if (!empty($_POST['id_cliente']) && !empty($_POST['NIT']) && !empty($_POST['nombre1']) && !empty($_POST['apellido1']) && !empty($_POST['correo'])) {
    try {
        // Preparar y sanitizar los datos recibidos
        $id_cliente = (int) htmlspecialchars(strip_tags($_POST['id_cliente']));
        $NIT = htmlspecialchars(strip_tags($_POST['NIT']));
        $nombre1 = htmlspecialchars(strip_tags($_POST['nombre1']));
        $nombre2 = !empty($_POST['nombre2']) ? htmlspecialchars(strip_tags($_POST['nombre2'])) : null;
        $nombre3 = !empty($_POST['nombre3']) ? htmlspecialchars(strip_tags($_POST['nombre3'])) : null;
        $apellido1 = htmlspecialchars(strip_tags($_POST['apellido1']));
        $apellido2 = htmlspecialchars(strip_tags($_POST['apellido2']));
        $telefono1 = !empty($_POST['telefono1']) ? htmlspecialchars(strip_tags($_POST['telefono1'])) : null;
        $telefono2 = !empty($_POST['telefono2']) ? htmlspecialchars(strip_tags($_POST['telefono2'])) : null;
        $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);  // Filtrar el correo para asegurar un formato válido
        $id_direccion = (int) htmlspecialchars(strip_tags($_POST['id_direccion']));

        // Validación adicional del correo electrónico
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => 'error', 'message' => 'Formato de correo no válido.']);
            exit;
        }

        // Actualizar los datos del cliente en la base de datos
        $query = "UPDATE cliente SET NIT = :NIT, nombre1 = :nombre1, nombre2 = :nombre2, nombre3 = :nombre3, 
                  apellido1 = :apellido1, apellido2 = :apellido2, telefono1 = :telefono1, telefono2 = :telefono2, 
                  correo = :correo, id_direccion = :id_direccion WHERE id_cliente = :id_cliente";

        $stmt = $db->prepare($query);
        $stmt->bindParam(':NIT', $NIT);
        $stmt->bindParam(':nombre1', $nombre1);
        $stmt->bindParam(':nombre2', $nombre2);
        $stmt->bindParam(':nombre3', $nombre3);
        $stmt->bindParam(':apellido1', $apellido1);
        $stmt->bindParam(':apellido2', $apellido2);
        $stmt->bindParam(':telefono1', $telefono1);
        $stmt->bindParam(':telefono2', $telefono2);
        $stmt->bindParam(':correo', $correo);
        $stmt->bindParam(':id_direccion', $id_direccion);
        $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);

        // Ejecutar la actualización
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Cliente actualizado exitosamente.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Error al actualizar el cliente.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Faltan datos necesarios para actualizar el cliente.']);
}
?>
