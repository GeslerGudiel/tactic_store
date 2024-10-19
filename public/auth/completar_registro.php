<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar Registro del Negocio</title>

    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center">Completar Registro del Negocio</h2>

        <?php
        session_start();
        include_once '../../src/config/config.php';
        include_once '../../src/config/database.php';

        // Conexión a la base de datos
        $database = new Database();
        $db = $database->getConnection();

        // Consultar las direcciones existentes
        $query_direcciones = "SELECT id_direccion, departamento, municipio, localidad FROM direccion";
        $stmt_direcciones = $db->prepare($query_direcciones);
        $stmt_direcciones->execute();
        $direcciones = $stmt_direcciones->fetchAll(PDO::FETCH_ASSOC);

        if (isset($_SESSION['message'])) {
            echo '<script>
                Swal.fire({
                    icon: "' . $_SESSION['message']['type'] . '",
                    title: "' . ucfirst($_SESSION['message']['type']) . '",
                    text: "' . $_SESSION['message']['text'] . '"
                });
                </script>';
            unset($_SESSION['message']);  // Limpiar el mensaje después de mostrarlo
        }
        ?>

        <form action="complete_registration_process.php" method="POST" enctype="multipart/form-data" class="mx-auto" style="max-width: 600px;">

            <!-- Sección: Información del Negocio -->
            <div class="mb-4">
                <h4>Información del Negocio</h4>
                <div class="mb-3">
                    <label for="nombre_negocio" class="form-label"><i class="fas fa-store"></i> Nombre del Negocio:</label>
                    <input type="text" id="nombre_negocio" name="nombre_negocio" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="patente_comercio" class="form-label"><i class="fas fa-file-pdf"></i> Documento de Patente de Comercio:</label>
                    <input type="file" id="patente_comercio" name="patente_comercio" class="form-control" accept=".pdf" required>
                </div>
                <!-- Selección de Dirección Existente -->
                <div class="mb-4">
                    <label for="direccion_existente" class="form-label"><i class="fas fa-map-marker-alt"></i> Seleccionar Dirección:</label>
                    <select id="direccion_existente" name="direccion_existente" class="form-select">
                        <option value="">-- Selecciona una dirección --</option>
                        <?php foreach ($direcciones as $direccion): ?>
                            <option value="<?= $direccion['id_direccion'] ?>">
                                <?= $direccion['departamento'] . ', ' . $direccion['municipio'] . ' - ' . $direccion['localidad'] ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Opción para Agregar Nueva Dirección -->
                <div class="mb-4">
                    <label for="nueva_direccion" class="form-label"><i class="fas fa-plus-circle"></i> ¿No encuentras tu dirección? Agrega una nueva:</label>
                    <input type="checkbox" id="agregar_direccion" name="agregar_direccion">
                </div>

                <div id="nueva_direccion_form" style="display: none;">
                    <div class="mb-3">
                        <label for="departamento" class="form-label"><i class="fas fa-map"></i> Departamento:</label>
                        <input type="text" id="departamento" name="departamento" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="municipio" class="form-label"><i class="fas fa-city"></i> Municipio:</label>
                        <input type="text" id="municipio" name="municipio" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label for="localidad" class="form-label"><i class="fas fa-map-signs"></i> Localidad (Aldea, Barrio, Caserío, Lotificación, Otros):</label>
                        <input type="text" id="localidad" name="localidad" class="form-control">
                    </div>
                </div>
                <div class="mb-3">
                    <label for="tienda_fisica" class="form-label"><i class="fas fa-store-alt"></i> ¿Tienes Tienda Física?</label>
                    <select id="tienda_fisica" name="tienda_fisica" class="form-select" required>
                        <option value="1">Sí</option>
                        <option value="0">No</option>
                    </select>
                </div>
            </div>

            <!-- Sección: Información Bancaria -->
            <div class="mb-4">
                <h4>Información Bancaria</h4>
                <div class="mb-3">
                    <label for="banco" class="form-label"><i class="fas fa-university"></i> Banco:</label>
                    <select id="banco" name="banco" class="form-select" required>
                        <option value="">Selecciona un banco</option>
                        <?php
                        // Consulta para obtener los bancos
                        $query_bancos = "SELECT id_banco, nombre_banco FROM banco";
                        $stmt_bancos = $db->prepare($query_bancos);
                        $stmt_bancos->execute();
                        $bancos = $stmt_bancos->fetchAll(PDO::FETCH_ASSOC);

                        // Generar las opciones del select
                        foreach ($bancos as $banco) {
                            echo '<option value="' . htmlspecialchars($banco['id_banco']) . '">' . htmlspecialchars($banco['nombre_banco']) . '</option>';
                        }
                        ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="no_cuenta_bancaria" class="form-label"><i class="fas fa-credit-card"></i> Número de Cuenta Bancaria:</label>
                    <input type="text" id="no_cuenta_bancaria" name="no_cuenta_bancaria" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="tipo_cuenta_bancaria" class="form-label"><i class="fas fa-wallet"></i> Tipo de Cuenta:</label>
                    <select id="tipo_cuenta_bancaria" name="tipo_cuenta_bancaria" class="form-select" required>
                        <option value="Ahorro">Ahorro</option>
                        <option value="Motenaria">Monetaria</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="nombre_cuenta_bancaria" class="form-label"><i class="fas fa-user"></i> Nombre en la Cuenta Bancaria:</label>
                    <input type="text" id="nombre_cuenta_bancaria" name="nombre_cuenta_bancaria" class="form-control" required>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-save"></i> Completar Registro</button>
        </form>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mostrar el formulario de nueva dirección si se marca el checkbox
        const agregarDireccionCheckbox = document.getElementById('agregar_direccion');
        const nuevaDireccionForm = document.getElementById('nueva_direccion_form');

        agregarDireccionCheckbox.addEventListener('change', function() {
            if (this.checked) {
                nuevaDireccionForm.style.display = 'block';
            } else {
                nuevaDireccionForm.style.display = 'none';
            }
        });
    </script>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>