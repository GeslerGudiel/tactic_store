<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .container {
            max-width: 600px;
            margin-top: 50px;
        }

        h2 {
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: bold;
        }

        .form-label i {
            margin-right: 5px;
            color: #007bff;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #0056b3;
        }

        .input-group-text {
            cursor: pointer;
        }

        .alert-floating {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            width: auto;
            padding: 15px;
            border-radius: 5px;
            opacity: 0.9;
        }

        #nueva_direccion_form {
            display: none;
            border: 1px solid #ddd;
            padding: 15px;
            margin-top: 15px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
    </style>
</head>

<body>
    <div class="container">
        <h2 class="text-center"><i class="fas fa-user-plus"></i> Registro de Cliente</h2>
        <?php
        session_start();
        if (isset($_SESSION['message'])) {
            echo '<script>
                Swal.fire({
                    icon: "' . $_SESSION['message']['type'] . '",
                    text: "' . $_SESSION['message']['text'] . '",
                    showConfirmButton: false,
                    timer: 2000
                });
            </script>';
            unset($_SESSION['message']);
        }

        include_once '../../src/config/database.php';
        try {
            $database = new Database();
            $db = $database->getConnection();
            $query_direcciones = "SELECT * FROM direccion";
            $stmt_direcciones = $db->prepare($query_direcciones);
            $stmt_direcciones->execute();
            $direcciones = $stmt_direcciones->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "<p>No se pudieron cargar las direcciones: " . $e->getMessage() . "</p>";
        }
        ?>
        <form action="register_cliente_process.php" method="POST">
            <div class="mb-3">
                <label for="NIT" class="form-label"><i class="fas fa-id-card"></i> NIT:</label>
                <input type="text" id="NIT" name="NIT" class="form-control" placeholder="Ingrese su NIT" required>
                <div class="invalid-feedback">Este NIT ya está registrado.</div>
            </div>
            <div class="row">
                <div class="mb-3 col-md-6">
                    <label for="nombre1" class="form-label"><i class="fas fa-user"></i> Primer Nombre:</label>
                    <input type="text" id="nombre1" name="nombre1" class="form-control" placeholder="Ingrese su primer nombre" required>
                </div>
                <div class="mb-3 col-md-6">
                    <label for="nombre2" class="form-label"><i class="fas fa-user"></i> Segundo Nombre:</label>
                    <input type="text" id="nombre2" name="nombre2" class="form-control" placeholder="Ingrese su segundo nombre">
                </div>
            </div>
            <div class="row">
                <div class="mb-3 col-md-6">
                    <label for="apellido1" class="form-label"><i class="fas fa-user-tag"></i> Primer Apellido:</label>
                    <input type="text" id="apellido1" name="apellido1" class="form-control" placeholder="Ingrese su primer apellido" required>
                </div>
                <div class="mb-3 col-md-6">
                    <label for="apellido2" class="form-label"><i class="fas fa-user-tag"></i> Segundo Apellido:</label>
                    <input type="text" id="apellido2" name="apellido2" class="form-control" placeholder="Ingrese su segundo apellido">
                </div>
            </div>

            <div class="mb-3">
                <label for="correo" class="form-label"><i class="fas fa-envelope"></i> Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" class="form-control" placeholder="Ingrese su correo electrónico" required>
                <div class="invalid-feedback">Este correo ya está registrado.</div>
            </div>
            <div class="row">
                <div class="mb-3 col-md-6">
                    <label for="contrasena" class="form-label"><i class="fas fa-lock"></i> Contraseña:</label>
                    <div class="input-group">
                        <input type="password" id="contrasena" name="contrasena" class="form-control" placeholder="Cree una contraseña" required>
                        <span class="input-group-text" id="toggle-password"><i class="fas fa-eye"></i></span>
                    </div>
                </div>
                <div class="mb-3 col-md-6">
                    <label for="confirmar_contrasena" class="form-label"><i class="fas fa-lock"></i> Confirmar Contraseña:</label>
                    <div class="input-group">
                        <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" class="form-control" placeholder="Confirme su contraseña" required>
                        <span class="input-group-text" id="toggle-confirm-password"><i class="fas fa-eye"></i></span>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="mb-3 col-md-6">
                    <label for="telefono1" class="form-label"><i class="fas fa-phone"></i> Teléfono 1:</label>
                    <input type="text" id="telefono1" name="telefono1" class="form-control" placeholder="Ingrese su teléfono" required>
                </div>
                <div class="mb-3 col-md-6">
                    <label for="telefono2" class="form-label"><i class="fas fa-phone"></i> Teléfono 2:</label>
                    <input type="text" id="telefono2" name="telefono2" class="form-control" placeholder="Ingrese otro teléfono (opcional)">
                </div>
            </div>
            <div class="mb-3">
                <label for="id_direccion" class="form-label"><i class="fas fa-map-marker-alt"></i> Dirección:</label>
                <select id="id_direccion" name="id_direccion" class="form-select">
                    <option value="">Seleccione su dirección...</option>
                    <?php foreach ($direcciones as $direccion): ?>
                        <option value="<?php echo $direccion['id_direccion']; ?>">
                            <?php echo htmlspecialchars($direccion['localidad'] . ', ' . $direccion['municipio'] . ', ' . $direccion['departamento']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <!-- Opción para Agregar Nueva Dirección -->
            <div class="mb-4">
                <label for="agregar_direccion" class="checkbox-label"><i class="fas fa-plus-circle"></i> ¿No encuentra su dirección? Agregue una nueva:</label>
                <input type="checkbox" id="agregar_direccion" name="agregar_direccion">
            </div>

            <div id="nueva_direccion_form">
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
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-user-plus"></i> Registrarse</button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Mostrar/ocultar contraseña
        document.getElementById('toggle-password').addEventListener('click', function() {
            const passwordField = document.getElementById('contrasena');
            const passwordFieldType = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', passwordFieldType);
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        document.getElementById('toggle-confirm-password').addEventListener('click', function() {
            const confirmPasswordField = document.getElementById('confirmar_contrasena');
            const confirmPasswordFieldType = confirmPasswordField.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPasswordField.setAttribute('type', confirmPasswordFieldType);
            this.querySelector('i').classList.toggle('fa-eye-slash');
        });

        // Mostrar el formulario de nueva dirección si se marca el checkbox
        document.getElementById('agregar_direccion').addEventListener('change', function() {
            const nuevaDireccionForm = document.getElementById('nueva_direccion_form');
            nuevaDireccionForm.style.display = this.checked ? 'block' : 'none';
        });

        // Validación AJAX para verificar si el NIT ya está registrado
        document.getElementById('NIT').addEventListener('blur', function() {
            const nitInput = this;
            fetch(`verificar_cliente.php?NIT=${nitInput.value}`)
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        nitInput.classList.add('is-invalid');
                    } else {
                        nitInput.classList.remove('is-invalid');
                    }
                })
                .catch(error => console.error('Error:', error));
        });

        // Validación AJAX para verificar si el correo ya está registrado
        document.getElementById('correo').addEventListener('blur', function() {
            const emailInput = this;
            fetch(`verificar_cliente.php?correo=${emailInput.value}`)
                .then(response => response.json())
                .then(data => {
                    if (data.exists) {
                        emailInput.classList.add('is-invalid');
                    } else {
                        emailInput.classList.remove('is-invalid');
                    }
                })
                .catch(error => console.error('Error:', error));
        });
    </script>
</body>

</html>