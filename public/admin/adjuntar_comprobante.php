<?php
session_start();
include_once '../../src/config/database.php';

if (!isset($_SESSION['id_administrador'])) {
    header("Location: login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Obtener los pedidos pendientes de comprobante
$query = "SELECT p.id_pedido, p.fecha_pedido, e.nombre_emprendedor 
          FROM pedido p 
          INNER JOIN emprendedor e ON p.id_emprendedor = e.id_emprendedor
          WHERE p.estado_pedido = 'Pendiente Comprobante'";
$stmt = $db->prepare($query);
$stmt->execute();

$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_pedido = $_POST['id_pedido'];

    if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
        $extension = pathinfo($_FILES['comprobante']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = 'comprobante_' . $id_pedido . '.' . $extension;
        $ruta_destino = '../../uploads/comprobantes/' . $nombre_archivo;

        if (move_uploaded_file($_FILES['comprobante']['tmp_name'], $ruta_destino)) {
            $query_comprobante = "UPDATE pedido SET comprobante = :comprobante WHERE id_pedido = :id_pedido";
            $stmt_comprobante = $db->prepare($query_comprobante);
            $stmt_comprobante->bindParam(':comprobante', $nombre_archivo);
            $stmt_comprobante->bindParam(':id_pedido', $id_pedido);
            if ($stmt_comprobante->execute()) {
                $_SESSION['message'] = [
                    'type' => 'success',
                    'text' => 'Comprobante adjuntado con éxito.'
                ];
            } else {
                $_SESSION['message'] = [
                    'type' => 'error',
                    'text' => 'Error al adjuntar el comprobante en la base de datos.'
                ];
            }
        } else {
            $_SESSION['message'] = [
                'type' => 'error',
                'text' => 'Error al subir el comprobante.'
            ];
        }
    } else {
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => 'Debes seleccionar un archivo de comprobante.'
        ];
    }

    header("Location: adjuntar_comprobante.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adjuntar Comprobante</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container mt-5">
    <h2>Adjuntar Comprobante</h2>

    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message']['type']; ?>">
            <?php echo $_SESSION['message']['text']; ?>
        </div>
    <?php unset($_SESSION['message']); endif; ?>

    <form action="adjuntar_comprobante.php" method="POST" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="id_pedido" class="form-label">Seleccionar Pedido</label>
            <select class="form-select" id="id_pedido" name="id_pedido" required>
                <option value="">Selecciona un pedido</option>
                <?php foreach ($pedidos as $pedido): ?>
                    <option value="<?php echo htmlspecialchars($pedido['id_pedido']); ?>">
                        Pedido #<?php echo htmlspecialchars($pedido['id_pedido']); ?> - <?php echo htmlspecialchars($pedido['nombre_emprendedor']); ?> - <?php echo htmlspecialchars($pedido['fecha_pedido']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="comprobante" class="form-label">Subir Comprobante</label>
            <input type="file" class="form-control" id="comprobante" name="comprobante" required>
        </div>
        <button type="submit" class="btn btn-primary">Adjuntar Comprobante</button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
