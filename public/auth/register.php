<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Emprendedor</title>


    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>

<body>
    <div class="container mt-5">
        <h2 class="text-center">Registro de Emprendedor</h2>

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

        <form action="register_process.php" method="POST" enctype="multipart/form-data" class="mx-auto" style="max-width: 600px;">
            <!-- Información Básica -->
            <div class="mb-4">
                <h4>Información personal</h4>
                <div class="mb-3">
                    <label for="nombre1" class="form-label"><i class="fas fa-user"></i> Primer Nombre:</label>
                    <input type="text" id="nombre1" name="nombre1" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="nombre2" class="form-label"><i class="fas fa-user"></i> Segundo Nombre:</label>
                    <input type="text" id="nombre2" name="nombre2" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="nombre3" class="form-label"><i class="fas fa-user"></i> Tercer Nombre (Opcional):</label>
                    <input type="text" id="nombre3" name="nombre3" class="form-control">
                </div>
                <div class="mb-3">
                    <label for="apellido1" class="form-label"><i class="fas fa-user"></i> Primer Apellido:</label>
                    <input type="text" id="apellido1" name="apellido1" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="apellido2" class="form-label"><i class="fas fa-user"></i> Segundo Apellido:</label>
                    <input type="text" id="apellido2" name="apellido2" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="dpi" class="form-label"><i class="fas fa-id-card"></i> CUI DPI (Documento de Identificación Personal):</label>
                    <input type="text" id="dpi" name="dpi" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label for="documento_identificacion" class="form-label"><i class="fas fa-file-pdf"></i> Subir Documento de Identificación (ambos lados en formato PDF):</label>
                    <input type="file" id="documento_identificacion" name="documento_identificacion" class="form-control" accept=".pdf" required>
                </div>

                <!-- Selección de Dirección Existente -->
                <div class="mb-4">
                    <label for="direccion_existente" class="form-label"><i class="fas fa-map-marker-alt"></i> Buscar y Seleccionar Dirección:</label>
                    <select id="direccion_existente" name="direccion_existente" class="form-select">
                        <option value="">-- Selecciona una dirección --</option>
                        <?php foreach ($direcciones as $direccion): ?>
                            <option value="<?= $direccion['id_direccion'] ?>">
                                <?= $direccion['localidad'] . ', ' . $direccion['municipio'] . ', ' . $direccion['departamento'] ?>
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

                <div class="mb-4">
                    <h4>Contacto personal</h4>
                    <div class="mb-3">
                        <label for="correo" class="form-label"><i class="fas fa-envelope"></i> Correo Electrónico:</label>
                        <input type="email" id="correo" name="correo" class="form-control" required>
                    </div>
                    <div class="mb-3 position-relative">
                        <label for="contrasena" class="form-label"><i class="fas fa-lock"></i> Contraseña:</label>
                        <input type="password" id="contrasena" name="contrasena" class="form-control" required>
                        <i class="fas fa-eye position-absolute" id="togglePassword" style="top: 50%; right: 10px; cursor: pointer;"></i>
                    </div>
                    <div class="mb-3 position-relative">
                        <label for="confirmar_contrasena" class="form-label"><i class="fas fa-lock"></i> Confirmar Contraseña:</label>
                        <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" class="form-control" required>
                        <i class="fas fa-eye position-absolute" id="toggleConfirmPassword" style="top: 50%; right: 10px; cursor: pointer;"></i>
                    </div>
                    <div class="mb-3">
                        <label for="telefono1" class="form-label"><i class="fas fa-phone"></i> Teléfono 1:</label>
                        <input type="text" id="telefono1" name="telefono1" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label for="telefono2" class="form-label"><i class="fas fa-phone"></i> Teléfono 2:</label>
                        <input type="text" id="telefono2" name="telefono2" class="form-control">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-user-plus"></i> Registrarse</button>
        </form>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>

    <script>
        // Funcionalidad para mostrar/ocultar la contraseña
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#contrasena');

        togglePassword.addEventListener('click', function(e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
        const confirmPassword = document.querySelector('#confirmar_contrasena');

        toggleConfirmPassword.addEventListener('click', function(e) {
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>

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

    <script>
        $(document).ready(function() {
            $('#direccion_existente').select2({
                placeholder: "-- Selecciona una dirección --",
                allowClear: true,
                width: '100%'
            });
        });
    </script>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>