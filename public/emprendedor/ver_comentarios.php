<?php
session_start();
include_once '../../src/config/database.php';

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

// Obtener los comentarios de los productos del emprendedor
$query = "
    SELECT c.id_comentario, c.id_producto, c.id_cliente, c.comentario, c.calificacion, c.fecha_comentario, c.respuesta, p.nombre_producto, cli.nombre1, cli.apellido1
    FROM comentario c
    INNER JOIN producto p ON c.id_producto = p.id_producto
    INNER JOIN cliente cli ON c.id_cliente = cli.id_cliente
    WHERE p.id_emprendedor = :id_emprendedor
    ORDER BY c.fecha_comentario DESC";
    
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $_SESSION['id_emprendedor']);
$stmt->execute();
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comentarios de Productos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .comentario-box {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h1>Comentarios de tus Productos</h1>
        <?php if (count($comentarios) > 0): ?>
            <?php foreach ($comentarios as $comentario): ?>
                <div class="comentario-box">
                    <h5><?php echo htmlspecialchars($comentario['nombre_producto']); ?> (Calificación: <?php echo htmlspecialchars($comentario['calificacion']); ?>/5)</h5>
                    <p><strong>Cliente:</strong> <?php echo htmlspecialchars($comentario['nombre1']) . ' ' . htmlspecialchars($comentario['apellido1']); ?></p>
                    <p><strong>Comentario:</strong> <?php echo htmlspecialchars($comentario['comentario']); ?></p>
                    <p><small><strong>Fecha:</strong> <?php echo htmlspecialchars($comentario['fecha_comentario']); ?></small></p>
                    <!-- Mostrar la respuesta si ya ha sido respondido -->
                    <?php if (!empty($comentario['respuesta'])): ?>
                        <p><strong>Mi respuesta:</strong> <?php echo htmlspecialchars($comentario['respuesta']); ?></p>
                    <?php endif; ?>
                    <!-- Agregar formulario para la respuesta -->
                    <form action="responder_comentario.php" method="POST">
                        <input type="hidden" name="id_comentario" value="<?php echo $comentario['id_comentario']; ?>">
                        <textarea name="respuesta" class="form-control mb-2" placeholder="Responder en nombre de la tienda..." required></textarea>
                        <button type="submit" class="btn btn-primary">Enviar Respuesta</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No hay comentarios en tus productos.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
