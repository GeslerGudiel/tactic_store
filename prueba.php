<?php
try {
    $pdo = new PDO("mysql:host=localhost;dbname=emprendedores_db", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("SELECT * FROM usuarios");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Usuario: " . $row['nombre'] . "<br>";
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Prueba de Bootstrap</title>
    <link href="public/assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="alert alert-success" role="alert">
        ¡Bootstrap está funcionando correctamente!
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center">Iniciar Sesión</h2>
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $correo = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL);
            $contrasena = $_POST['contrasena'];

            // Simular una validación de login
            if ($correo !== "usuario@ejemplo.com" || $contrasena !== "password") {
                echo '<div class="alert alert-danger" role="alert">Correo o contraseña incorrectos.</div>';
            } else {
                echo '<div class="alert alert-success" role="alert">Inicio de sesión exitoso.</div>';
            }
        }
        ?>
        <form action="" method="POST" class="mx-auto" style="max-width: 400px;">
            <div class="mb-3">
                <label for="correo" class="form-label">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" class="form-control" required>
            </div>

            <div class="mb-3">
                <label for="contrasena" class="form-label">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
        </form>
    </div>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
