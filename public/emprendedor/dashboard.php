<?php
session_start();

if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 1800)) {
    // Si la última actividad fue hace más de 30 minutos
    session_unset();
    session_destroy();
    header("Location: ../auth/login.php");
    exit;
}
$_SESSION['LAST_ACTIVITY'] = time(); // Actualizamos el timestamp

include_once '../../src/config/database.php';

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    header("Location: ../auth/login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$id_emprendedor = $_SESSION['id_emprendedor'];

// Consulta para contar notificaciones no leídas
$query = "SELECT COUNT(*) as no_leidas FROM notificacion WHERE id_emprendedor = :id_emprendedor AND leido = 0";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$notificaciones_no_leidas = $result['no_leidas'] ?? 0;

$message = null;

// Verificar si hay un mensaje de sesión
if (isset($_SESSION['message'])) {
    $message = $_SESSION['message'];
    unset($_SESSION['message']);
}

$page = isset($_GET['page']) ? $_GET['page'] : 'default';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$buscar = isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '';

// Verificar el estado del emprendedor
$estado_query = "SELECT id_estado_usuario FROM emprendedor WHERE id_emprendedor = :id_emprendedor";
$estado_stmt = $db->prepare($estado_query);
$estado_stmt->bindParam(':id_emprendedor', $id_emprendedor);
$estado_stmt->execute();
$estado_emprendedor = $estado_stmt->fetch(PDO::FETCH_ASSOC)['id_estado_usuario'];

?>

<?php include 'dashboard/partials/header.php'; ?>

<body>
    <?php include 'dashboard/partials/sidebar.php'; ?>
    <?php include 'dashboard/partials/content.php'; ?>
    <?php include 'dashboard/partials/footer.php'; ?>
</body>

</html>