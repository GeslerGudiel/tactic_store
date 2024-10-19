<?php
function agregarNotificacion($db, $id_cliente = null, $id_emprendedor = null, $titulo, $mensaje) {
    $query = "INSERT INTO notificacion (id_cliente, id_emprendedor, titulo, mensaje, fecha, leido)
              VALUES (:id_cliente, :id_emprendedor, :titulo, :mensaje, NOW(), 0)";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_cliente', $id_cliente);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor);
    $stmt->bindParam(':titulo', $titulo);
    $stmt->bindParam(':mensaje', $mensaje);
    $stmt->execute();
}

?>
