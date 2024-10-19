<?php
// Configurar la zona horaria del sistema
date_default_timezone_set('America/Guatemala');
// Configuración del directorio de backups
$backupDir = realpath(__DIR__ . '/../../backups/backups/');

// Obtener la lista de archivos de respaldo
$backups = glob($backupDir . '/*.sql');
usort($backups, function ($a, $b) {
    return filemtime($b) - filemtime($a);
});

// Generar la tabla
if (!empty($backups)) {
    foreach ($backups as $backup) {
        $fileName = basename($backup);
        $fileSize = filesize($backup);
        $fileDate = date("Y-m-d H:i:s", filemtime($backup));
        echo "<tr>
                <td>{$fileName}</td>
                <td>{$fileDate}</td>
                <td>" . round($fileSize / 1024, 2) . " KB</td>
                <td>
                    <a href='../../backups/backups/{$fileName}' class='btn btn-success btn-sm' download>
                        <i class='fas fa-download'></i> Descargar
                    </a>
                    <button class='btn btn-danger btn-sm' onclick='eliminarBackup(\"" . urlencode($fileName) . "\")'>
                        <i class='fas fa-trash'></i> Eliminar
                    </button>
                </td>
            </tr>";
    }
} else {
    echo "<tr><td colspan='4' class='text-center'>No hay copias de seguridad realizadas.</td></tr>";
}
