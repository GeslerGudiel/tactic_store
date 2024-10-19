<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_emprendedor']) || $_SESSION['usuario_rol'] !== 'emprendedor') {
    header("Location: ../../auth/login.php");
    exit;
}

include_once '../../../src/config/database.php';

$emprendedor_id = $_SESSION['id_emprendedor'];

try {
    $database = new Database();
    $db = $database->getConnection();

    // Obtener datos del emprendedor
    $query_emprendedor = "SELECT * FROM emprendedor WHERE id_emprendedor = :emprendedor_id";
    $stmt_emprendedor = $db->prepare($query_emprendedor);
    $stmt_emprendedor->bindParam(':emprendedor_id', $emprendedor_id, PDO::PARAM_INT);
    $stmt_emprendedor->execute();
    $emprendedor = $stmt_emprendedor->fetch(PDO::FETCH_ASSOC);

    // Obtener datos del negocio
    $query_negocio = "SELECT * FROM negocio WHERE id_emprendedor = :emprendedor_id";
    $stmt_negocio = $db->prepare($query_negocio);
    $stmt_negocio->bindParam(':emprendedor_id', $emprendedor_id, PDO::PARAM_INT);
    $stmt_negocio->execute();
    $negocio = $stmt_negocio->fetch(PDO::FETCH_ASSOC);

    // Obtener las direcciones disponibles
    $query_direcciones = "SELECT * FROM direccion";
    $stmt_direcciones = $db->prepare($query_direcciones);
    $stmt_direcciones->execute();
    $direcciones = $stmt_direcciones->fetchAll(PDO::FETCH_ASSOC);

    // Obtener los nombres de bancos desde la tabla banco
    $query_bancos = "SELECT * FROM banco";
    $stmt_bancos = $db->prepare($query_bancos);
    $stmt_bancos->execute();
    $bancos = $stmt_bancos->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Devolvemos un mensaje de error si ocurre una excepción.
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Actualizar datos personales
    $nombre1 = htmlspecialchars(strip_tags($_POST['nombre1']));
    $nombre2 = htmlspecialchars(strip_tags($_POST['nombre2']));
    $nombre3 = htmlspecialchars(strip_tags($_POST['nombre3']));
    $apellido1 = htmlspecialchars(strip_tags($_POST['apellido1']));
    $apellido2 = htmlspecialchars(strip_tags($_POST['apellido2']));
    $telefono1 = htmlspecialchars(strip_tags($_POST['telefono1']));
    $telefono2 = htmlspecialchars(strip_tags($_POST['telefono2']));
    $banco = htmlspecialchars(strip_tags($_POST['banco']));
    $no_cuenta_bancaria = htmlspecialchars(strip_tags($_POST['no_cuenta_bancaria']));
    $tipo_cuenta_bancaria = htmlspecialchars(strip_tags($_POST['tipo_cuenta_bancaria']));
    $nombre_cuenta_bancaria = htmlspecialchars(strip_tags($_POST['nombre_cuenta_bancaria']));

    // Direcciones del emprendedor y negocio
    $id_direccion_emprendedor = isset($_POST['id_direccion_emprendedor']) ? htmlspecialchars(strip_tags($_POST['id_direccion_emprendedor'])) : null;
    $id_direccion_negocio = isset($_POST['id_direccion_negocio']) ? htmlspecialchars(strip_tags($_POST['id_direccion_negocio'])) : null;

    // Comprobar si se seleccionó agregar nueva dirección
    $agregar_direccion_emprendedor = isset($_POST['agregar_direccion_emprendedor']) && $_POST['agregar_direccion_emprendedor'] == 'on';
    $agregar_direccion_negocio = isset($_POST['agregar_direccion_negocio']) && $_POST['agregar_direccion_negocio'] == 'on';

    // Manejar la creación de una nueva dirección si es necesario
    if ($agregar_direccion_emprendedor) {
        $departamento_emprendedor = htmlspecialchars(strip_tags($_POST['departamento_emprendedor']));
        $municipio_emprendedor = htmlspecialchars(strip_tags($_POST['municipio_emprendedor']));
        $localidad_emprendedor = htmlspecialchars(strip_tags($_POST['localidad_emprendedor']));

        // Validar que los campos no estén vacíos
        if (!empty($departamento_emprendedor) && !empty($municipio_emprendedor) && !empty($localidad_emprendedor)) {
            // Insertar nueva dirección para el emprendedor
            $query_direccion_emprendedor = "INSERT INTO direccion (departamento, municipio, localidad) 
                                        VALUES (:departamento, :municipio, :localidad)";
            $stmt_direccion_emprendedor = $db->prepare($query_direccion_emprendedor);
            $stmt_direccion_emprendedor->bindParam(':departamento', $departamento_emprendedor);
            $stmt_direccion_emprendedor->bindParam(':municipio', $municipio_emprendedor);
            $stmt_direccion_emprendedor->bindParam(':localidad', $localidad_emprendedor);
            $stmt_direccion_emprendedor->execute();
            $id_direccion_emprendedor = $db->lastInsertId();
        } else {
            echo "Error: Todos los campos de dirección del emprendedor deben estar completos.";
        }
    }

    if ($agregar_direccion_negocio) {
        $departamento_negocio = htmlspecialchars(strip_tags($_POST['departamento_negocio']));
        $municipio_negocio = htmlspecialchars(strip_tags($_POST['municipio_negocio']));
        $localidad_negocio = htmlspecialchars(strip_tags($_POST['localidad_negocio']));

        // Validar que los campos no estén vacíos
        if (!empty($departamento_negocio) && !empty($municipio_negocio) && !empty($localidad_negocio)) {
            // Insertar nueva dirección para el negocio
            $query_direccion_negocio = "INSERT INTO direccion (departamento, municipio, localidad) 
                                    VALUES (:departamento, :municipio, :localidad)";
            $stmt_direccion_negocio = $db->prepare($query_direccion_negocio);
            $stmt_direccion_negocio->bindParam(':departamento', $departamento_negocio);
            $stmt_direccion_negocio->bindParam(':municipio', $municipio_negocio);
            $stmt_direccion_negocio->bindParam(':localidad', $localidad_negocio);
            $stmt_direccion_negocio->execute();
            $id_direccion_negocio = $db->lastInsertId();
        } else {
            echo "Error: Todos los campos de dirección del negocio deben estar completos.";
        }
    }


    // Manejo de documentos
    $documento_identificacion = $emprendedor['documento_identificacion'];
    if (isset($_FILES['documento_identificacion']) && $_FILES['documento_identificacion']['error'] === UPLOAD_ERR_OK) {
        $documento_identificacion = basename($_FILES["documento_identificacion"]["name"]);
        $target_file = "../../../uploads/dpi_docs/" . $documento_identificacion;

        if (move_uploaded_file($_FILES["documento_identificacion"]["tmp_name"], $target_file)) {
            // Eliminar el documento antiguo si se subió uno nuevo
            if ($emprendedor['documento_identificacion']) {
                unlink("../../../uploads/dpi_docs/" . $emprendedor['documento_identificacion']);
            }
        } else {
            echo "Hubo un error al subir el nuevo documento de identificación.";
        }
    }

    // Actualizar datos del negocio
    $nombre_negocio = htmlspecialchars(strip_tags($_POST['nombre_negocio']));
    $referencia_direccion = htmlspecialchars(strip_tags($_POST['referencia_direccion']));
    $patente_comercio = $negocio['patente_comercio'];
    if (isset($_FILES['patente_comercio']) && $_FILES['patente_comercio']['error'] === UPLOAD_ERR_OK) {
        // Obtener la extensión del archivo original
        $fileExtension = pathinfo($_FILES['patente_comercio']['name'], PATHINFO_EXTENSION);

        // Crear un nuevo nombre para el archivo con un timestamp y el nombre del negocio
        $newFileName = $nombre_negocio . '_patente_' . time() . '.' . $fileExtension;

        // Definir la ruta donde se guardará el archivo
        $target_file = "../../../uploads/patente_docs/" . $newFileName;

        // Mover el archivo a la carpeta de destino
        if (move_uploaded_file($_FILES["patente_comercio"]["tmp_name"], $target_file)) {
            // Eliminar el archivo de la patente antigua si existe
            if (!empty($negocio['patente_comercio']) && file_exists("../../uploads/patente_docs/" . $negocio['patente_comercio'])) {
                unlink("../../../uploads/patente_docs/" . $negocio['patente_comercio']);
            }
            // Actualizar el nombre del archivo de patente en la base de datos
            $patente_comercio = $newFileName;
        } else {
            echo "Hubo un error al subir la nueva patente de comercio.";
        }
    }

    try {
        // Actualizar la base de datos
        $query_emprendedor_update = "UPDATE emprendedor SET nombre1 = :nombre1, nombre2 = :nombre2, nombre3 = :nombre3, 
                                    apellido1 = :apellido1, apellido2 = :apellido2, telefono1 = :telefono1, telefono2 = :telefono2, 
                                    id_banco = :banco, no_cuenta_bancaria = :no_cuenta_bancaria, tipo_cuenta_bancaria = :tipo_cuenta_bancaria, 
                                    nombre_cuenta_bancaria = :nombre_cuenta_bancaria, documento_identificacion = :documento_identificacion, 
                                    id_direccion = :id_direccion_emprendedor
                                    WHERE id_emprendedor = :emprendedor_id";
        $stmt = $db->prepare($query_emprendedor_update);
        $stmt->bindParam(':nombre1', $nombre1);
        $stmt->bindParam(':nombre2', $nombre2);
        $stmt->bindParam(':nombre3', $nombre3);
        $stmt->bindParam(':apellido1', $apellido1);
        $stmt->bindParam(':apellido2', $apellido2);
        $stmt->bindParam(':telefono1', $telefono1);
        $stmt->bindParam(':telefono2', $telefono2);
        $stmt->bindParam(':banco', $banco);
        $stmt->bindParam(':no_cuenta_bancaria', $no_cuenta_bancaria);
        $stmt->bindParam(':tipo_cuenta_bancaria', $tipo_cuenta_bancaria);
        $stmt->bindParam(':nombre_cuenta_bancaria', $nombre_cuenta_bancaria);
        $stmt->bindParam(':documento_identificacion', $documento_identificacion);
        $stmt->bindParam(':id_direccion_emprendedor', $id_direccion_emprendedor);
        $stmt->bindParam(':emprendedor_id', $emprendedor_id, PDO::PARAM_INT);
        $stmt->execute();

        // Actualizar la base de datos para el negocio
        $query_negocio_update = "UPDATE negocio SET nombre_negocio = :nombre_negocio, referencia_direccion = :referencia_direccion, 
                                patente_comercio = :patente_comercio, id_direccion = :id_direccion_negocio
                                WHERE id_emprendedor = :emprendedor_id";
        $stmt = $db->prepare($query_negocio_update);
        $stmt->bindParam(':nombre_negocio', $nombre_negocio);
        $stmt->bindParam(':referencia_direccion', $referencia_direccion);
        $stmt->bindParam(':patente_comercio', $patente_comercio);
        $stmt->bindParam(':id_direccion_negocio', $id_direccion_negocio);
        $stmt->bindParam(':emprendedor_id', $emprendedor_id, PDO::PARAM_INT);
        $stmt->execute();

        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Perfil actualizado exitosamente.'
        ];
        // Redirigir dentro del dashboard con AJAX.
        echo json_encode(['success' => true]);
        exit;
    } catch (PDOException $e) {
        // Devolvemos un mensaje de error si ocurre una excepción.
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .container {
            max-width: 800px;
        }

        h4 {
            border-bottom: 2px solid #ddd;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .form-label i {
            margin-right: 5px;
            color: #17a2b8;
        }

        .btn i {
            margin-right: 5px;
        }

        .checkbox-label {
            font-weight: bold;
            color: #007bff;
            cursor: pointer;
        }

        #nueva_direccion_form_emprendedor,
        #nueva_direccion_form_negocio {
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
    </style>
</head>

<body>
    <div class="container my-5">
        <h2 class="mb-4 text-center">Editar Perfil</h2>
        <form id="editar-perfil-form" method="POST" enctype="multipart/form-data">
            <h4>Datos Personales</h4>
            <div class="mb-3">
                <label for="nombre1" class="form-label"><i class="fas fa-user"></i> Primer Nombre:</label>
                <input type="text" id="nombre1" name="nombre1" class="form-control" value="<?php echo htmlspecialchars($emprendedor['nombre1']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="nombre2" class="form-label"><i class="fas fa-user"></i> Segundo Nombre:</label>
                <input type="text" id="nombre2" name="nombre2" class="form-control" value="<?php echo htmlspecialchars($emprendedor['nombre2']); ?>">
            </div>
            <div class="mb-3">
                <label for="nombre3" class="form-label"><i class="fas fa-user"></i> Tercer Nombre:</label>
                <input type="text" id="nombre3" name="nombre3" class="form-control" value="<?php echo htmlspecialchars($emprendedor['nombre3']); ?>">
            </div>
            <div class="mb-3">
                <label for="apellido1" class="form-label"><i class="fas fa-user-tag"></i> Primer Apellido:</label>
                <input type="text" id="apellido1" name="apellido1" class="form-control" value="<?php echo htmlspecialchars($emprendedor['apellido1']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="apellido2" class="form-label"><i class="fas fa-user-tag"></i> Segundo Apellido:</label>
                <input type="text" id="apellido2" name="apellido2" class="form-control" value="<?php echo htmlspecialchars($emprendedor['apellido2']); ?>">
            </div>
            <div class="mb-3">
                <label for="id_direccion_emprendedor" class="form-label"><i class="fas fa-map-marker-alt"></i> Dirección del emprendedor:</label>
                <select id="id_direccion_emprendedor" name="id_direccion_emprendedor" class="form-select" required>
                    <?php foreach ($direcciones as $direccion): ?>
                        <option value="<?php echo $direccion['id_direccion']; ?>" <?php if ($emprendedor['id_direccion'] == $direccion['id_direccion']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($direccion['localidad'] . ', ' . $direccion['municipio'] . ', ' . $direccion['departamento']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Opción para Agregar Nueva Dirección -->
            <div class="mb-4">
                <label for="agregar_direccion_emprendedor" class="checkbox-label"><i class="fas fa-plus-circle"></i> ¿Cambiaste de dirección? Si no lo encuentras, agrega una nueva:</label>
                <input type="checkbox" id="agregar_direccion_emprendedor" name="agregar_direccion_emprendedor">
            </div>

            <div id="nueva_direccion_form_emprendedor" style="display: none;">
                <div class="mb-3">
                    <label for="departamento" class="form-label"><i class="fas fa-map"></i> Departamento:</label>
                    <input type="text" id="departamento" name="departamento_emprendedor" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="municipio" class="form-label"><i class="fas fa-city"></i> Municipio:</label>
                    <input type="text" id="municipio" name="municipio_emprendedor" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="localidad" class="form-label"><i class="fas fa-map-signs"></i> Localidad (Aldea, Barrio, Caserío, Lotificación, Otros):</label>
                    <input type="text" id="localidad" name="localidad_emprendedor" class="form-control">
                </div>
            </div>
            <div class="mb-3">
                <label for="telefono1" class="form-label"><i class="fas fa-phone"></i> Teléfono 1:</label>
                <input type="text" id="telefono1" name="telefono1" class="form-control" value="<?php echo htmlspecialchars($emprendedor['telefono1']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="telefono2" class="form-label"><i class="fas fa-phone"></i> Teléfono 2:</label>
                <input type="text" id="telefono2" name="telefono2" class="form-control" value="<?php echo htmlspecialchars($emprendedor['telefono2']); ?>">
            </div>
            <div class="mb-3">
                <label for="banco" class="form-label"><i class="fas fa-university"></i> Banco:</label>
                <select id="banco" name="banco" class="form-select" required>
                    <?php foreach ($bancos as $banco): ?>
                        <option value="<?php echo $banco['id_banco']; ?>" <?php if ($emprendedor['id_banco'] == $banco['id_banco']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($banco['nombre_banco']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="no_cuenta_bancaria" class="form-label"><i class="fas fa-money-check"></i> Número de Cuenta Bancaria:</label>
                <input type="text" id="no_cuenta_bancaria" name="no_cuenta_bancaria" class="form-control" value="<?php echo htmlspecialchars($emprendedor['no_cuenta_bancaria']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="tipo_cuenta_bancaria" class="form-label"><i class="fas fa-wallet"></i> Tipo de Cuenta Bancaria:</label>
                <select id="tipo_cuenta_bancaria" name="tipo_cuenta_bancaria" class="form-select" required>
                    <option value="Ahorro" <?php if ($emprendedor['tipo_cuenta_bancaria'] == 'Ahorro') echo 'selected'; ?>>Ahorro</option>
                    <option value="Monetaria" <?php if ($emprendedor['tipo_cuenta_bancaria'] == 'Monetaria') echo 'selected'; ?>>Monetaria</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="nombre_cuenta_bancaria" class="form-label"><i class="fas fa-id-card"></i> Nombre en la Cuenta Bancaria:</label>
                <input type="text" id="nombre_cuenta_bancaria" name="nombre_cuenta_bancaria" class="form-control" value="<?php echo htmlspecialchars($emprendedor['nombre_cuenta_bancaria']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="documento_identificacion" class="form-label"><i class="fas fa-file-alt"></i> Documento de Identificación:</label>
                <input type="file" id="documento_identificacion" name="documento_identificacion" class="form-control" accept="application/pdf">
                <?php if ($emprendedor['documento_identificacion']): ?>
                    <a href="/comercio_electronico/uploads/dpi_docs/<?php echo htmlspecialchars($emprendedor['documento_identificacion']); ?>" target="_blank">Ver Documento</a>
                <?php endif; ?>
            </div>

            <h4>Datos del Negocio</h4>
            <div class="mb-3">
                <label for="nombre_negocio" class="form-label"><i class="fas fa-store"></i> Nombre del Negocio:</label>
                <input type="text" id="nombre_negocio" name="nombre_negocio" class="form-control" value="<?php echo htmlspecialchars($negocio['nombre_negocio']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="referencia_direccion" class="form-label"><i class="fas fa-map-signs"></i> Referencia de la Dirección:</label>
                <textarea id="referencia_direccion" name="referencia_direccion" class="form-control" required><?php echo htmlspecialchars($negocio['referencia_direccion']); ?></textarea>
            </div>
            <div class="mb-3">
                <label for="patente_comercio" class="form-label"><i class="fas fa-file-alt"></i> Patente de Comercio:</label>
                <input type="file" id="patente_comercio" name="patente_comercio" class="form-control" accept="application/pdf">
                <?php if ($negocio['patente_comercio']): ?>
                    <a href="/comercio_electronico/uploads/patente_docs/<?php echo htmlspecialchars($negocio['patente_comercio']); ?>" target="_blank">Ver Documento</a>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="id_direccion_negocio" class="form-label"><i class="fas fa-map-marker-alt"></i> Dirección del Negocio:</label>
                <select id="id_direccion_negocio" name="id_direccion_negocio" class="form-select" required>
                    <?php foreach ($direcciones as $direccion): ?>
                        <option value="<?php echo $direccion['id_direccion']; ?>" <?php if ($negocio['id_direccion'] == $direccion['id_direccion']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($direccion['localidad'] . ', ' . $direccion['municipio'] . ', ' . $direccion['departamento']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Opción para Agregar Nueva Dirección -->
            <div class="mb-4">
                <label for="agregar_direccion_negocio" class="checkbox-label"><i class="fas fa-plus-circle"></i> ¿Cambiaste de dirección? Si no lo encuentras, agrega una nueva:</label>
                <input type="checkbox" id="agregar_direccion_negocio" name="agregar_direccion_negocio">
            </div>

            <div id="nueva_direccion_form_negocio" style="display: none;">
                <div class="mb-3">
                    <label for="departamento" class="form-label"><i class="fas fa-map"></i> Departamento:</label>
                    <input type="text" id="departamento" name="departamento_negocio" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="municipio" class="form-label"><i class="fas fa-city"></i> Municipio:</label>
                    <input type="text" id="municipio" name="municipio_negocio" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="localidad" class="form-label"><i class="fas fa-map-signs"></i> Localidad (Aldea, Barrio, Caserío, Lotificación, Otros):</label>
                    <input type="text" id="localidad" name="localidad_negocio" class="form-control">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i> Guardar Cambios
            </button>

            <a href="#" id="cancelar-edicion" class="btn btn-secondary">
                <i class="fas fa-times"></i> Cancelar
            </a>

        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>

    <script>
    $(document).ready(function () {
        // Mostrar el formulario de nueva dirección si se marca el checkbox
        const agregarDireccionEmprendedorCheckbox = document.getElementById('agregar_direccion_emprendedor');
        const nuevaDireccionFormEmprendedor = document.getElementById('nueva_direccion_form_emprendedor');

        agregarDireccionEmprendedorCheckbox.addEventListener('change', function () {
            nuevaDireccionFormEmprendedor.style.display = this.checked ? 'block' : 'none';
        });

        const agregarDireccionNegocioCheckbox = document.getElementById('agregar_direccion_negocio');
        const nuevaDireccionFormNegocio = document.getElementById('nueva_direccion_form_negocio');

        agregarDireccionNegocioCheckbox.addEventListener('change', function () {
            nuevaDireccionFormNegocio.style.display = this.checked ? 'block' : 'none';
        });

        // Redirigir al perfil sin recargar el dashboard al hacer clic en "Cancelar"
        $(document).on('click', '#cancelar-edicion', function (e) {
            e.preventDefault();
            $('#content-area').load('/comercio_electronico/public/emprendedor/perfil/ver_perfil.php');
        });

        // Manejar el envío del formulario con AJAX
        $(document).on('submit', '#editar-perfil-form', function (e) {
            e.preventDefault(); // Evita el envío normal del formulario

            let formData = new FormData(this);

            $.ajax({
                url: '/comercio_electronico/public/emprendedor/perfil/editar_perfil.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function (response) {
                    console.log('Respuesta del servidor:', response);

                    try {
                        let result = JSON.parse(response); // Convertir la respuesta en JSON

                        if (result.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: 'Perfil actualizado exitosamente.',
                                confirmButtonText: 'Aceptar'
                            }).then(() => {
                                $('#content-area').load('/comercio_electronico/public/emprendedor/perfil/ver_perfil.php');
                            });
                        } else {
                            Swal.fire('Error', result.message || 'Hubo un problema al guardar los cambios.', 'error');
                        }
                    } catch (error) {
                        console.error('Error al parsear JSON:', error);
                        Swal.fire('Error', 'Respuesta inesperada del servidor.', 'error');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('Error:', status, error);
                    Swal.fire('Error', 'No se pudo completar la solicitud.', 'error');
                }
            });
        });
    });
</script>


</body>

</html>