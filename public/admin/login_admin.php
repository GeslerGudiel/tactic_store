<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Administrador</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            background-color: #f4f4f4;
        }

        .login-container {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 400px;
            width: 100%;
        }

        .login-container h2 {
            text-align: center;
            margin-bottom: 20px;
        }

        .form-control-icon {
            position: relative;
        }

        .form-control-icon input {
            padding-left: 40px;
        }

        .form-control-icon .icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #495057;
        }
    </style>
</head>

<body>
    <div class="login-container">
        <h2><i class="fas fa-user-shield"></i> Login Administrador</h2>
        <form method="POST" action="login_process_admin.php">
            <div class="mb-3 form-control-icon">
                <label for="correo" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" id="correo" name="correo" required>
                <i class="fas fa-envelope icon"></i>
            </div>
            <div class="mb-3 form-control-icon">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
                <i class="fas fa-lock icon"></i>
            </div>
            <button type="submit" class="btn btn-primary w-100">
                <i class="fas fa-sign-in-alt"></i> Iniciar sesión
            </button>
        </form>
    </div>

    <!-- Mostrar alertas si hay mensajes -->
    <?php if (isset($_GET['message'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '<?php echo htmlspecialchars($_GET['message']); ?>',
                confirmButtonText: 'Aceptar'
            });
        </script>
    <?php endif; ?>
</body>

</html>