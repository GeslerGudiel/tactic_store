<?php
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

if (!empty($_POST['id_cliente'])) {
    $id_cliente = (int) htmlspecialchars(strip_tags($_POST['id_cliente']));

    try {
        // Consultar los detalles del cliente junto con la información de dirección y estado de usuario
        $query = "
            SELECT c.*, d.localidad, d.municipio, d.departamento, es.nombre_estado 
            FROM cliente c
            LEFT JOIN direccion d ON c.id_direccion = d.id_direccion
            LEFT JOIN estado_usuario es ON c.id_estado_usuario = es.id_estado_usuario
            WHERE c.id_cliente = :id_cliente";
        $stmt = $db->prepare($query);
        $stmt->bindParam(':id_cliente', $id_cliente, PDO::PARAM_INT);
        $stmt->execute();
        $cliente = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($cliente) {
            // Devolver los datos del cliente junto con la información de dirección y estado
            echo json_encode(['status' => 'success', 'data' => $cliente]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Cliente no encontrado.']);
        }
    } catch (PDOException $e) {
        echo json_encode(['status' => 'error', 'message' => 'Error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'ID de cliente no especificado.']);
}
?>
