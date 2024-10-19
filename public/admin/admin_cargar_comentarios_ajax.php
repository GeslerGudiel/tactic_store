<?php
session_start();
include_once '../../src/config/database.php';

// Verificar si el usuario es administrador o superadmin
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    echo 'No autorizado';
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Obtener las fechas del filtro
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : null;
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : null;

// Construir la consulta SQL
$query = "
    SELECT c.id_comentario, c.id_producto, c.id_cliente, c.comentario, c.calificacion, c.fecha_comentario, c.respuesta, 
           p.nombre_producto, e.nombre1 AS nombre_emprendedor, e.apellido1 AS apellido_emprendedor, cli.nombre1, cli.apellido1
    FROM comentario c
    INNER JOIN producto p ON c.id_producto = p.id_producto
    INNER JOIN cliente cli ON c.id_cliente = cli.id_cliente
    INNER JOIN emprendedor e ON p.id_emprendedor = e.id_emprendedor
    WHERE 1=1";  // WHERE 1=1 para agregar condiciones dinámicamente

// Filtrar por rango de fechas
if (!empty($fecha_inicio)) {
    $query .= " AND c.fecha_comentario >= :fecha_inicio";
}
if (!empty($fecha_fin)) {
    $query .= " AND c.fecha_comentario <= :fecha_fin";
}

$query .= " ORDER BY c.fecha_comentario DESC";

$stmt = $db->prepare($query);

// Asignar parámetros de fecha si existen
if (!empty($fecha_inicio)) {
    $stmt->bindParam(':fecha_inicio', $fecha_inicio);
}
if (!empty($fecha_fin)) {
    $stmt->bindParam(':fecha_fin', $fecha_fin);
}

$stmt->execute();
$comentarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (count($comentarios) > 0): 
    foreach ($comentarios as $comentario): ?>
        <div class="comentario-box">
            <h5><i class="fas fa-box"></i> <?php echo htmlspecialchars($comentario['nombre_producto']); ?> (Calificación: <?php echo htmlspecialchars($comentario['calificacion']); ?>/5)</h5>
            <p><strong><i class="fas fa-user-tie"></i> Emprendedor:</strong> <?php echo htmlspecialchars($comentario['nombre_emprendedor']) . ' ' . htmlspecialchars($comentario['apellido_emprendedor']); ?></p>
            <p><strong><i class="fas fa-user"></i> Cliente:</strong> <?php echo htmlspecialchars($comentario['nombre1']) . ' ' . htmlspecialchars($comentario['apellido1']); ?></p>
            <p><strong><i class="fas fa-comment-alt"></i> Comentario:</strong> <?php echo htmlspecialchars($comentario['comentario']); ?></p>
            <p><small><i class="fas fa-calendar-alt"></i> <strong>Fecha:</strong> <?php echo htmlspecialchars($comentario['fecha_comentario']); ?></small></p>
            <?php if (!empty($comentario['respuesta'])): ?>
                <p><strong><i class="fas fa-reply"></i> Respuesta del Emprendedor:</strong> <?php echo htmlspecialchars($comentario['respuesta']); ?></p>
            <?php endif; ?>
            <form class="responder-form" action="admin_responder_comentario.php" method="POST">
                <input type="hidden" name="id_comentario" value="<?php echo $comentario['id_comentario']; ?>">
                <textarea name="respuesta" class="form-control mb-2" placeholder="Responder o modificar respuesta..." required></textarea>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-reply"></i> Responder o Modificar Respuesta</button>
            </form>
            <form class="eliminar-form" action="admin_eliminar_comentario.php" method="POST" style="margin-top: 10px;">
                <input type="hidden" name="id_comentario" value="<?php echo $comentario['id_comentario']; ?>">
                <button type="submit" class="btn btn-danger btn-sm">
                    <i class="fas fa-trash-alt"></i> Eliminar Comentario
                </button>
            </form>
        </div>
    <?php endforeach; 
else: ?>
    <p class="text-center"><i class="fas fa-info-circle"></i> No hay comentarios disponibles.</p>
<?php endif; ?>
