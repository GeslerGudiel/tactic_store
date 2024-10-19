<?php
session_start();
include_once '../../src/config/database.php';
require('../../src/config/fpdf/fpdf.php');

// Verificar si el emprendedor ha iniciado sesión
if (!isset($_SESSION['id_emprendedor'])) {
    header("Location: ../auth/login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

// Verificar el estado del emprendedor
$query = "SELECT id_estado_usuario FROM emprendedor WHERE id_emprendedor = :id_emprendedor";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->execute();
$estado_emprendedor = $stmt->fetch(PDO::FETCH_ASSOC)['id_estado_usuario'];

// Si el estado es "Pendiente de Validación", redirigir a la página del perfil
if ($estado_emprendedor == 3) {
    $_SESSION['message'] = [
        'type' => 'warning',
        'text' => 'Tu cuenta está pendiente de validación por el administrador.'
    ];
    header("Location: dashboard.php");
    exit;
}

// Configuración de ordenamiento
$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'nombre_producto';
$order_direction = isset($_GET['order_direction']) && $_GET['order_direction'] == 'desc' ? 'desc' : 'asc';

// Consulta para obtener productos del emprendedor
$query = "SELECT id_producto, nombre_producto, stock, precio, costo, (precio - costo) AS ganancia_unitaria 
          FROM producto 
          WHERE id_emprendedor = :id_emprendedor
          ORDER BY $order_by $order_direction";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular el resumen del inventario
$total_inversion = 0;
$total_ganancia_esperada = 0;
$total_productos = 0;
$total_sin_stock = 0;

foreach ($productos as $producto) {
    $total_inversion += $producto['costo'] * $producto['stock'];
    $total_ganancia_esperada += $producto['ganancia_unitaria'] * $producto['stock'];
    $total_productos += $producto['stock'];

    if ($producto['stock'] == 0) {
        $total_sin_stock++;
    }
}

// Descargar PDF si se solicita
if (isset($_GET['download']) && $_GET['download'] == 'pdf') {
    $order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'nombre_producto';
    $order_direction = isset($_GET['order_direction']) ? $_GET['order_direction'] : 'asc';

    $query = "SELECT id_producto, nombre_producto, stock, precio, costo, (precio - costo) AS ganancia_unitaria 
              FROM producto 
              WHERE id_emprendedor = :id_emprendedor
              ORDER BY $order_by $order_direction";
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->SetTextColor(33, 37, 41); // Color gris oscuro
    $pdf->Cell(0, 10, 'Resumen del Inventario', 0, 1, 'C');

    $pdf->Ln(10); // Espacio entre el título y la tabla

    // Resumen
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, 'Total Productos:');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, $total_productos, 0, 1);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, 'Total sin Stock:');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, $total_sin_stock, 0, 1);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, 'Inversion Realizada:');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, 'Q. ' . number_format($total_inversion, 2), 0, 1);

    $pdf->SetFont('Arial', 'B', 12);
    $pdf->Cell(60, 10, 'Ganancia Esperada:');
    $pdf->SetFont('Arial', '', 12);
    $pdf->Cell(40, 10, 'Q. ' . number_format($total_ganancia_esperada, 2), 0, 1);

    $pdf->Ln(10); // Espacio entre el resumen y la tabla de productos

    // Encabezado de la tabla
    $pdf->SetFont('Arial', 'B', 12);
    $pdf->SetFillColor(50, 50, 50); // Color de fondo para el encabezado
    $pdf->SetTextColor(255, 255, 255); // Texto blanco
    $pdf->Cell(20, 10, 'ID', 1, 0, 'C', true);
    $pdf->Cell(60, 10, 'Nombre', 1, 0, 'C', true);
    $pdf->Cell(25, 10, 'Stock', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Precio', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Costo', 1, 0, 'C', true);
    $pdf->Cell(30, 10, 'Ganancia', 1, 1, 'C', true);

    // Contenido de la tabla
    $pdf->SetFont('Arial', '', 12);
    $pdf->SetTextColor(0, 0, 0); // Color negro para los datos
    foreach ($productos as $producto) {
        $pdf->Cell(20, 10, $producto['id_producto'], 1);
        $pdf->Cell(60, 10, $producto['nombre_producto'], 1);
        $pdf->Cell(25, 10, $producto['stock'], 1, 0, 'R');
        $pdf->Cell(30, 10, 'Q. ' . number_format($producto['precio'], 2), 1, 0, 'R');
        $pdf->Cell(30, 10, 'Q. ' . number_format($producto['costo'], 2), 1, 0, 'R');
        $pdf->Cell(30, 10, 'Q. ' . number_format($producto['ganancia_unitaria'], 2), 1, 1, 'R');
    }

    $pdf->Output('D', 'inventario.pdf');
    exit;
}

