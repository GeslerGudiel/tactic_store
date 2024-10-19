<?php
session_start();

if (isset($_GET['id'])) {
    $id_producto = intval($_GET['id']);
    
    if (isset($_SESSION['carrito'][$id_producto])) {
        unset($_SESSION['carrito'][$id_producto]);
    }
}

header("Location: carrito.php");
exit;
?>
