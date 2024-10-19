<?php
session_start();
include_once '../../src/config/database.php';

if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Obtener el mes seleccionado del formulario o tomar el mes actual por defecto
$mes_seleccionado = isset($_GET['mes_seleccionado']) ? $_GET['mes_seleccionado'] : date('m');
$anio_actual = date('Y');

// Calcular el primer y último día del mes seleccionado
$fecha_inicio = "$anio_actual-$mes_seleccionado-01";
$fecha_fin = date("Y-m-t", strtotime($fecha_inicio));

// Obtener todas las comisiones de los emprendedores
$query = "SELECT p.id_pedido, SUM(c.monto_comision) AS total_comision, p.fecha_pedido, p.estado_pedido, GROUP_CONCAT(c.id_comision) AS id_comisiones, c.estado_comision, c.comprobante_pago, c.fecha_pago,
                 e.nombre1, e.apellido1, n.nombre_negocio
          FROM comision c
          INNER JOIN pedido p ON c.id_pedido = p.id_pedido
          INNER JOIN emprendedor e ON c.id_emprendedor = e.id_emprendedor
          INNER JOIN negocio n ON e.id_emprendedor = n.id_emprendedor
          WHERE p.fecha_pedido BETWEEN :fecha_inicio AND :fecha_fin
          GROUP BY p.id_pedido, p.fecha_pedido, p.estado_pedido, e.nombre1, e.apellido1, n.nombre_negocio
          ORDER BY p.fecha_pedido DESC";

$stmt = $db->prepare($query);
$stmt->bindParam(':fecha_inicio', $fecha_inicio);
$stmt->bindParam(':fecha_fin', $fecha_fin);
$stmt->execute();
$comisiones = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Comisiones</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>

<body>
    <div class="container mt-4">
        <h1 class="text-center mb-4"><i class="fas fa-money-bill-wave"></i> Gestionar Comisiones</h1>

        <form id="filtro-mes-form" class="mb-4">
            <label for="mes_seleccionado" class="form-label">Seleccionar Mes</label>
            <select id="mes_seleccionado" name="mes_seleccionado" class="form-select">
                <?php
                // Generar las opciones de los meses
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

                foreach ($meses as $mes_num => $mes_nombre) {
                    // Si el mes está seleccionado, lo marcamos como seleccionado en el dropdown
                    $selected = ($mes_num == $mes_seleccionado) ? 'selected' : '';
                    echo "<option value='$mes_num' $selected>$mes_nombre $anio_actual</option>";
                }
                ?>
            </select>
        </form>


        <div id="comisiones-contenido">
            <?php if (count($comisiones) > 0): ?>
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th><i class="fas fa-hashtag"></i> ID Pedido</th>
                            <th><i class="fas fa-user"></i> Emprendedor</th>
                            <th><i class="fas fa-store"></i> Negocio</th>
                            <th><i class="fas fa-coins"></i> Monto Comisión</th>
                            <th><i class="fas fa-info-circle"></i> Estado Pedido</th>
                            <th><i class="fas fa-calendar-alt"></i> Fecha Pedido</th>
                            <th><i class="fas fa-info"></i> Estado Comisión</th>
                            <th><i class="fas fa-file-invoice-dollar"></i> Comprobante pago</th>
                            <th><i class="fas fa-calendar-day"></i> Fecha pago</th>
                            <th><i class="fas fa-tasks"></i> Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($comisiones as $comision): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($comision['id_pedido']); ?></td>
                                <td><?php echo htmlspecialchars($comision['nombre1'] . ' ' . $comision['apellido1']); ?></td>
                                <td><?php echo htmlspecialchars($comision['nombre_negocio']); ?></td>
                                <td>Q. <?php echo number_format($comision['total_comision'], 2); ?></td>
                                <td><?php echo htmlspecialchars($comision['estado_pedido']); ?></td>
                                <td><?php echo htmlspecialchars($comision['fecha_pedido']); ?></td>
                                <td>
                                    <?php
                                    switch ($comision['estado_comision']) {
                                        case 'Pagada':
                                            echo '<i class="fas fa-check-circle text-success"></i> Pagada';
                                            break;
                                        case 'Pendiente':
                                            echo '<i class="fas fa-hourglass-half text-warning"></i> Pendiente';
                                            break;
                                        case 'Rechazada':
                                            echo '<i class="fas fa-times-circle text-danger"></i> Rechazada';
                                            break;
                                        default:
                                            echo '<i class="fas fa-question-circle text-secondary"></i> Desconocido';
                                            break;
                                    }
                                    ?>
                                </td>
                                <td>
                                    <?php if (!empty($comision['comprobante_pago'])): ?>
                                        <a href="../../uploads/comprobantes_comision/<?php echo htmlspecialchars($comision['comprobante_pago']); ?>" target="_blank">Ver Comprobante</a>
                                    <?php else: ?>
                                        <span class="text-danger">No disponible</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($comision['fecha_pago']); ?></td>
                                <td>
                                    <form class="upload-comprobante-form" enctype="multipart/form-data">
                                        <input type="hidden" name="id_comisiones" value="<?php echo htmlspecialchars($comision['id_comisiones']); ?>">
                                        <input type="file" name="comprobante_pago" class="form-control form-control-sm mb-2" required>
                                        <button type="submit" class="btn btn-success btn-sm"><i class="fas fa-upload"></i></button>
                                    </form>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center"><i class="fas fa-info-circle"></i> No hay comisiones registradas.</p>
            <?php endif; ?>
        </div>

    </div>

    <!-- jQuery para manejar la subida de archivos con AJAX -->
    <script>
        $(document).ready(function() {
            // Manejar el cambio de mes y cargar las comisiones usando AJAX
            $('#mes_seleccionado').on('change', function() {
                var mesSeleccionado = $(this).val();

                // Cargar el contenido filtrado de comisiones
                $('#comisiones-contenido').load('gestion_comisiones.php?mes_seleccionado=' + mesSeleccionado + ' #comisiones-contenido');
            });
        });

        $(document).on('submit', '.upload-comprobante-form', function(e) {
            e.preventDefault();
            var formData = new FormData(this);

            $.ajax({
                url: 'procesar_comision.php',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    var res = JSON.parse(response);
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: res.message
                        }).then(function() {
                            // Recargar solo la tabla de comisiones
                            $('#comisiones-contenido').load('gestion_comisiones.php #comisiones-contenido');
                            $('#comisiones-contenido').load('gestion_comisiones.php?mes_seleccionado=' + $('#mes_seleccionado').val() + ' #comisiones-contenido');

                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: res.message
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un error al procesar la solicitud.'
                    });
                }
            });
        });
    </script>

</body>

</html>