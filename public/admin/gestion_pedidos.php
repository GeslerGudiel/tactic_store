<?php
session_start();
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection(); 

// Obtener el mes y el estado seleccionados del formulario
$mes_seleccionado = isset($_GET['mes_seleccionado']) ? $_GET['mes_seleccionado'] : date('m');
$estado_seleccionado = isset($_GET['estado_pedido']) ? $_GET['estado_pedido'] : ''; // Estado del pedido (si se selecciona)
$anio_actual = date('Y');

// Calcular el primer y último día del mes seleccionado
$fecha_inicio = "$anio_actual-$mes_seleccionado-01"; // Primer día del mes
$fecha_fin = date("Y-m-t", strtotime($fecha_inicio)); // Último día del mes

// Consulta para obtener el conteo de pedidos por estado
$query_estados = "SELECT estado_pedido, COUNT(*) as total 
                  FROM pedido 
                  WHERE fecha_pedido BETWEEN :fecha_inicio AND :fecha_fin 
                  GROUP BY estado_pedido";

$stmt_estados = $db->prepare($query_estados);
$stmt_estados->bindParam(':fecha_inicio', $fecha_inicio);
$stmt_estados->bindParam(':fecha_fin', $fecha_fin);
$stmt_estados->execute();
$conteo_estados = $stmt_estados->fetchAll(PDO::FETCH_ASSOC);

// Imprimir en consola para verificar el conteo de pedidos
echo "<script>console.log(" . json_encode($conteo_estados) . ");</script>";

// Consulta para obtener todos los pedidos dentro del rango de fechas y con el estado seleccionado (si aplica)
$query_pedidos = "SELECT p.id_pedido, p.fecha_pedido, p.estado_pedido, c.nombre1 AS nombre_cliente, c.apellido1 AS apellido_cliente 
          FROM pedido p 
          JOIN cliente c ON p.id_cliente = c.id_cliente 
          WHERE p.fecha_pedido BETWEEN :fecha_inicio AND :fecha_fin";

if (!empty($estado_seleccionado)) {
    $query_pedidos .= " AND p.estado_pedido = :estado_pedido"; // Filtro adicional por estado
}

$query_pedidos .= " ORDER BY p.fecha_pedido DESC";

