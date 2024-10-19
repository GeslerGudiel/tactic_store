<?php
session_start();

// Verificar si el usuario tiene rol de administrador o superadmin
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

// Incluir la conexión a la base de datos
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener el ID del emprendedor
$id_emprendedor = isset($_GET['id']) ? $_GET['id'] : die('ID de emprendedor no especificado.');

// Actualizar el estado del emprendedor a "Desactivado" (id_estado_usuario = 4)
$query = "UPDATE emprendedor SET id_estado_usuario = 4 WHERE id_emprendedor = :id_emprendedor";
$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $id_emprendedor);

if ($stmt->execute()) {
    $_SESSION['message'] = "El emprendedor ha sido rechazado.";
} else {
    $_SESSION['message'] = "Error al rechazar al emprendedor.";
}

header("Location: gestion_emprendedores.php");
exit;
?>
