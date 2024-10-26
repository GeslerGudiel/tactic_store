<?php
session_start();

if (!isset($_SESSION['id_emprendedor'])) {
    echo json_encode(['error' => 'No se ha iniciado sesión correctamente.']);
    exit;
}

include_once '../../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

$id_emprendedor = $_SESSION['id_emprendedor'];
$nombre = $_GET['nombre'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

try {
    // Consulta SQL para obtener los clientes del emprendedor
    $query = "
        SELECT 
            id_cliente_emprendedor, 
            nombre_cliente, 
            correo_cliente, 
            telefono_cliente, 
            direccion_cliente, 
            fecha_registro
        FROM cliente_emprendedor
        WHERE id_emprendedor = :id_emprendedor";

    // Aplicar filtros dinámicos
    if (!empty($nombre)) {
        $query .= " AND nombre_cliente LIKE :nombre";
    }

    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $query .= " AND fecha_registro BETWEEN :fecha_inicio AND :fecha_fin";
    } elseif (!empty($fecha_inicio)) {
        $query .= " AND fecha_registro >= :fecha_inicio";
    } elseif (!empty($fecha_fin)) {
        $query .= " AND fecha_registro <= :fecha_fin";
    }

    $query .= " ORDER BY fecha_registro DESC";

    // Preparar la consulta
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);

    if (!empty($nombre)) {
        $nombre_param = "%$nombre%";
        $stmt->bindParam(':nombre', $nombre_param, PDO::PARAM_STR);
    }
    if (!empty($fecha_inicio)) {
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    }
    if (!empty($fecha_fin)) {
        $stmt->bindParam(':fecha_fin', $fecha_fin);
    }

    // Ejecutar la consulta
    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Enviar los datos como respuesta JSON
    echo json_encode(['status' => 'success', 'data' => $clientes]);
} catch (PDOException $e) {
    // Manejar errores
    echo json_encode(['error' => 'Error al obtener los clientes: ' . $e->getMessage()]);
}
exit;
