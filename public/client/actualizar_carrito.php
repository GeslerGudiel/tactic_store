<?php
session_start();
//Lógica para actualizar la cantidad de un producto en el carrito.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = intval($_POST['id_producto']);
    $cantidad = intval($_POST['cantidad']);

    if ($cantidad > 0 && isset($_SESSION['carrito'][$id_producto])) {
        $_SESSION['carrito'][$id_producto]['cantidad'] = $cantidad;
    }

    header("Location: carrito.php");
    exit;
}
?>
