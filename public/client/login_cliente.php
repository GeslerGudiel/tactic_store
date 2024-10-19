<?php
session_start();

if (isset($_SESSION['message'])) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: '" . $_SESSION['message']['type'] . "',
                title: 'Atención',
                text: '" . $_SESSION['message']['text'] . "',
                confirmButtonText: 'Aceptar'
            });
        });
    </script>";
    unset($_SESSION['message']);
}

if (isset($_SESSION['id_cliente'])) {
    header("Location: principal_cliente.php");
    exit;
}

$error = '';

if (isset($_SESSION['login_error'])) {
    $error = $_SESSION['login_error'];
    unset($_SESSION['login_error']);
}

if ($error) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '$error',
            });
        });
    </script>";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .container {
            max-width: 400px;
            margin-top: 50px;
        }

        .logo {
            width: 100px;
            margin: 0 auto 20px;
            display: block;
        }

        h2 {
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            color: #007bff;
        }

        .btn-primary {
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-primary:hover {
            background-color: #0056b3;
            border-color: #004085;
        }

        p.text-center a {
            color: #007bff;
        }

        p.text-center a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Espacio para el logo de la tienda -->
        <img src="../../assets/images/logo_cliente.png" alt="Logo Tienda" class="logo">

        <h2>Iniciar Sesión</h2>

        <form action="login_process.php" method="POST">
            <div class="mb-3">
                <label for="correo" class="form-label">Correo electrónico</label>
                <input type="email" class="form-control" id="correo" name="correo" required>
            </div>
            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="contrasena" name="contrasena" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
        </form>
        <p class="mt-3 text-center">
            <a href="register_cliente.php">Crear cuenta nueva</a> | 
            <a href="forgot_password.php">¿Olvidaste tu contraseña?</a>
        </p>
    </div>
</body>
</html>
