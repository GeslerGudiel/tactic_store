<?php
//Zona horaria del sistema
date_default_timezone_set('America/Guatemala');

// Configuración de la base de datos
$host = 'localhost';
$username = 'root';  // Usuario de MySQL
$password = '';  // Contraseña de MySQL
$database = 'emprendedores_db';

// Nombre del archivo de respaldo con la fecha actual
$backupFile = 'backup_' . $database . '_' . date('Y-m-d_H-i-s') . '.sql';
$backupDir = realpath(__DIR__ . '/backups/'); // Directorio donde se guardará el respaldo
$backupPath = $backupDir . '/' . $backupFile;

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

// Verificar si el archivo de copia de seguridad se ha creado
header('Content-Type: application/json');
if (file_exists($backupPath)) {
    // Respuesta JSON con éxito
    echo json_encode([
        'status' => 'success',
        'message' => 'Copia de seguridad creada correctamente'
    ]);
} else {
    // Respuesta JSON con error
    echo json_encode([
        'status' => 'error',
        'message' => 'Hubo un problema al crear la copia de seguridad'
    ]);
}
exit;
