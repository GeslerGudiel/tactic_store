<?php
session_start();

// Verificar si el usuario tiene rol de administrador o superadmin
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener los valores de búsqueda y filtro
$searchTerm = isset($_GET['searchTerm']) ? '%' . $_GET['searchTerm'] . '%' : '%%';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

// Consulta con búsqueda y filtro dinámico
$query = "
    SELECT n.*, e.nombre1, e.apellido1, d.departamento, d.municipio, d.localidad
    FROM negocio n
    JOIN emprendedor e ON n.id_emprendedor = e.id_emprendedor
    JOIN direccion d ON n.id_direccion = d.id_direccion
    WHERE (n.nombre_negocio LIKE :searchTerm OR e.nombre1 LIKE :searchTerm OR e.apellido1 LIKE :searchTerm OR d.localidad LIKE :searchTerm OR d.departamento LIKE :searchTerm OR d.municipio LIKE :searchTerm)
";

if ($estado !== '') {
    if ($estado == 1) {
        $query .= " AND n.tienda_fisica = 1";
    } else {
        $query .= " AND n.tienda_fisica = 0";
    }
}

$query .= " ORDER BY n.fecha_creacion DESC";

$stmt = $db->prepare($query);
$stmt->bindParam(':searchTerm', $searchTerm);
$stmt->execute();
$negocios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Generar el HTML de la tabla
if (count($negocios) > 0) {
    foreach ($negocios as $index => $negocio) {
        echo "
        <tr>
            <td class='text-center'>" . ($index + 1) . "</td>
            <td>" . htmlspecialchars($negocio['nombre_negocio']) . "</td>
            <td>" . htmlspecialchars($negocio['nombre1'] . ' ' . $negocio['apellido1']) . "</td>
            <td>" . htmlspecialchars($negocio['localidad'] . ', ' . $negocio['municipio'] . ', ' . $negocio['departamento']) . "</td>
            <td class='text-center'>" . ($negocio['patente_comercio'] ? "<a href='../../uploads/patente_docs/" . htmlspecialchars($negocio['patente_comercio']) . "' target='_blank' class='btn btn-info btn-sm'><i class='fas fa-eye'></i> Ver Patente</a>" : "<span class='text-muted'>No disponible</span>") . "</td>
            <td class='text-center'>" . ($negocio['tienda_fisica'] ? '<i class="fas fa-check text-success"></i> Sí' : '<i class="fas fa-times text-danger"></i> No') . "</td>
            <td>" . htmlspecialchars($negocio['fecha_creacion']) . "</td>
        </tr>";
    }
} else {
    echo "
    <tr>
        <td colspan='7' class='text-center'><i class='fas fa-info-circle'></i> No hay negocios registrados.</td>
    </tr>";
}
