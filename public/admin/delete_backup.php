<?php
// Verificar si se pasó un archivo
if (isset($_GET['file'])) {
    $backupDir = realpath(__DIR__ . '/../../backups/backups/'); // Ajusta la ruta correctamente
    $fileName = basename($_GET['file']);
    $filePath = $backupDir . '/' . $fileName;

    // Verificar si el archivo existe
    if (file_exists($filePath)) {
        if (unlink($filePath)) {
            // El archivo fue eliminado exitosamente
            echo json_encode(['status' => 'success', 'message' => "El archivo de respaldo '$fileName' fue eliminado correctamente."]);
        } else {
            // Hubo un problema al eliminar el archivo
            echo json_encode(['status' => 'error', 'message' => "No se pudo eliminar el archivo '$fileName'."]);
        }
    } else {
        // El archivo no existe
        echo json_encode(['status' => 'error', 'message' => "El archivo de respaldo '$fileName' no existe."]);
    }
} else {
    // No se especificó ningún archivo
    echo json_encode(['status' => 'error', 'message' => "No se especificó ningún archivo para eliminar."]);
}
exit;
