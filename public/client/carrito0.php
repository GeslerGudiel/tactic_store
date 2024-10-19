<?php
session_start();
include_once '../../src/config/database.php';

$productos = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];

if (empty($productos)) {
    echo "<p>Tu carrito está vacío.</p>";
} else {
    echo "<div class='container mt-5'>";
    echo "<h2>Tu Carrito</h2>";
    echo "<table class='table'>";
    echo "<thead><tr><th>Producto</th><th>Cantidad</th><th>Precio</th><th>Total</th><th>Acciones</th></tr></thead>";
    echo "<tbody>";

    $total = 0;

    foreach ($productos as $id_producto => $producto) {
        $subtotal = $producto['precio'] * $producto['cantidad'];
        $total += $subtotal;

        echo "<tr>";
        echo "<td>" . htmlspecialchars($producto['nombre']) . "</td>";
        echo "<td>" . htmlspecialchars($producto['cantidad']) . "</td>";
        echo "<td>Q" . number_format($producto['precio'], 2) . "</td>";
        echo "<td>Q" . number_format($subtotal, 2) . "</td>";
        echo "<td>
                <a href='eliminar_producto.php?id=$id_producto' class='btn btn-danger btn-sm'>Eliminar</a>
              </td>";
        echo "</tr>";
    }

    echo "<tr><td colspan='3'>Total</td><td>Q. " . number_format($total, 2) . "</td><td></td></tr>";
    echo "</tbody>";
    echo "</table>";
    echo "<a href='checkout.php' class='btn btn-primary'>Proceder al Pago</a>";
    echo "</div>";
}
?>
