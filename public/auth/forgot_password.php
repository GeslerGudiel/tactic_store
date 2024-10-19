<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Recuperar Contraseña</h2>
        <p class="text-center">Ingresa tu correo electrónico y te enviaremos un enlace para recuperar tu contraseña.</p>

        <?php
        include_once '../../src/config/config.php';
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

        <form action="forgot_password_process.php" method="POST" class="mx-auto" style="max-width: 400px;">
            <div class="mb-3">
                <label for="correo" class="form-label"><i class="fas fa-envelope"></i> Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-warning w-100"><i class="fas fa-paper-plane"></i> Enviar Enlace de Recuperación</button>
        </form>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