// Solicitud AJAX, solo devolver la tabla
if (isset($_GET['ajax']) && $_GET['ajax'] == 1) {
    ob_start();
    include 'inventario_table.php';
    $content = ob_get_clean();
    echo $content;
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario del Emprendedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        /* Estilo del resumen del inventario */
        .summary-box {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .summary-box h3 {
            font-weight: bold;
            margin-bottom: 15px;
            text-align: center;
        }

        .summary-box p {
            font-size: 1.1rem;
            display: flex;
            align-items: center;
        }

        .summary-box p i {
            margin-right: 10px;
        }

        .summary-box .highlight {
            font-weight: bold;
            font-size: 1.2rem;
            color: #28a745;
        }

        /* Estilo para la tabla */
        .table-inventario {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
        }

        .table-inventario th {
            background-color: #343a40;
            color: white;
            text-align: center;
        }

        .table-inventario td {
            text-align: center;
        }

        .table-inventario th a {
            color: white;
            text-decoration: none;
        }

        .table-inventario th a:hover {
            color: #ffc107;
        }

        /* Botón de descarga PDF */
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            padding: 10px 15px;
            font-size: 1rem;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .btn-danger i {
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4"><i class="fas fa-boxes"></i> Inventario del Emprendedor</h1>

        <!-- Resumen de Inventario -->
        <div class="summary-box">
            <h3><i class="fas fa-chart-bar"></i> Resumen del Inventario</h3>
            <p><i class="fas fa-box-open"></i> <strong>Total Productos:</strong> <span class="highlight"><?php echo $total_productos; ?></span></p>
            <p><i class="fas fa-exclamation-triangle"></i> <strong>Total sin Stock:</strong> <span class="highlight"><?php echo $total_sin_stock; ?></span></p>
            <p><i class="fas fa-dollar-sign"></i> <strong>Inversión Realizada:</strong> <span class="highlight">Q. <?php echo number_format($total_inversion, 2); ?></span></p>
            <p><i class="fas fa-money-bill-wave"></i> <strong>Ganancia Esperada:</strong> <span class="highlight">Q. <?php echo number_format($total_ganancia_esperada, 2); ?></span></p>
        </div>

        <!-- Botón para descargar PDF -->
        <div class="d-flex justify-content-end mb-3">
            <a id="pdf-link" href="ver_inventario.php?download=pdf&order_by=<?php echo $order_by; ?>&order_direction=<?php echo $order_direction; ?>" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Descargar PDF
            </a>
        </div>

        <!-- Tabla de productos -->
        <div id="tabla-inventario" class="table-responsive">
            <table class="table table-bordered table-inventario">
                <thead>
                    <tr>
                        <th><a href="#" class="order-link" data-order_by="id_producto" data-order_direction="asc"><i class="fas fa-hashtag"></i> ID Producto</a></th>
                        <th><a href="#" class="order-link" data-order_by="nombre_producto" data-order_direction="asc"><i class="fas fa-tag"></i> Nombre</a></th>
                        <th><a href="#" class="order-link" data-order_by="stock" data-order_direction="asc"><i class="fas fa-warehouse"></i> Stock</a></th>
                        <th><a href="#" class="order-link" data-order_by="costo" data-order_direction="asc"><i class="fas fa-dollar-sign"></i> Costo</a></th>
                        <th><a href="#" class="order-link" data-order_by="precio" data-order_direction="asc"><i class="fas fa-money-check-alt"></i> Precio</a></th>
                        <th><a href="#" class="order-link" data-order_by="ganancia_unitaria" data-order_direction="asc"><i class="fas fa-coins"></i> Ganancia Unitaria</a></th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($productos)) : ?>
                        <?php foreach ($productos as $producto): ?>
                            <tr>
                                <td><?php echo $producto['id_producto']; ?></td>
                                <td><?php echo $producto['nombre_producto']; ?></td>
                                <td><?php echo $producto['stock']; ?></td>
                                <td>Q. <?php echo number_format($producto['costo'], 2); ?></td>
                                <td>Q. <?php echo number_format($producto['precio'], 2); ?></td>
                                <td>Q. <?php echo number_format($producto['ganancia_unitaria'], 2); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="6">No hay productos disponibles.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Función para manejar el clic en los encabezados de la tabla
            setOrderLinks(); // Aplicar el evento de clic inicial

            function setOrderLinks() {
                console.log('Eventos de ordenamiento aplicados.');
                $('.order-link').off('click').on('click', function(e) {
                    e.preventDefault();

                    var order_by = $(this).data('order_by');
                    var current_order_direction = $(this).data('order_direction');
                    console.log('Ordenar por:', order_by, 'Dirección actual:', current_order_direction);

                    var new_order_direction = current_order_direction === 'asc' ? 'desc' : 'asc';

                    $(this).data('order_direction', new_order_direction);
                    console.log('Nueva dirección:', new_order_direction);

                    $.get('ver_inventario.php', {
                        ajax: 1,
                        order_by: order_by,
                        order_direction: new_order_direction
                    }, function(data) {
                        console.log('Respuesta del servidor recibida.');
                        $('#tabla-inventario').html(data);

                        // Actualizar el enlace de descarga de PDF con los nuevos parámetros
                        $('#pdf-link').attr('href', 'ver_inventario.php?download=pdf&order_by=' + order_by + '&order_direction=' + new_order_direction);

                        // Reaplicar los eventos después de actualizar el contenido
                        setOrderLinks();
                    });
                });
            }
        });
    </script>
</body>

</html>