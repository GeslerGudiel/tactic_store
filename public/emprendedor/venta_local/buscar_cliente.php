<?php
include_once '../../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();
$filtro = isset($_GET['filtro']) ? $_GET['filtro'] : '';

$query = "SELECT id_cliente_emprendedor, nombre_cliente, correo_cliente, telefono_cliente FROM cliente_emprendedor WHERE nombre_cliente LIKE :filtro";
$stmt = $db->prepare($query);
$stmt->bindValue(':filtro', "%$filtro%");
$stmt->execute();
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($clientes as $cliente) {
    echo '<li class="list-group-item cliente-item" 
            data-id="' . htmlspecialchars($cliente['id_cliente_emprendedor']) . '" 
            data-nombre="' . htmlspecialchars($cliente['nombre_cliente']) . '" 
            data-correo="' . htmlspecialchars($cliente['correo_cliente']) . '" 
            data-telefono="' . htmlspecialchars($cliente['telefono_cliente']) . '">'
         . htmlspecialchars($cliente['nombre_cliente']) . ' - ' . htmlspecialchars($cliente['telefono_cliente']) . 
         '</li>';
}
