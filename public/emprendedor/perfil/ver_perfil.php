<?php
session_start();
if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    header("Location: ../../auth/login.php");
    exit;
}

include_once '../../../src/config/config.php';

$emprendedor_id = $_SESSION['id_emprendedor'];

try {
    $database = new Database();
    $db = $database->getConnection();

    // Obtener datos del emprendedor
    $query_emprendedor = "SELECT e.*, s.tipo_suscripcion AS suscripcion_nombre, es.nombre_estado AS estado_nombre, b.nombre_banco AS nombre_banco
FROM emprendedor e
LEFT JOIN suscripcion s ON e.id_suscripcion = s.id_suscripcion
LEFT JOIN estado_usuario es ON e.id_estado_usuario = es.id_estado_usuario
LEFT JOIN banco b ON e.id_banco = b.id_banco
WHERE e.id_emprendedor = :emprendedor_id";

    $stmt_emprendedor = $db->prepare($query_emprendedor);
    $stmt_emprendedor->bindParam(':emprendedor_id', $emprendedor_id, PDO::PARAM_INT);
    $stmt_emprendedor->execute();
    $emprendedor = $stmt_emprendedor->fetch(PDO::FETCH_ASSOC);

    // Obtener dirección del emprendedor
    $query_direccion_emprendedor = "SELECT * FROM direccion WHERE id_direccion = :id_direccion";
    $stmt_direccion_emprendedor = $db->prepare($query_direccion_emprendedor);
    $stmt_direccion_emprendedor->bindParam(':id_direccion', $emprendedor['id_direccion'], PDO::PARAM_INT);
    $stmt_direccion_emprendedor->execute();
    $direccion_emprendedor = $stmt_direccion_emprendedor->fetch(PDO::FETCH_ASSOC);

    if (!$emprendedor) {
        echo "<p>No se encontraron datos del emprendedor.</p>";
        exit;
    }

    // Obtener datos del negocio
    $query_negocio = "SELECT * FROM negocio WHERE id_emprendedor = :emprendedor_id";
    $stmt_negocio = $db->prepare($query_negocio);
    $stmt_negocio->bindParam(':emprendedor_id', $emprendedor_id, PDO::PARAM_INT);
    $stmt_negocio->execute();
    $negocio = $stmt_negocio->fetch(PDO::FETCH_ASSOC);

    // Obtener dirección del negocio
    $query_direccion_negocio = "SELECT * FROM direccion WHERE id_direccion = :id_direccion";
    $stmt_direccion_negocio = $db->prepare($query_direccion_negocio);
    $stmt_direccion_negocio->bindParam(':id_direccion', $negocio['id_direccion'], PDO::PARAM_INT);
    $stmt_direccion_negocio->execute();
    $direccion_negocio = $stmt_direccion_negocio->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p>No se pudo cargar la información del perfil: " . $e->getMessage() . "</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil del Emprendedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .profile-section {
            margin-bottom: 30px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .profile-header h5 {
            margin: 0;
        }

        .profile-body {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .profile-body p {
            flex: 1 1 300px;
        }

        .scrollable-content {
            max-height: 70vh;
            overflow-y: auto;
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <h2 class="mb-4 text-center">Perfil del Emprendedor</h2>

        <div class="scrollable-content">

            <!-- Información del Emprendedor -->
            <div class="card profile-section">
                <div class="card-header profile-header">
                    <h5><i class="fas fa-user"></i> Datos Personales</h5>
                    <a href="#" id="editar-perfil-link" class="btn btn-primary btn-sm">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                </div>
                <div class="card-body profile-body">
                    <p><strong><i class="fas fa-id-card"></i> Nombre:</strong> <?php echo htmlspecialchars($emprendedor['nombre1'] . ' ' . $emprendedor['nombre2'] . ' ' . $emprendedor['nombre3']); ?></p>
                    <p><strong><i class="fas fa-id-card"></i> Apellidos:</strong> <?php echo htmlspecialchars($emprendedor['apellido1'] . ' ' . $emprendedor['apellido2']); ?></p>
                    <p><strong><i class="fas fa-envelope"></i> Correo:</strong> <?php echo htmlspecialchars($emprendedor['correo']); ?></p>
                    <p><strong><i class="fas fa-phone"></i> Teléfono 1:</strong> <?php echo htmlspecialchars($emprendedor['telefono1']); ?></p>
                    <p><strong><i class="fas fa-phone"></i> Teléfono 2:</strong> <?php echo htmlspecialchars($emprendedor['telefono2']); ?></p>
                    <p><strong><i class="fas fa-id-badge"></i> DPI:</strong> <?php echo htmlspecialchars($emprendedor['dpi']); ?></p>
                    <p><strong><i class="fas fa-map-marker-alt"></i> Dirección:</strong> <?php echo htmlspecialchars($direccion_emprendedor['localidad'] . ', ' . $direccion_emprendedor['municipio'] . ', ' . $direccion_emprendedor['departamento']); ?></p>
                    <p><strong><i class="fas fa-university"></i> Banco:</strong> <?php echo htmlspecialchars($emprendedor['nombre_banco']); ?></p>
                    <p><strong><i class="fas fa-money-check-alt"></i> No. de Cuenta Bancaria:</strong> <?php echo htmlspecialchars($emprendedor['no_cuenta_bancaria']); ?></p>
                    <p><strong><i class="fas fa-file-alt"></i> Tipo de Cuenta Bancaria:</strong> <?php echo htmlspecialchars($emprendedor['tipo_cuenta_bancaria']); ?></p>
                    <p><strong><i class="fas fa-user-tag"></i> Nombre de la Cuenta Bancaria:</strong> <?php echo htmlspecialchars($emprendedor['nombre_cuenta_bancaria']); ?></p>
                    <p><strong><i class="fas fa-calendar-alt"></i> Fecha de Creación:</strong> <?php echo htmlspecialchars($emprendedor['fecha_creacion']); ?></p>
                    <p><strong><i class="fas fa-file-pdf"></i> Documento de Identificación:</strong> <a href="../../uploads/dpi_docs/<?php echo htmlspecialchars($emprendedor['documento_identificacion']); ?>" target="_blank">Ver Documento</a></p>
                    <p><strong><i class="fas fa-user-check"></i> Estado de la Cuenta:</strong> <?php echo htmlspecialchars($emprendedor['estado_nombre']); ?></p>
                    <p><strong><i class="fas fa-star"></i> Tipo de Suscripción:</strong> <?php echo htmlspecialchars($emprendedor['suscripcion_nombre']); ?></p>
                </div>
            </div>

            <!-- Información del Negocio -->
            <div class="card profile-section">
                <div class="card-header profile-header">
                    <h5><i class="fas fa-store"></i> Datos del Negocio</h5>
                </div>
                <div class="card-body profile-body">
                    <p><strong><i class="fas fa-store"></i> Nombre del Negocio:</strong> <?php echo htmlspecialchars($negocio['nombre_negocio']); ?></p>
                    <p><strong><i class="fas fa-map-marker-alt"></i> Dirección del Negocio:</strong> <?php echo htmlspecialchars($direccion_negocio['localidad'] . ', ' . $direccion_negocio['municipio'] . ', ' . $direccion_negocio['departamento']); ?></p>
                    <p><strong><i class="fas fa-map-signs"></i> Referencia de Dirección:</strong> <?php echo htmlspecialchars($negocio['referencia_direccion']); ?></p>
                    <p><strong><i class="fas fa-file-signature"></i> Patente de Comercio:</strong> <?php echo htmlspecialchars($negocio['patente_comercio']); ?></p>
                    <p><strong><i class="fas fa-store-alt"></i> Tienda Física:</strong> <?php echo $negocio['tienda_fisica'] ? 'Sí' : 'No'; ?></p>
                    <p><strong><i class="fas fa-calendar-alt"></i> Fecha de Creación:</strong> <?php echo htmlspecialchars($negocio['fecha_creacion']); ?></p>
                    <?php if ($negocio['patente_comercio']): ?>
                        <p><strong><i class="fas fa-file-pdf"></i> Documento del Negocio:</strong> <a href="../../uploads/patente_docs/<?php echo htmlspecialchars($negocio['patente_comercio']); ?>" target="_blank">Ver Documento</a></p>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
</body>

</html>