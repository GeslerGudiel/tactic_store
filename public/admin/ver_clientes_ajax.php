<?php
include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Variables para la búsqueda
$termino_busqueda = isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : '';
$estado_filtro = isset($_GET['estado']) ? $_GET['estado'] : '';

try {
    // Consultar los clientes con filtros
    $query = "SELECT c.*, es.nombre_estado AS estado_nombre
              FROM cliente c
              LEFT JOIN estado_usuario es ON c.id_estado_usuario = es.id_estado_usuario
              WHERE (c.nombre1 LIKE :buscar OR c.apellido1 LIKE :buscar OR c.NIT LIKE :buscar OR c.correo LIKE :buscar)";

    // Filtrar por estado si se seleccionó uno
    if (!empty($estado_filtro)) {
        $query .= " AND c.id_estado_usuario = :estado";
    }

    $stmt = $db->prepare($query);
    $stmt->bindValue(':buscar', "%$termino_busqueda%", PDO::PARAM_STR);

    if (!empty($estado_filtro)) {
        $stmt->bindParam(':estado', $estado_filtro, PDO::PARAM_INT);
    }

    $stmt->execute();
    $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($clientes)) {
        echo '<table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th><i class="fas fa-id-card"></i> NIT</th>
                        <th><i class="fas fa-user"></i> Nombre Completo</th>
                        <th><i class="fas fa-envelope"></i> Correo</th>
                        <th><i class="fas fa-phone"></i> Teléfono 1</th>
                        <th><i class="fas fa-phone-alt"></i> Teléfono 2</th>
                        <th><i class="fas fa-info-circle"></i> Estado</th>
                        <th><i class="fas fa-tasks"></i> Acciones</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($clientes as $cliente) {
            echo '<tr>
                    <td>' . htmlspecialchars($cliente['NIT']) . '</td>
                    <td>' . htmlspecialchars($cliente['nombre1'] . ' ' . $cliente['apellido1']) . '</td>
                    <td>' . htmlspecialchars($cliente['correo']) . '</td>
                    <td>' . htmlspecialchars($cliente['telefono1']) . '</td>
                    <td>' . htmlspecialchars($cliente['telefono2']) . '</td>
                    <td>' . htmlspecialchars($cliente['estado_nombre']) . '</td>
                    <td>
                        <a href="#" class="btn btn-info btn-sm ver-detalles-cliente" data-id="' . $cliente['id_cliente'] . '" title="Ver Detalles">
                            <i class="fas fa-eye"></i> Ver Detalles
                        </a>
                        <a href="#" class="btn btn-primary btn-sm editar-cliente" data-id="' . $cliente['id_cliente'] . '" title="Editar">
                            <i class="fas fa-edit"></i> Editar
                        </a>
                        <a href="#" class="btn btn-warning btn-sm cambiar-estado" data-id="' . $cliente['id_cliente'] . '" title="Cambiar Estado">
                            <i class="fas fa-sync-alt"></i> ' . ($cliente['id_estado_usuario'] == 2 ? 'Desactivar' : 'Activar') . '
                        </a>
                        <a href="#" class="btn btn-danger btn-sm eliminar-cliente" data-id="' . $cliente['id_cliente'] . '" title="Eliminar">
                            <i class="fas fa-trash-alt"></i> Eliminar
                        </a>
                    </td>
                  </tr>';
        }

        echo '</tbody></table>';
    } else {
        echo '<p class="text-center"><i class="fas fa-info-circle"></i> No se encontraron clientes.</p>';
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => 'Error al obtener los datos: ' . $e->getMessage()]);
}
?>
