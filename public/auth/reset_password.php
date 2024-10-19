<?php
include_once '../../src/config/config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $token = $_POST['token'];
    $nueva_contrasena = $_POST['nueva_contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];

    if ($nueva_contrasena !== $confirmar_contrasena) {
        redirectWithMessage('reset_password.php?token=' . $token, 'error', 'Las contraseñas no coinciden.');
    } else {
        $hashed_password = password_hash($nueva_contrasena, PASSWORD_BCRYPT);

        try {
            $db = getDBConnection();

            // Verificar el token
            $query = "SELECT id FROM usuarios WHERE token_recuperacion = :token";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':token', $token, PDO::PARAM_STR);
            $stmt->execute();

            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($usuario) {
                // Actualizar la contraseña y eliminar el token
                $query = "UPDATE usuarios SET contrasena = :contrasena, token_recuperacion = NULL WHERE id = :id";
                $stmt = $db->prepare($query);
                $stmt->bindParam(':contrasena', $hashed_password, PDO::PARAM_STR);
                $stmt->bindParam(':id', $usuario['id'], PDO::PARAM_INT);
                $stmt->execute();

                redirectWithMessage('login.php', 'success', 'Contraseña actualizada correctamente. Ahora puedes iniciar sesión.');
            } else {
                redirectWithMessage('reset_password.php?token=' . $token, 'error', 'Token no válido.');
            }

        } catch (PDOException $e) {
            redirectWithMessage('reset_password.php?token=' . $token, 'error', 'Error: ' . $e->getMessage());
        }
    }
} else {
    if (isset($_GET['token'])) {
        $token = $_GET['token'];
    } else {
        redirectWithMessage('forgot_password.php', 'error', 'Token no proporcionado.');
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"> <!-- Font Awesome -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Restablecer Contraseña</h2>

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

        <form action="reset_password.php" method="POST" class="mx-auto" style="max-width: 400px;">
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <div class="mb-3 position-relative">
                <label for="nueva_contrasena" class="form-label"><i class="fas fa-lock"></i> Nueva Contraseña:</label>
                <input type="password" id="nueva_contrasena" name="nueva_contrasena" class="form-control" required>
                <i class="fas fa-eye position-absolute" id="toggleNewPassword" style="top: 50%; right: 10px; cursor: pointer;"></i>
            </div>

            <div class="mb-3 position-relative">
                <label for="confirmar_contrasena" class="form-label"><i class="fas fa-lock"></i> Confirmar Nueva Contraseña:</label>
                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" class="form-control" required>
                <i class="fas fa-eye position-absolute" id="toggleConfirmPassword" style="top: 50%; right: 10px; cursor: pointer;"></i>
            </div>

            <button type="submit" class="btn btn-success w-100"><i class="fas fa-key"></i> Restablecer Contraseña</button>
        </form>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Funcionalidad para mostrar/ocultar la nueva contraseña
        const toggleNewPassword = document.querySelector('#toggleNewPassword');
        const newPassword = document.querySelector('#nueva_contrasena');

        toggleNewPassword.addEventListener('click', function (e) {
            const type = newPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            newPassword.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });

        const toggleConfirmPassword = document.querySelector('#toggleConfirmPassword');
        const confirmPassword = document.querySelector('#confirmar_contrasena');

        toggleConfirmPassword.addEventListener('click', function (e) {
            const type = confirmPassword.getAttribute('type') === 'password' ? 'text' : 'password';
            confirmPassword.setAttribute('type', type);
            this.classList.toggle('fa-eye');
            this.classList.toggle('fa-eye-slash');
        });
    </script>
</body>
</html>