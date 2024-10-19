<?php
include_once '../../src/config/database.php';

$database = new Database(); 
$db = $database->getConnection();

// Obtener parámetros de búsqueda y filtro de estado
$searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';
$estado = isset($_GET['estado']) ? $_GET['estado'] : '';

// Consulta base
$query = "SELECT e.id_emprendedor, e.nombre1, e.apellido1, e.correo, e.telefono1, e.id_estado_usuario, eu.nombre_estado 
          FROM emprendedor e 
          JOIN estado_usuario eu ON e.id_estado_usuario = eu.id_estado_usuario
          WHERE (e.nombre1 LIKE :searchTerm OR e.apellido1 LIKE :searchTerm OR e.correo LIKE :searchTerm)";

// Agregar filtro de estado si se selecciona
if ($estado !== '') {
    $query .= " AND e.id_estado_usuario = :estado";
}

// Preparar la consulta
$stmt = $db->prepare($query);
$searchTerm = "%$searchTerm%";
$stmt->bindParam(':searchTerm', $searchTerm);

// Solo agregar el filtro de estado si fue seleccionado
if ($estado !== '') {
    $stmt->bindParam(':estado', $estado, PDO::PARAM_INT);
}

$stmt->execute();
$emprendedores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Mostrar resultados
if (count($emprendedores) > 0) {
    echo "<table class='table table-bordered'>
            <thead>
                <tr>
                    <th><i class='fas fa-id-card'></i> ID</th>
                    <th><i class='fas fa-user'></i> Nombre</th>
                    <th><i class='fas fa-envelope'></i> Correo</th>
                    <th><i class='fas fa-phone'></i> Teléfono</th>
                    <th><i class='fas fa-info-circle'></i> Estado Actual</th>
                    <th><i class='fas fa-tasks'></i> Acciones</th>
                </tr>
            </thead>
            <tbody>";
    foreach ($emprendedores as $emprendedor) {
        echo "<tr>
                <td>{$emprendedor['id_emprendedor']}</td>
                <td>" . htmlspecialchars($emprendedor['nombre1'] . " " . $emprendedor['apellido1']) . "</td>
                <td>" . htmlspecialchars($emprendedor['correo']) . "</td>
                <td>" . htmlspecialchars($emprendedor['telefono1']) . "</td>
                <td>" . htmlspecialchars($emprendedor['nombre_estado']) . "</td>
                <td>
                    <button class='btn btn-info btn-sm revisar-emprendedor' data-id='{$emprendedor['id_emprendedor']}'>
                        <i class='fas fa-search'></i> Revisar
                    </button>
                    <form method='POST' action='gestion_emprendedores.php' style='display:inline-block;'>
                        <select name='nuevo_estado' class='form-select form-select-sm' style='width:auto; display:inline-block;'>
                            <option value='1' " . ($emprendedor['id_estado_usuario'] == 1 ? 'selected' : '') . ">Pendiente de Activación</option>
                            <option value='2' " . ($emprendedor['id_estado_usuario'] == 2 ? 'selected' : '') . ">Activado</option>
                            <option value='3' " . ($emprendedor['id_estado_usuario'] == 3 ? 'selected' : '') . ">Pendiente de Validación</option>
                            <option value='4' " . ($emprendedor['id_estado_usuario'] == 4 ? 'selected' : '') . ">Desactivado</option>
                        </select>
                        <input type='hidden' name='id_emprendedor' value='{$emprendedor['id_emprendedor']}'>
                        <button type='submit' class='btn btn-warning btn-sm'>
                            <i class='fas fa-sync-alt'></i> Actualizar Estado
                        </button>
                    </form>
                </td>
              </tr>";
    }
    echo "</tbody></table>";
} else {
    echo "<p class='alert alert-warning'>No se encontraron emprendedores.</p>";
}
?>
