<?php
session_start();
//Lógica para eliminar un producto del carrito.
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_producto = intval($_POST['id_producto']);

    if (isset($_SESSION['carrito'][$id_producto])) {
        unset($_SESSION['carrito'][$id_producto]);
    }

    header("Location: carrito.php");
    exit;
}
?>
