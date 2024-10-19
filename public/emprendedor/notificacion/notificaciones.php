<?php
session_start();
include_once '../../../src/config/database.php';

if (!isset($_SESSION['id_emprendedor'])) {
    header("Location: ../../auth/login.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

$fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-d', strtotime('monday this week'));
$fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-d', strtotime('sunday this week'));
$estado = isset($_GET['estado']) ? $_GET['estado'] : 'todas';

// Construcción de la consulta SQL según el estado seleccionado
$query = "SELECT * FROM notificacion 
          WHERE id_emprendedor = :id_emprendedor 
          AND fecha BETWEEN :fecha_inicio AND :fecha_fin";

if ($estado === 'leidas') {
    $query .= " AND leido = 1";
} elseif ($estado === 'no_leidas') {
    $query .= " AND leido = 0";
}

$query .= " ORDER BY fecha DESC";

$stmt = $db->prepare($query);
$stmt->bindParam(':id_emprendedor', $_SESSION['id_emprendedor']);
$stmt->bindParam(':fecha_inicio', $fechaInicio);
$stmt->bindParam(':fecha_fin', $fechaFin);
$stmt->execute();
$notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2><i class="fas fa-bell"></i> Notificaciones</h2>

    <form id="filtro-fechas-form" class="row mb-4">
        <div class="col-md-4">
            <label for="fecha_inicio" class="form-label">Fecha Inicio:</label>
            <input type="date" id="fecha_inicio" class="form-control" value="<?php echo htmlspecialchars($fechaInicio); ?>">
        </div>
        <div class="col-md-4">
            <label for="fecha_fin" class="form-label">Fecha Fin:</label>
            <input type="date" id="fecha_fin" class="form-control" value="<?php echo htmlspecialchars($fechaFin); ?>">
        </div>

        <div class="col-md-4 d-flex align-items-center">
            <div class="form-check me-3">
                <input class="form-check-input" type="radio" name="estado" id="estado-todas" value="todas" checked>
                <label class="form-check-label" for="estado-todas">Todas</label>
            </div>
            <div class="form-check me-3">
                <input class="form-check-input" type="radio" name="estado" id="estado-leidas" value="leidas">
                <label class="form-check-label" for="estado-leidas">Leídas</label>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="estado" id="estado-no-leidas" value="no_leidas">
                <label class="form-check-label" for="estado-no-leidas">No Leídas</label>
            </div>
        </div>

        <div class="col-md-2 d-flex align-items-end">
            <button type="button" id="aplicar-filtro" class="btn btn-primary w-100">Filtrar</button>
        </div>
    </form>

    <div id="spinner" style="display: none; text-align: center;">
        <div class="spinner-border" role="status">
            <span class="visually-hidden">Cargando...</span>
        </div>
    </div>

    <div class="notificaciones-container">
        <?php if (count($notificaciones) > 0): ?>
            <?php foreach ($notificaciones as $notificacion): ?>
                <div class="card mb-3 notification <?php echo $notificacion['leido'] ? 'border-secondary' : 'border-primary'; ?>">
                    <div class="card-body">
                        <h5 class="card-title">
                            <i class="fas <?php echo $notificacion['leido'] ? 'fa-envelope-open-text' : 'fa-envelope'; ?>"></i>
                            <?php echo htmlspecialchars($notificacion['titulo']); ?>
                        </h5>
                        <p class="card-text"><?php echo htmlspecialchars($notificacion['mensaje']); ?></p>
                        <p class="card-text">
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> Fecha: <?php echo htmlspecialchars($notificacion['fecha']); ?>
                            </small>
                        </p>
                        <?php if (!$notificacion['leido']): ?>
                            <button class="btn btn-primary mark-as-read" data-id="<?php echo $notificacion['id_notificacion']; ?>">
                                <i class="fas fa-check"></i> Marcar como leída
                            </button>
                        <?php else: ?>
                            <span class="badge bg-secondary">Leída</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No tienes notificaciones en este rango de fechas.
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    $(document).ready(function() {
        let isProcessing = false; // Evitar solicitudes múltiples simultáneas

        // Aplicar filtro por rango de fechas
        $('#aplicar-filtro').click(function() {
            const fechaInicio = $('#fecha_inicio').val();
            const fechaFin = $('#fecha_fin').val();
            const estado = $('input[name="estado"]:checked').val();

            if (!fechaInicio || !fechaFin) {
                Swal.fire('Advertencia', 'Por favor selecciona ambas fechas.', 'warning');
                return;
            }
            if (new Date(fechaInicio) > new Date(fechaFin)) {
                Swal.fire('Advertencia', 'La fecha de inicio no puede ser mayor que la fecha de fin.', 'warning');
                return;
            }

            $('#spinner').show();
            $('#aplicar-filtro').prop('disabled', true);

            $.get('/comercio_electronico/public/emprendedor/notificacion/notificaciones.php', {
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                estado: estado
            }, function(data) {
                $('#content-area').html(data);
                $('#spinner').hide();
                $('#aplicar-filtro').prop('disabled', false);
            }).fail(function() {
                Swal.fire('Error', 'No se pudieron cargar las notificaciones.', 'error');
                $('#spinner').hide();
                $('#aplicar-filtro').prop('disabled', false);
            });
        });

        // Marcar notificación como leída
        $(document).on('click', '.mark-as-read', function() {
            if (isProcessing) return; // Evitar solicitudes duplicadas

            const button = $(this);
            const id = button.data('id');

            isProcessing = true; // Bloquear nuevas solicitudes
            button.prop('disabled', true); // Deshabilitar botón temporalmente

            $.post('/comercio_electronico/public/emprendedor/notificacion/marcar_notificacion_leida.php', {
                id_notificacion: id
            }, function(response) {
                if (response.success) {
                    Swal.fire('Éxito', 'Notificación marcada como leída.', 'success');
                    button.closest('.notification').remove(); // Eliminar la notificación de la vista
                } else {
                    Swal.fire('Error', 'No se pudo marcar como leída.', 'error');
                }
                isProcessing = false; // Liberar bloqueo
                button.prop('disabled', false); // Rehabilitar el botón
            }, 'json').fail(function() {
                Swal.fire('Error', 'Hubo un problema al procesar la solicitud.', 'error');
                isProcessing = false;
                button.prop('disabled', false);
            });
        });
    });
</script>