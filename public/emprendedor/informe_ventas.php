<?php
// Iniciar sesión
session_start();

// Incluir la conexión a la base de datos
include_once '../../src/config/database.php';

// Consulta para obtener las ventas del día actual
$query = "SELECT SUM(total_venta) AS total_ventas, SUM(ganancia) AS ganancia_total, SUM(inversion) AS inversion_total 
          FROM ventas WHERE DATE(fecha_venta) = CURDATE()";
$stmt = $db->prepare($query);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);

// Asignar los resultados
$total_ventas = $result['total_ventas'] ?? 0;
$ganancia_total = $result['ganancia_total'] ?? 0;
$inversion_total = $result['inversion_total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Ventas - Día</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2>Informe de Ventas - Día Actual</h2>
        <p>Total de ventas: <?php echo number_format($total_ventas, 2); ?> GTQ</p>
        <p>Ganancia total: <?php echo number_format($ganancia_total, 2); ?> GTQ</p>
        <p>Inversión total: <?php echo number_format($inversion_total, 2); ?> GTQ</p>
    </div>
</body>
</html>
