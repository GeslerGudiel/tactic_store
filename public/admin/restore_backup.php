<?php
// Configuración de la base de datos
$host = 'localhost';
$username = 'root';  // Usuario de MySQL
$password = '';  // Contraseña de MySQL
$database = 'emprendedores_db';

// Verificar si se ha subido un archivo
if (isset($_FILES['backupFile']) && $_FILES['backupFile']['error'] === UPLOAD_ERR_OK) {
    $backupFile = $_FILES['backupFile']['tmp_name'];
    $fileName = $_FILES['backupFile']['name'];

    // Comando para restaurar la copia de seguridad
    $mysqlPath = "C:/xampp/mysql/bin/mysql";  // Ajusta la ruta completa de mysql si es necesario
    $command = "\"$mysqlPath\" --host=$host --user=$username --password=$password $database < \"$backupFile\"";

    // Ejecutar el comando 
    $output = [];
    $return_var = null;
    exec($command, $output, $return_var);

    // Verificar si la restauración fue exitosa
    if ($return_var === 0) {
        // Devolver una respuesta JSON con éxito
        echo json_encode([
            'status' => 'success',
            'message' => "Copia de seguridad '$fileName' restaurada correctamente."
        ]);
    } else {
        // Devolver una respuesta JSON con error
        echo json_encode([
            'status' => 'error',
            'message' => "Error al restaurar la copia de seguridad '$fileName'."
        ]);
    }
} else {
    // Devolver una respuesta JSON si no se seleccionó archivo
    echo json_encode([
        'status' => 'error',
        'message' => 'No se seleccionó ningún archivo de respaldo.'
    ]);
}
exit;
