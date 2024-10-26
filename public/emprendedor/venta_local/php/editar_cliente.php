<?php
session_start();
include_once '../../../../src/config/database.php';

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['id_emprendedor'])) {
    echo json_encode(['error' => 'No se ha iniciado sesión correctamente.']);
    exit;
}

$database = new Database();
$db = $database->getConnection();

if (isset($_POST['id_cliente_emprendedor'], $_POST['nombre_cliente'], $_POST['correo_cliente'], $_POST['telefono_cliente'], $_POST['direccion_cliente'])) {
    $id_cliente_emprendedor = htmlspecialchars(strip_tags($_POST['id_cliente_emprendedor']));
    $nombre_cliente = htmlspecialchars(strip_tags($_POST['nombre_cliente']));
    $correo_cliente = htmlspecialchars(strip_tags($_POST['correo_cliente']));
    $telefono_cliente = htmlspecialchars(strip_tags($_POST['telefono_cliente']));
    $direccion_cliente = htmlspecialchars(strip_tags($_POST['direccion_cliente']));

    try {
        $query = "UPDATE cliente_emprendedor 
                  SET nombre_cliente = :nombre_cliente, correo_cliente = :correo_cliente, 
                      telefono_cliente = :telefono_cliente, direccion_cliente = :direccion_cliente 
                  WHERE id_cliente_emprendedor = :id_cliente_emprendedor";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':nombre_cliente', $nombre_cliente);
        $stmt->bindParam(':correo_cliente', $correo_cliente);
        $stmt->bindParam(':telefono_cliente', $telefono_cliente);
        $stmt->bindParam(':direccion_cliente', $direccion_cliente);
        $stmt->bindParam(':id_cliente_emprendedor', $id_cliente_emprendedor, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Cliente actualizado correctamente.']);
        } else {
            echo json_encode(['error' => 'No se pudo actualizar el cliente.']);
        }
    } catch (Exception $e) {
        echo json_encode(['error' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['error' => 'Datos incompletos para la actualización.']);
}
?>
