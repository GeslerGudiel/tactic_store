<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

// Verificar si el usuario está autenticado como emprendedor
if (!isset($_SESSION['id_emprendedor'])) {
    header('Location: ../../auth/login.php');
    exit;
}

include_once '../../../src/config/database.php'; // Conexión a la base de datos
$database = new Database();
$db = $database->getConnection();

$id_emprendedor = $_SESSION['id_emprendedor'];

// Preparar la consulta para obtener los productos del emprendedor
$query = "SELECT id_producto, nombre_producto, precio, stock, imagen 
          FROM producto 
          WHERE id_emprendedor = :id_emprendedor";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->execute();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Venta Local</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h3>Mis Productos</h3>

        <div class="row" id="productosContainer">
            <?php if ($stmt->rowCount() > 0): ?>
                <?php while ($producto = $stmt->fetch(PDO::FETCH_ASSOC)): ?>
                    <div class="col-md-4 mb-3">
                        <div class="card h-100">
                            <img src="../../uploads/productos/<?= htmlspecialchars($producto['imagen']) ?>" 
                                 class="card-img-top" 
                                 alt="<?= htmlspecialchars($producto['nombre_producto']) ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?= htmlspecialchars($producto['nombre_producto']) ?></h5>
                                <p class="card-text">Precio: $<?= number_format($producto['precio'], 2) ?></p>
                                <p class="card-text">Stock: <?= $producto['stock'] ?></p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No tienes productos registrados.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
