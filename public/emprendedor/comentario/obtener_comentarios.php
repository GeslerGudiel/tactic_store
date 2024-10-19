<?php
session_start();
include_once '../../../src/config/database.php';

if (!isset($_SESSION['id_emprendedor'])) {
    echo '<p>No tienes acceso a esta sección.</p>';
    exit;
}

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

// Capturar las fechas de filtro
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

// Consulta SQL para obtener los comentarios con filtro de fechas
$query = "
    SELECT c.id_comentario, c.id_producto, c.id_cliente, c.comentario, c.calificacion, 
           c.fecha_comentario, c.respuesta, p.nombre_producto, cli.nombre1, cli.apellido1
    FROM comentario c
    INNER JOIN producto p ON c.id_producto = p.id_producto
    INNER JOIN cliente cli ON c.id_cliente = cli.id_cliente
    WHERE p.id_emprendedor = :id_emprendedor";

// Agregar filtros de fecha si se proporcionan
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $query .= " AND c.fecha_comentario BETWEEN :fecha_inicio AND :fecha_fin";
}

$query .= " ORDER BY c.fecha_comentario DESC";

$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);

if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    $stmt->bindParam(':fecha_fin', $fecha_fin);
}

$stmt->execute();
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($comentarios)) {
    echo '<p>No hay comentarios en tus productos para las fechas seleccionadas.</p>';
    exit;
}

foreach ($comentarios as $comentario) {
    echo "<div class='comentario-box'>
            <h5>{$comentario['nombre_producto']} (Calificación: {$comentario['calificacion']}/5)</h5>
            <p><strong>Cliente:</strong> {$comentario['nombre1']} {$comentario['apellido1']}</p>
            <p><strong>Comentario:</strong> {$comentario['comentario']}</p>
            <p><small><strong>Fecha:</strong> {$comentario['fecha_comentario']}</small></p>";

    if (!empty($comentario['respuesta'])) {
        echo "<p><strong>Mi respuesta:</strong> {$comentario['respuesta']}</p>";
    }

    echo "<form class='form-responder-comentario'>
            <input type='hidden' name='id_comentario' value='{$comentario['id_comentario']}'>
            <textarea name='respuesta' class='form-control mb-2' placeholder='Responder en nombre de la tienda...' required></textarea>
            <button type='submit' class='btn btn-primary'>Enviar Respuesta</button>
          </form>
          </div>";
}
?>
