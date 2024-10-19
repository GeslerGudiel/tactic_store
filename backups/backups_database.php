<?php
// Zona horaria del sistema
date_default_timezone_set('America/Guatemala'); // Cambia a la zona horaria de tu ubicación

// Configuración de la base de datos
$host = 'localhost';
$username = 'root';  // Usuario de MySQL
$password = '';  // Contraseña de MySQL
$database = 'emprendedores_db';

// Nombre del archivo de respaldo con la fecha actual
$backupFile = 'backup_' . $database . '_' . date('Y-m-d_H-i-s') . '.sql';
$backupDir = __DIR__ . '/backups/'; // Directorio donde se guardará el respaldo
$backupPath = $backupDir . $backupFile;

// Crear el directorio de respaldo si no existe
if (!file_exists($backupDir)) {
    mkdir($backupDir, 0777, true);
}

// Ruta completa de mysqldump
$mysqldumpPath = "C:/xampp/mysql/bin/mysqldump";

// Comando para realizar la copia de seguridad
$command = "$mysqldumpPath --host=$host --user=$username --password=$password $database > $backupPath";

// Ejecutar el comando y capturar el código de salida
exec($command, $output, $return_var);

// Verificar si la copia de seguridad se realizó correctamente
if ($return_var === 0) {
    // Redirigir con un mensaje de éxito
    header("Location: ../public/admin/backup_admin.php?status=success&message=Copia de seguridad creada correctamente");
    exit;
} else {
    // Depurar el comando en caso de error
    echo "Error al ejecutar el comando: $command";
    // Redirigir con un mensaje de error
    header("Location: ../public/admin/backup_admin.php?status=error&message=Error al crear la copia de seguridad");
    exit;
}
