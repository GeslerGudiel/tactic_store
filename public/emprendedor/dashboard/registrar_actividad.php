<?php
session_start();

if (isset($_SESSION['id_emprendedor'], $_POST['accion'])) {
    $id_emprendedor = $_SESSION['id_emprendedor'];
    $accion = $_POST['accion'];
    $timestamp = date("Y-m-d H:i:s");

    include_once '../../../src/config/database.php';
    $database = new Database();
    $db = $database->getConnection();

    $query = "INSERT INTO actividad_emprendedor (id_emprendedor, accion, timestamp) VALUES (:id_emprendedor, :accion, :timestamp)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor);
    $stmt->bindParam(':accion', $accion);
    $stmt->bindParam(':timestamp', $timestamp);

    if ($stmt->execute()) {
        echo "Actividad registrada correctamente";
    } else {
        echo "Error al registrar actividad";
    }
}
?>
