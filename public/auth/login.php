<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container mt-5">
        <div class="text-center mb-4">
            <img src="../assets/img/logo.png" alt="Logo de la Aplicación" class="img-fluid" style="max-width: 150px;">
        </div>

        <h2 class="text-center">Iniciar Sesión</h2>

        <?php
        include_once '../../src/config/config.php';
        if (isset($_SESSION['message'])) {
            showAlert($_SESSION['message']['type'], $_SESSION['message']['text']);
            unset($_SESSION['message']);  // Limpiar el mensaje después de mostrarlo
        }
        ?>

        <form action="login_process.php" method="POST" class="mx-auto" style="max-width: 400px;">
            <div class="mb-3">
                <label for="correo" class="form-label"><i class="fas fa-envelope"></i> Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" class="form-control" required>
            </div>

            <div class="mb-3 position-relative">
                <label for="contrasena" class="form-label"><i class="fas fa-lock"></i> Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" class="form-control" required>
                <i class="fas fa-eye position-absolute" id="togglePassword" style="top: 50%; right: 10px; cursor: pointer;"></i>
            </div>

            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-sign-in-alt"></i> Iniciar Sesión</button>
        </form>

        <div class="text-center mt-3">
            <a href="forgot_password.php" class="d-block">¿Olvidaste tu contraseña?</a>
            <a href="register.php" class="d-block">Crear una cuenta</a>
        </div>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        const togglePassword = document.querySelector('#togglePassword');
        const password = document.querySelector('#contrasena');

        togglePassword.addEventListener('click', function (e) {
            const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
            password.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>
