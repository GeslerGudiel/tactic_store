<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

include_once '../../src/config/database.php';

$id_emprendedor = isset($_GET['id']) ? $_GET['id'] : die('ID de emprendedor no especificado.');

try {
    $database = new Database();
    $db = $database->getConnection();

    // Obtener datos del emprendedor
    $query_emprendedor = "SELECT e.*, s.tipo_suscripcion AS suscripcion_nombre, es.nombre_estado AS estado_nombre, b.nombre_banco AS nombre_banco
        FROM emprendedor e
        LEFT JOIN suscripcion s ON e.id_suscripcion = s.id_suscripcion
        LEFT JOIN estado_usuario es ON e.id_estado_usuario = es.id_estado_usuario
        LEFT JOIN banco b ON e.id_banco = b.id_banco
        WHERE e.id_emprendedor = :id_emprendedor";

    $stmt_emprendedor = $db->prepare($query_emprendedor);
    $stmt_emprendedor->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
    $stmt_emprendedor->execute();
    $emprendedor = $stmt_emprendedor->fetch(PDO::FETCH_ASSOC);

    // Obtener dirección del emprendedor
    $query_direccion_emprendedor = "SELECT * FROM direccion WHERE id_direccion = :id_direccion";
    $stmt_direccion_emprendedor = $db->prepare($query_direccion_emprendedor);
    $stmt_direccion_emprendedor->bindParam(':id_direccion', $emprendedor['id_direccion'], PDO::PARAM_INT);
    $stmt_direccion_emprendedor->execute();
    $direccion_emprendedor = $stmt_direccion_emprendedor->fetch(PDO::FETCH_ASSOC);

    // Obtener datos del negocio
    $query_negocio = "SELECT * FROM negocio WHERE id_emprendedor = :id_emprendedor";
    $stmt_negocio = $db->prepare($query_negocio);
    $stmt_negocio->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
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

    <div class="container my-5">
        <h2 class="mb-4 text-center">Revisión del Perfil del Emprendedor</h2>

        <form action="aprobar_rechazar_emprendedor.php" method="POST">
            <input type="hidden" name="id_emprendedor" value="<?php echo $id_emprendedor; ?>">

            <!-- Información del Emprendedor -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-user"></i> Datos Personales</h5>
                </div>
                <div class="card-body">
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($emprendedor['nombre1'] . ' ' . $emprendedor['nombre2'] . ' ' . $emprendedor['nombre3']); ?>
                        <input type="checkbox" name="aprobar_nombre" value="1" checked> Aprobar
                    </p>
                    <p><strong>Apellidos:</strong> <?php echo htmlspecialchars($emprendedor['apellido1'] . ' ' . $emprendedor['apellido2']); ?>
                        <input type="checkbox" name="aprobar_apellidos" value="1" checked> Aprobar
                    </p>
                    <p><strong>Teléfono 1:</strong> <?php echo htmlspecialchars($emprendedor['telefono1']); ?>
                        <input type="checkbox" name="aprobar_telefono1" value="1" checked> Aprobar
                    </p>
                    <p><strong>Teléfono 2:</strong> <?php echo htmlspecialchars($emprendedor['telefono2']); ?>
                        <input type="checkbox" name="aprobar_telefono2" value="1" checked> Aprobar
                    </p>
                    <p><strong>DPI:</strong> <?php echo htmlspecialchars($emprendedor['dpi']); ?>
                        <input type="checkbox" name="aprobar_dpi" value="1" checked> Aprobar
                    </p>
                    <p><strong>Documento de Identificación:</strong> <a href="../../uploads/dpi_docs/<?php echo htmlspecialchars($emprendedor['documento_identificacion']); ?>" target="_blank">Ver Documento</a>
                        <input type="checkbox" name="aprobar_documento" value="1" checked> Aprobar
                    </p>
                    <p><strong>Dirección del emprendedor:</strong> <?php echo htmlspecialchars($direccion_emprendedor['localidad'] . ', ' . $direccion_emprendedor['municipio'] . ', ' . $direccion_emprendedor['departamento']); ?>
                        <input type="checkbox" name="aprobar_direccion_emprendedor" value="1" checked> Aprobar
                    </p>
                    <p><strong>Banco:</strong> <?php echo htmlspecialchars($emprendedor['nombre_banco']); ?>
                        <input type="checkbox" name="aprobar_banco" value="1" checked> Aprobar
                    </p>
                    <p><strong>No. de Cuenta Bancaria:</strong> <?php echo htmlspecialchars($emprendedor['no_cuenta_bancaria']); ?>
                        <input type="checkbox" name="aprobar_cuenta_bancaria" value="1" checked> Aprobar
                    </p>
                    <p><strong>Tipo de Cuenta Bancaria:</strong> <?php echo htmlspecialchars($emprendedor['tipo_cuenta_bancaria']); ?>
                        <input type="checkbox" name="aprobar_tipo_cuenta" value="1" checked> Aprobar
                    </p>
                    <p><strong>Nombre de la Cuenta Bancaria:</strong> <?php echo htmlspecialchars($emprendedor['nombre_cuenta_bancaria']); ?>
                        <input type="checkbox" name="aprobar_nombre_cuenta" value="1" checked> Aprobar
                    </p>
                    <p><strong>Estado de la Cuenta:</strong> <?php echo htmlspecialchars($emprendedor['estado_nombre']); ?></p>
                    <p><strong>Fecha de Creación:</strong> <?php echo htmlspecialchars($emprendedor['fecha_creacion']); ?></p>
                    <p><strong>Tipo de Suscripción:</strong> <?php echo htmlspecialchars($emprendedor['suscripcion_nombre']); ?></p>
                </div>
            </div>

            <!-- Información del Negocio -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5><i class="fas fa-store"></i> Datos del Negocio</h5>
                </div>
                <div class="card-body">
                    <p><strong>Nombre del Negocio:</strong> <?php echo htmlspecialchars($negocio['nombre_negocio']); ?>
                        <input type="checkbox" name="aprobar_nombre_negocio" value="1" checked> Aprobar
                    </p>
                    <p><strong>Dirección del Negocio:</strong> <?php echo htmlspecialchars($direccion_negocio['localidad'] . ', ' . $direccion_negocio['municipio'] . ', ' . $direccion_negocio['departamento']); ?>
                        <input type="checkbox" name="aprobar_direccion_negocio" value="1" checked> Aprobar
                    </p>
                    <p><strong>Referencia de Dirección:</strong> <?php echo htmlspecialchars($negocio['referencia_direccion']); ?>
                        <input type="checkbox" name="aprobar_referencia" value="1" checked> Aprobar
                    </p>
                    <p><strong>Patente de Comercio:</strong> <a href="../../uploads/patente_docs/<?php echo htmlspecialchars($negocio['patente_comercio']); ?>" target="_blank">Ver Documento</a>
                        <input type="checkbox" name="aprobar_patente" value="1" checked> Aprobar
                    </p>
                    <p><strong>Tienda Física:</strong> <?php echo $negocio['tienda_fisica'] ? 'Sí' : 'No'; ?>
                        <input type="checkbox" name="aprobar_tienda_fisica" value="1" checked> Aprobar
                    </p>
                    <p><strong>Fecha de Creación:</strong> <?php echo htmlspecialchars($negocio['fecha_creacion']); ?></p>
                </div>
            </div>

            <!-- Botones de Aprobar/Rechazar -->
            <button type="submit" class="btn btn-success">Guardar Cambios</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
