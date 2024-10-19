<?php
session_start();
include_once '../../src/config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_comisiones = explode(',', $_POST['id_comisiones']);
    $comprobante_pago = $_FILES['comprobante_pago'];

    // Validar si se subió un archivo
    if ($comprobante_pago['error'] === UPLOAD_ERR_OK) {
        $nombre_archivo = 'comprobante_' . time() . '.' . pathinfo($comprobante_pago['name'], PATHINFO_EXTENSION);
        $ruta_destino = '../../uploads/comprobantes_comision/' . $nombre_archivo;

        if (move_uploaded_file($comprobante_pago['tmp_name'], $ruta_destino)) {
            $database = new Database();
            $db = $database->getConnection();

            // Actualizar todas las comisiones
            $query = "UPDATE comision SET estado_comision = 'Pagada', comprobante_pago = ?, fecha_pago = NOW() 
                      WHERE id_comision IN (" . implode(',', array_fill(0, count($id_comisiones), '?')) . ")";
            $stmt = $db->prepare($query);

            // Bind el nombre del comprobante
            $stmt->bindValue(1, $nombre_archivo);

            // Bind los IDs de las comisiones
            foreach ($id_comisiones as $index => $id_comision) {
                $stmt->bindValue($index + 2, $id_comision);  // +2 porque el primer parámetro es para el archivo
            }

            if ($stmt->execute()) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Comisiones actualizadas correctamente.'
                ]);
            } else {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Error al actualizar las comisiones.'
                ]);
            }
        } else {
            echo json_encode([
                'status' => 'error',
                'message' => 'Error al mover el archivo.'
            ]);
        }
    } else {
        echo json_encode([
            'status' => 'error',
            'message' => 'Error al subir el comprobante de pago.'
        ]);
    }
}
?>
