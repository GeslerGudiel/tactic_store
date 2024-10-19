<?php
session_start();

// Configurar la zona horaria
date_default_timezone_set('America/Guatemala');

// Verificar el rol del usuario
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

// Directorio donde se almacenan las copias de seguridad
$backupDir = realpath(__DIR__ . '/../../backups/backups/');

// Verificar si el directorio de backups existe
if (!is_dir($backupDir)) {
    die("El directorio de copias de seguridad no existe o no se puede acceder.");
}

// Obtener la lista de archivos de respaldo
$backups = glob($backupDir . '/*.sql');
usort($backups, function ($a, $b) {
    return filemtime($b) - filemtime($a);
});

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Copias de Seguridad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div class="container mt-5">
        <h2><i class="fas fa-database"></i> Gestión de Copias de Seguridad</h2>

        <!-- Crear Copia de Seguridad -->
        <div class="row mt-4">
            <div class="col-md-6">
                <h4><i class="fas fa-save"></i> Crear Copia de Seguridad</h4>
                <button id="crearBackupBtn" class="btn btn-primary">
                    <i class="fas fa-download"></i> Crear copia de seguridad
                </button>
            </div>

            <!-- Restaurar Copia de Seguridad -->
            <div class="col-md-6">
                <h4><i class="fas fa-upload"></i> Restaurar Copia de Seguridad</h4>
                <form id="restaurarBackupForm" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="backupFile" class="form-label">Seleccionar archivo de respaldo (.sql)</label>
                        <input type="file" class="form-control" id="backupFile" name="backupFile" accept=".sql" required>
                    </div>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-upload"></i> Restaurar copia de seguridad
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de Copias de Seguridad -->
        <div class="mt-5">
            <h4><i class="fas fa-archive"></i> Copias de Seguridad Realizadas</h4>
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Nombre del archivo</th>
                        <th>Fecha de creación</th>
                        <th>Tamaño</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($backups)): ?>
                        <?php foreach ($backups as $backup): ?>
                            <tr>
                                <?php
                                $fileName = basename($backup);
                                $fileSize = filesize($backup);
                                $fileDate = date("Y-m-d H:i:s", filemtime($backup));
                                ?>
                                <td><?php echo $fileName; ?></td>
                                <td><?php echo $fileDate; ?></td>
                                <td><?php echo round($fileSize / 1024, 2); ?> KB</td>
                                <td>
                                    <a href="../../backups/backups/<?php echo $fileName; ?>" class="btn btn-success btn-sm" download>
                                        <i class="fas fa-download"></i> Descargar
                                    </a>
                                    <button class="btn btn-danger btn-sm" onclick="eliminarBackup('<?php echo urlencode($fileName); ?>')">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No hay copias de seguridad realizadas.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        // Crear backup usando AJAX
        document.getElementById('crearBackupBtn').addEventListener('click', function() {
            fetch('../../backups/backups_database_ajax.php', {
                    method: 'POST'
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        icon: data.status === 'success' ? 'success' : 'error',
                        title: data.status === 'success' ? 'Éxito' : 'Error',
                        text: data.message
                    }).then(() => {
                        if (data.status === 'success') {
                            // Recargar solo la tabla de backups
                            cargarTablaBackups();
                        }
                    });
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al crear el backup.'
                    });
                });
        });

        // Función para recargar la tabla de backups
        function cargarTablaBackups() {
            fetch('listar_backups.php') // Archivo que actualiza la lista de copias
                .then(response => response.text())
                .then(html => {
                    document.querySelector('table tbody').innerHTML = html;
                })
                .catch(error => {
                    console.error('Error al cargar la tabla de backups:', error);
                });
        }

        // Restaurar backup usando AJAX
        document.getElementById('restaurarBackupForm').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);

            fetch('restore_backup.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        icon: data.status === 'success' ? 'success' : 'error',
                        title: data.status === 'success' ? 'Éxito' : 'Error',
                        text: data.message
                    });
                })
                .catch(error => {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Hubo un problema al restaurar el backup.'
                    });
                });
        });

        // Eliminar backup usando AJAX
        function eliminarBackup(fileName) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esto",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminarlo'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('delete_backup.php?file=' + fileName)
                        .then(response => response.json())
                        .then(data => {
                            Swal.fire({
                                icon: data.status === 'success' ? 'success' : 'error',
                                title: data.status === 'success' ? 'Éxito' : 'Error',
                                text: data.message
                            }).then(() => {
                                if (data.status === 'success') {
                                    // Recargar solo la tabla de backups
                                    cargarTablaBackups();
                                }
                            });
                        })
                        .catch(error => {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Hubo un problema al eliminar el backup.'
                            });
                        });
                }
            });
        }
    </script>
</body>

</html>