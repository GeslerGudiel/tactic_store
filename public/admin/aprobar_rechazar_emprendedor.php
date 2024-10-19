<?php
session_start();

if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    echo json_encode(['status' => 'error', 'message' => 'Acceso no autorizado']);
    exit;
}

include_once '../../src/config/database.php';
include_once '../../src/config/funciones.php'; // Incluir la función de notificaciones

$database = new Database(); 
$db = $database->getConnection();

$id_emprendedor = isset($_POST['id_emprendedor']) ? $_POST['id_emprendedor'] : die(json_encode(['status' => 'error', 'message' => 'ID de emprendedor no especificado.']));

try {
    $db->beginTransaction(); // Iniciar la transacción

    $todos_datos_aprobados = true; // Variable para verificar si todo fue aprobado
    $notificaciones = []; // Arreglo para almacenar los mensajes de notificación

    // Aprobación/Rechazo de datos personales
    $fields_to_update = [];

    if (isset($_POST['aprobar_nombre']) && $_POST['aprobar_nombre'] == 1) {
        $fields_to_update[] = "nombre1 = nombre1, nombre2 = nombre2, nombre3 = nombre3";
    } else {
        $fields_to_update[] = "nombre1 = NULL, nombre2 = NULL, nombre3 = NULL";
        $todos_datos_aprobados = false;
    }

    if (isset($_POST['aprobar_apellidos']) && $_POST['aprobar_apellidos'] == 1) {
        $fields_to_update[] = "apellido1 = apellido1, apellido2 = apellido2";
    } else {
        $fields_to_update[] = "apellido1 = NULL, apellido2 = NULL";
        $todos_datos_aprobados = false;
    }

    if (isset($_POST['aprobar_telefono1']) && $_POST['aprobar_telefono1'] == 1) {
        $fields_to_update[] = "telefono1 = telefono1";
    } else {
        $fields_to_update[] = "telefono1 = NULL";
        $todos_datos_aprobados = false;
    }

    if (isset($_POST['aprobar_telefono2']) && $_POST['aprobar_telefono2'] == 1) {
        $fields_to_update[] = "telefono2 = telefono2";
    } else {
        $fields_to_update[] = "telefono2 = NULL";
        $todos_datos_aprobados = false;
    }

    if (isset($_POST['aprobar_dpi']) && $_POST['aprobar_dpi'] == 1) {
        $fields_to_update[] = "dpi = dpi";
    } else {
        $fields_to_update[] = "dpi = NULL";
        $todos_datos_aprobados = false;
    }

    if (isset($_POST['aprobar_documento']) && $_POST['aprobar_documento'] == 1) {
        $fields_to_update[] = "documento_identificacion = documento_identificacion";
    } else {
        $fields_to_update[] = "documento_identificacion = NULL";
        $todos_datos_aprobados = false;
    }

    if (isset($_POST['aprobar_direccion_emprendedor']) && $_POST['aprobar_direccion_emprendedor'] == 1) {
        $fields_to_update[] = "id_direccion = id_direccion";
    } else {
        $fields_to_update[] = "id_direccion = NULL";
        $todos_datos_aprobados = false;
    }

    if (isset($_POST['aprobar_banco']) && $_POST['aprobar_banco'] == 1) {
        $fields_to_update[] = "id_banco = id_banco";
    } else {
        $fields_to_update[] = "id_banco = NULL";
        $todos_datos_aprobados = false;
    }

    if (isset($_POST['aprobar_cuenta_bancaria']) && $_POST['aprobar_cuenta_bancaria'] == 1) {
        $fields_to_update[] = "no_cuenta_bancaria = no_cuenta_bancaria";
    } else {
        $fields_to_update[] = "no_cuenta_bancaria = NULL";
        $todos_datos_aprobados = false;
    }

    if (isset($_POST['aprobar_tipo_cuenta']) && $_POST['aprobar_tipo_cuenta'] == 1) {
        $fields_to_update[] = "tipo_cuenta_bancaria = tipo_cuenta_bancaria";
    } else {
        $fields_to_update[] = "tipo_cuenta_bancaria = NULL";
        $todos_datos_aprobados = false;
    }

    if (isset($_POST['aprobar_nombre_cuenta']) && $_POST['aprobar_nombre_cuenta'] == 1) {
        $fields_to_update[] = "nombre_cuenta_bancaria = nombre_cuenta_bancaria";
    } else {
        $fields_to_update[] = "nombre_cuenta_bancaria = NULL";
        $todos_datos_aprobados = false;
    }

    // Actualizar datos del emprendedor
    if (!empty($fields_to_update)) {
        $query_emprendedor = "UPDATE emprendedor SET " . implode(", ", $fields_to_update) . " WHERE id_emprendedor = :id_emprendedor";
        $stmt_emprendedor = $db->prepare($query_emprendedor);
        $stmt_emprendedor->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
        $stmt_emprendedor->execute();
    }

    // Aprobación/Rechazo de datos del negocio
    $fields_to_update_negocio = [];

    if (isset($_POST['aprobar_nombre_negocio']) && $_POST['aprobar_nombre_negocio'] == 1) {
        $fields_to_update_negocio[] = "nombre_negocio = nombre_negocio";
    } else {
        $fields_to_update_negocio[] = "nombre_negocio = NULL";
        $todos_datos_aprobados = false;
    }

    if (isset($_POST['aprobar_direccion_negocio']) && $_POST['aprobar_direccion_negocio'] == 1) {
        $fields_to_update_negocio[] = "id_direccion = id_direccion";
    } else {
        $fields_to_update_negocio[] = "id_direccion = NULL";
        $todos_datos_aprobados = false;
    }

    if (isset($_POST['aprobar_referencia']) && $_POST['aprobar_referencia'] == 1) {
        $fields_to_update_negocio[] = "referencia_direccion = referencia_direccion";
    } else {
        $fields_to_update_negocio[] = "referencia_direccion = NULL";
        $todos_datos_aprobados = false;
    }

    if (isset($_POST['aprobar_patente']) && $_POST['aprobar_patente'] == 1) {
        $fields_to_update_negocio[] = "patente_comercio = patente_comercio";
    } else {
        $fields_to_update_negocio[] = "patente_comercio = NULL";
        $todos_datos_aprobados = false;
    }

    if (isset($_POST['aprobar_tienda_fisica']) && $_POST['aprobar_tienda_fisica'] == 1) {
        $fields_to_update_negocio[] = "tienda_fisica = tienda_fisica";
    } else {
        $fields_to_update_negocio[] = "tienda_fisica = NULL";
        $todos_datos_aprobados = false;
    }

    // Actualizar datos del negocio
    if (!empty($fields_to_update_negocio)) {
        $query_negocio = "UPDATE negocio SET " . implode(", ", $fields_to_update_negocio) . " WHERE id_emprendedor = :id_emprendedor";
        $stmt_negocio = $db->prepare($query_negocio);
        $stmt_negocio->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
        $stmt_negocio->execute();
    }

    // Cambiar el estado del emprendedor a "Activado" solo si todos los datos fueron aprobados
    if ($todos_datos_aprobados) {
        $query_estado = "UPDATE emprendedor SET id_estado_usuario = 2 WHERE id_emprendedor = :id_emprendedor";
        $stmt_estado = $db->prepare($query_estado);
        $stmt_estado->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
        $stmt_estado->execute();

        $notificaciones[] = "Tus datos han sido aprobados por el administrador, ya puedes gestionar tu tienda.";

        // Enviar respuesta exitosa en JSON
        echo json_encode(['status' => 'success', 'message' => 'Todos los datos fueron aprobados. El emprendedor ha sido activado.']);
    } else {
        // Si algún dato fue rechazado, enviar una notificación y mantener el estado como "Pendiente de correcciones"
        $query_estado = "UPDATE emprendedor SET id_estado_usuario = 3 WHERE id_emprendedor = :id_emprendedor";
        $stmt_estado = $db->prepare($query_estado);
        $stmt_estado->bindParam(':id_emprendedor', $id_emprendedor, PDO::PARAM_INT);
        $stmt_estado->execute();

        $notificaciones[] = "Algunos datos de tu perfil fueron rechazados. Por favor, revisa que todos los campos sean con tus datos reales.";

        // Enviar respuesta de advertencia en JSON
        echo json_encode(['status' => 'warning', 'message' => 'Algunos datos fueron rechazados. El emprendedor debe corregirlos.']);
    }

    // Notificar al emprendedor si hubo rechazos
    foreach ($notificaciones as $mensaje) {
        agregarNotificacion($db, null, $id_emprendedor, "Corrección de Datos", $mensaje);
    }

    $db->commit(); // Confirmar la transacción
} catch (PDOException $e) {
    $db->rollBack(); // Revertir la transacción en caso de error
    echo json_encode(['status' => 'error', 'message' => 'Error al actualizar los datos: ' . $e->getMessage()]);
    exit;
}
