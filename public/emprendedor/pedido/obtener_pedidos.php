<?php
session_start();
include_once '../../../src/config/database.php';

try {
    header("Cache-Control: no-cache, must-revalidate");
    header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

    $database = new Database();
    $db = $database->getConnection();

    $id_emprendedor = $_SESSION['id_emprendedor'] ?? null;
    if (!$id_emprendedor) {
        throw new Exception('El usuario no ha iniciado sesión correctamente.');
    }

    // Capturar fechas de filtro y número de pedido
    $fecha_inicio = $_GET['fecha_inicio'] ?? '';
    $fecha_fin = $_GET['fecha_fin'] ?? '';
    $numero_pedido = $_GET['numero_pedido'] ?? '';

    // Validar que la fecha de inicio no sea mayor que la fecha de fin
    if (!empty($fecha_inicio) && !empty($fecha_fin) && $fecha_inicio > $fecha_fin) {
        throw new Exception('La fecha de inicio no puede ser mayor que la fecha de fin.');
    }

    // Consulta SQL inicial
    $query = "
        SELECT 
            p.id_pedido, p.fecha_pedido, p.estado_pedido, 
            pa.estado_pago, dp.nombre_producto, dp.cantidad, 
            dp.precio_unitario, dp.subtotal, dp.factura_emprendedor
        FROM pedido p
        JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
        JOIN pago pa ON p.id_pedido = pa.id_pedido
        WHERE dp.id_emprendedor = :id_emprendedor";

    // Añadir filtros dinámicos según se proporcionen
    if (!empty($fecha_inicio) && !empty($fecha_fin)) {
        $query .= " AND p.fecha_pedido BETWEEN :fecha_inicio AND :fecha_fin";
    } elseif (!empty($fecha_inicio)) {
        $query .= " AND p.fecha_pedido >= :fecha_inicio";
    } elseif (!empty($fecha_fin)) {
        $query .= " AND p.fecha_pedido <= :fecha_fin";
    }

    if ($numero_pedido) {
        $query .= " AND p.id_pedido LIKE :numero_pedido";
    }

    $query .= " ORDER BY p.fecha_pedido DESC, p.id_pedido ASC";

    // Preparar la consulta
    $stmt = $db->prepare($query);
    $stmt->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);

    if (!empty($fecha_inicio)) {
        $stmt->bindParam(':fecha_inicio', $fecha_inicio);
    }
    if (!empty($fecha_fin)) {
        $stmt->bindParam(':fecha_fin', $fecha_fin);
    }
    if ($numero_pedido) {
        $numero_pedido = "%$numero_pedido%";
        $stmt->bindParam(':numero_pedido', $numero_pedido);
    }

    // Ejecutar la consulta
    $stmt->execute();
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($pedidos)) {
        echo '<p class="text-center">No tienes pedidos pendientes.</p>';
        exit;
    }

    // Renderizar pedidos agrupados
    $pedido_actual = null;
    foreach ($pedidos as $pedido) {
        if ($pedido_actual !== $pedido['id_pedido']) {
            if ($pedido_actual !== null) {
                echo '</tbody></table>';
                mostrarBotonFactura($pedido_actual, $pedido['factura_emprendedor']); // Mostrar botón o enlace de factura
                echo '<hr>';
            }

            $pedido_actual = $pedido['id_pedido'];
            echo "<h4><i class='fas fa-receipt'></i> Pedido #{$pedido['id_pedido']} - Fecha: {$pedido['fecha_pedido']}</h4>";
            echo "<table class='table table-bordered'>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Subtotal</th>
                            <th>Estado de Pago</th>
                            <th>Estado de Pedido</th>
                        </tr>
                    </thead>
                    <tbody>";
        }

        echo "<tr>
                <td>{$pedido['nombre_producto']}</td>
                <td>{$pedido['cantidad']}</td>
                <td>Q. " . number_format($pedido['precio_unitario'], 2) . "</td>
                <td>Q. " . number_format($pedido['subtotal'], 2) . "</td>
                <td>{$pedido['estado_pago']}</td>
                <td>{$pedido['estado_pedido']}</td>
            </tr>";
    }
    echo '</tbody></table>';
    mostrarBotonFactura($pedido_actual, $pedido['factura_emprendedor']); // Mostrar para el último pedido

} catch (Exception $e) {
    echo "<script>
            Swal.fire('Error', '{$e->getMessage()}', 'error');
          </script>";
    exit;
}

// Función para mostrar el botón o enlace de factura
function mostrarBotonFactura($id_pedido, $factura) {
    if (!empty($factura)) {
        echo "<a href='/comercio_electronico/uploads/facturas_emprendedores/{$factura}' 
                target='_blank' class='btn btn-info btn-sm'>
                <i class='fas fa-file-invoice'></i> Ver Factura
              </a>";
    } else {
        echo "<form class='form-subir-factura' enctype='multipart/form-data'>
                <input type='hidden' name='id_pedido' value='{$id_pedido}'>
                <div class='mb-3'>
                    <label for='factura_{$id_pedido}' class='form-label'>Subir Factura:</label>
                    <input type='file' class='form-control' id='factura_{$id_pedido}' name='factura' required>
                </div>
                <button type='submit' class='btn btn-success'>Subir Factura</button>
              </form>";
    }
}
?>