$stmt_pedidos = $db->prepare($query_pedidos);
$stmt_pedidos->bindParam(':fecha_inicio', $fecha_inicio);
$stmt_pedidos->bindParam(':fecha_fin', $fecha_fin);
if (!empty($estado_seleccionado)) {
    $stmt_pedidos->bindParam(':estado_pedido', $estado_seleccionado);
}
$stmt_pedidos->execute();
$pedidos = $stmt_pedidos->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4 text-center"><i class="fas fa-shopping-cart"></i> Gestión de Pedidos</h1>

        <!-- Formulario de filtro de fechas -->
        <form id="filtro-mes-form" method="GET" class="mb-4">
            <div class="row">
                <div class="col-md-5">
                    <label for="mes_seleccionado" class="form-label">Seleccionar Mes</label>
                    <select id="mes_seleccionado" name="mes_seleccionado" class="form-select">
                        <?php
                        // Generar opciones de los meses
                        $meses = [
                            '01' => 'Enero',
                            '02' => 'Febrero',
                            '03' => 'Marzo',
                            '04' => 'Abril',
                            '05' => 'Mayo',
                            '06' => 'Junio',
                            '07' => 'Julio',
                            '08' => 'Agosto',
                            '09' => 'Septiembre',
                            '10' => 'Octubre',
                            '11' => 'Noviembre',
                            '12' => 'Diciembre'
                        ];

                        $mes_actual = date('m');
                        $anio_actual = date('Y');

                        foreach ($meses as $mes_num => $mes_nombre) {
                            // Si hay un mes seleccionado, seleccionamos ese en el dropdown
                            $selected = ($mes_num == (isset($_GET['mes_seleccionado']) ? $_GET['mes_seleccionado'] : $mes_actual)) ? 'selected' : '';
                            echo "<option value='$mes_num' $selected>$mes_nombre $anio_actual</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="col-md-5">
                    <label for="estado_pedido" class="form-label">Filtrar por Estado</label>
                    <select id="estado_pedido" name="estado_pedido" class="form-select">
                        <option value="">Todos</option>
                        <option value="Pendiente" <?= $estado_seleccionado == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                        <option value="En Proceso" <?= $estado_seleccionado == 'En Proceso' ? 'selected' : '' ?>>En Proceso</option>
                        <option value="Enviado" <?= $estado_seleccionado == 'Enviado' ? 'selected' : '' ?>>Enviado</option>
                        <option value="Entregado" <?= $estado_seleccionado == 'Entregado' ? 'selected' : '' ?>>Entregado</option>
                        <option value="Cancelado" <?= $estado_seleccionado == 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary mt-4">Filtrar</button>
                </div>
            </div>
        </form>

        <!-- Mostrar el conteo de pedidos por estado -->
        <div id="conteo-estados" class="mb-4">
            <?php if (!empty($conteo_estados)): ?>
                <?php foreach ($conteo_estados as $estado): ?>
                    <a href="#" class="btn btn-outline-secondary estado-link" data-estado="<?= htmlspecialchars($estado['estado_pedido']) ?>">
                        <?= htmlspecialchars($estado['estado_pedido']) ?>: <?= htmlspecialchars($estado['total']) ?>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
            <?php endif; ?>
        </div>

        <div id="pedidos-contenido">
            <?php if (empty($pedidos)): ?>
                <p class="alert alert-warning">No se encontraron pedidos en el rango de fechas seleccionado.</p>
            <?php else: ?>
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID Pedido</th>
                            <th><i class="fas fa-user"></i> Cliente</th>
                            <th><i class="fas fa-calendar-alt"></i> Fecha del Pedido</th>
                            <th><i class="fas fa-info-circle"></i> Estado</th>
                            <th><i class="fas fa-tasks"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $pedido): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($pedido['id_pedido']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['nombre_cliente'] . ' ' . $pedido['apellido_cliente']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['fecha_pedido']); ?></td>
                                <td><?php echo htmlspecialchars($pedido['estado_pedido']); ?></td>
                                <td>
                                    <a href="#" class="btn btn-info btn-sm ver-detalles" data-id="<?php echo htmlspecialchars($pedido['id_pedido']); ?>" title="Ver Detalles">
                                        <i class="fas fa-eye"></i> Ver Detalles
                                    </a>
                                </td>
                            </tr>
                            <!-- Fila oculta que se expandirá para mostrar los detalles -->
                            <tr id="detalle-pedido-<?php echo htmlspecialchars($pedido['id_pedido']); ?>" style="display: none;">
                                <td colspan="5">
                                    <div class="detalle-pedido-contenido"></div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- jQuery para cargar los detalles mediante AJAX -->
    <script>
        $(document).ready(function() {
            // Ver detalles del pedido
            $(document).on('click', '.ver-detalles', function(e) {
                e.preventDefault();

                // Obtener el ID del pedido
                var idPedido = $(this).data('id');
                var detallesFila = $('#detalle-pedido-' + idPedido);

                // Si la fila ya está visible, la ocultamos
                if (detallesFila.is(':visible')) {
                    detallesFila.hide();
                } else {
                    // Cargar los detalles con AJAX si no están ya visibles
                    $.ajax({
                        url: 'detalle_pedido_ajax.php',
                        method: 'GET',
                        data: {
                            id_pedido: idPedido
                        },
                        success: function(response) {
                            detallesFila.find('.detalle-pedido-contenido').html(response);
                            detallesFila.show();
                        },
                        error: function() {
                            Swal.fire('Error', 'Hubo un error al cargar los detalles del pedido', 'error');
                        }
                    });
                }
            });

            // Manejar el formulario de actualización de estado
            $(document).on('submit', '#form-estado-pedido', function(e) {
                e.preventDefault(); // Evitar el comportamiento predeterminado del formulario

                var formData = $(this).serialize(); // Serializar los datos del formulario
                var idPedido = $(this).find('input[name="id_pedido"]').val(); // Obtener el ID del pedido actual

                $.ajax({
                    url: 'cambiar_estado_pedido.php', // URL del archivo PHP que manejará la solicitud
                    method: 'POST',
                    data: formData,
                    success: function(response) {
                        try {
                            var res = JSON.parse(response); // Parsear la respuesta JSON
                            if (res.status === 'success') {
                                // Mostrar alerta de éxito y recargar el pedido
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Éxito',
                                    text: res.message
                                }).then(function() {
                                    $.ajax({
                                        url: 'detalle_pedido_ajax.php',
                                        method: 'GET',
                                        data: {
                                            id_pedido: idPedido
                                        },
                                        success: function(response) {
                                            $('#detalle-pedido-' + idPedido).find('.detalle-pedido-contenido').html(response);
                                        },
                                        error: function() {
                                            Swal.fire('Error', 'Hubo un error al cargar los detalles del pedido', 'error');
                                        }
                                    });
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: res.message
                                });
                            }
                        } catch (e) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Error inesperado: ' + e.message
                            });
                        }
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Hubo un error al intentar actualizar el estado del pedido.'
                        });
                    }
                });
            });

            // Manejar el filtro del formulario dinámicamente
            $('#filtro-mes-form').on('submit', function(e) {
                e.preventDefault();
                var formData = $(this).serialize();

                $.ajax({
                    url: 'gestion_pedidos.php',
                    method: 'GET',
                    data: formData,
                    success: function(response) {
                        $('#pedidos-contenido').html($(response).find('#pedidos-contenido').html());
                        $('#conteo-estados').html($(response).find('#conteo-estados').html());
                    },
                    error: function() {
                        Swal.fire('Error', 'Hubo un error al filtrar los pedidos.', 'error');
                    }
                });
            });

            // Manejar clic en los enlaces de conteo de estados
            $(document).on('click', '.estado-link', function(e) {
                e.preventDefault();
                var estadoSeleccionado = $(this).data('estado');
                var mesSeleccionado = $('#mes_seleccionado').val();

                $.ajax({
                    url: 'gestion_pedidos.php',
                    method: 'GET',
                    data: {
                        estado_pedido: estadoSeleccionado,
                        mes_seleccionado: mesSeleccionado
                    },
                    success: function(response) {
                        $('#pedidos-contenido').html($(response).find('#pedidos-contenido').html());
                        $('#conteo-estados').html($(response).find('#conteo-estados').html());
                    },
                    error: function() {
                        Swal.fire('Error', 'Hubo un error al filtrar los pedidos.', 'error');
                    }
                });
            });
        });
    </script>

</body>

</html>