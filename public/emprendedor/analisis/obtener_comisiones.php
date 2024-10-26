<?php
session_start();
include_once '../../../src/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    $id_emprendedor = $_SESSION['id_emprendedor'] ?? null;
    if (!$id_emprendedor) {
        throw new Exception('El usuario no ha iniciado sesión correctamente.');
    }

    // Capturar las fechas del filtro
    $fecha_inicio = $_GET['fecha_inicio'] ?? '';
    $fecha_fin = $_GET['fecha_fin'] ?? '';

    // Consulta para obtener las comisiones
    $query = "
        SELECT 
            SUM(CASE WHEN estado_comision = 'Pagada' THEN monto_comision ELSE 0 END) AS total_pagado,
            SUM(CASE WHEN estado_comision = 'Pendiente' THEN monto_comision ELSE 0 END) AS total_pendiente
        FROM comision
        WHERE id_emprendedor = :id_emprendedor
        AND fecha_comision BETWEEN :fecha_inicio AND :fecha_fin";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_fin', $fecha_fin);
    $stmt->execute();

    $comisiones = $stmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode($comisiones);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
