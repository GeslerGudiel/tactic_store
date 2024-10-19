<?php
session_start();

// Verificar si el usuario tiene rol de administrador o superadmin
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

include_once '../../src/config/database.php';

$database = new Database();
$db = $database->getConnection();

// Obtener lista de clientes y emprendedores
$query_clientes = "SELECT id_cliente, CONCAT(nombre1, ' ', apellido1) AS nombre_cliente FROM cliente";
$stmt_clientes = $db->prepare($query_clientes);
$stmt_clientes->execute();
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);

$query_emprendedores = "SELECT id_emprendedor, CONCAT(nombre1, ' ', apellido1) AS nombre_emprendedor FROM emprendedor";
$stmt_emprendedores = $db->prepare($query_emprendedores);
$stmt_emprendedores->execute();
$emprendedores = $stmt_emprendedores->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enviar Notificación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <div class="container mt-5">
        <h2 class="text-center"><i class="fas fa-bell"></i> Enviar Notificación</h2>

        <form id="form-notificacion" method="POST">
            <div class="mb-3">
                <label for="titulo" class="form-label"><i class="fas fa-heading"></i> Título de la Notificación</label>
                <input type="text" class="form-control" id="titulo" name="titulo" required>
            </div>

            <div class="mb-3">
                <label for="mensaje" class="form-label"><i class="fas fa-comment-alt"></i> Mensaje</label>
                <textarea class="form-control" id="mensaje" name="mensaje" rows="5" required></textarea>
            </div>

            <div class="mb-3">
                <label for="destinatario" class="form-label"><i class="fas fa-users"></i> Enviar a:</label>
                <select class="form-select" id="destinatario" name="destinatario" required>
                    <option value="todos_clientes">Todos los clientes</option>
                    <option value="todos_emprendedores">Todos los emprendedores</option>
                    <option value="cliente">Cliente específico</option>
                    <option value="emprendedor">Emprendedor específico</option>
                </select>
            </div>

            <div class="mb-3" id="seleccionar_cliente" style="display: none;">
                <label for="id_cliente" class="form-label"><i class="fas fa-user"></i> Seleccionar Cliente</label>
                <select class="form-select" id="id_cliente" name="id_cliente">
                    <option value="">Seleccionar...</option>
                    <?php foreach ($clientes as $cliente): ?>
                        <option value="<?php echo $cliente['id_cliente']; ?>"><?php echo htmlspecialchars($cliente['nombre_cliente']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3" id="seleccionar_emprendedor" style="display: none;">
                <label for="id_emprendedor" class="form-label"><i class="fas fa-user-tie"></i> Seleccionar Emprendedor</label>
                <select class="form-select" id="id_emprendedor" name="id_emprendedor">
                    <option value="">Seleccionar...</option>
                    <?php foreach ($emprendedores as $emprendedor): ?>
                        <option value="<?php echo $emprendedor['id_emprendedor']; ?>"><?php echo htmlspecialchars($emprendedor['nombre_emprendedor']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-paper-plane"></i> Enviar Notificación</button>
        </form>
    </div>

    <script>
        // Mostrar el campo de selección basado en el destinatario seleccionado
        document.getElementById('destinatario').addEventListener('change', function() {
            var value = this.value;
            document.getElementById('seleccionar_cliente').style.display = (value === 'cliente') ? 'block' : 'none';
            document.getElementById('seleccionar_emprendedor').style.display = (value === 'emprendedor') ? 'block' : 'none';
        });

        // Enviar el formulario mediante AJAX
        $("#form-notificacion").on("submit", function(e) {
            e.preventDefault();

            $.ajax({
                url: "procesar_notificacion.php",
                method: "POST",
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire('Éxito', 'Notificación enviada correctamente', 'success');
                    $("#form-notificacion")[0].reset(); // Limpiar el formulario
                    $("#seleccionar_cliente, #seleccionar_emprendedor").hide(); // Ocultar los selectores
                },
                error: function() {
                    Swal.fire('Error', 'Hubo un error al enviar la notificación', 'error');
                }
            });
        });
    </script>
</body>
</html>
