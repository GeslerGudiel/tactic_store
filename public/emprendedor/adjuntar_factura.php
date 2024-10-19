<?php
session_start();
include_once '../../src/config/database.php';

if (!isset($_SESSION['id_emprendedor'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

$id_emprendedor = $_SESSION['id_emprendedor'];

// Obtener los pedidos para los que puede subir facturas
$query = "SELECT p.id_pedido, p.fecha_pedido, p.estado_pedido, SUM(d.subtotal) as monto_total 
          FROM pedido p 
          INNER JOIN detalle_pedido d ON p.id_pedido = d.id_pedido
          WHERE p.estado_pedido = 'Pendiente' AND d.id_emprendedor = :id_emprendedor
          GROUP BY p.id_pedido";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->execute();

$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pedido = $_POST['id_pedido'];

    if (isset($_FILES['factura']) && $_FILES['factura']['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($_FILES['factura']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = 'factura_' . $id_pedido . '.' . $extension;
        $ruta_destino = '../../uploads/facturas/' . $nombre_archivo;

        if (move_uploaded_file($_FILES['factura']['tmp_name'], $ruta_destino)) {
            $query_factura = "UPDATE pedido SET factura = :factura WHERE id_pedido = :id_pedido";
            $stmt_factura = $db->prepare($query_factura);
            $stmt_factura->bindParam(':factura', $nombre_archivo);
            $stmt_factura->bindParam(':id_pedido', $id_pedido);
            if ($stmt_factura->execute()) {
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Factura adjuntada con éxito.'
                ];
            } else {
                $_SESSION['message'] = [
                    'type' => 'error',
                    'text' => 'Error al adjuntar la factura en la base de datos.'
                ];
            }
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Error al subir la factura.'
            ];
        }
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Debes seleccionar un archivo de factura.'
        ];
    }

    header("Location: adjuntar_factura.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adjuntar Factura</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">
    <h2>Adjuntar Factura</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message']['type']; ?>">
            <?php echo $_SESSION['message']['text']; ?>
        </div>
    <?php unset($_SESSION['message']); endif; ?>

    <form action="adjuntar_factura.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="id_pedido" class="form-label">Seleccionar Pedido</label>
            <select class="form-select" id="id_pedido" name="id_pedido" required>
                <option value="">Selecciona un pedido</option>
                <?php foreach ($pedidos as $pedido): ?>
                    <option value="<?php echo htmlspecialchars($pedido['id_pedido']); ?>">
                        Pedido #<?php echo htmlspecialchars($pedido['id_pedido']); ?> - Q. <?php echo number_format($pedido['monto_total'], 2); ?> - <?php echo htmlspecialchars($pedido['fecha_pedido']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="factura" class="form-label">Subir Factura</label>
            <input type="file" class="form-control" id="factura" name="factura" required>
        </div>
        <button type="submit" class="btn btn-primary">Adjuntar Factura</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
