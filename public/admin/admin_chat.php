<?php
session_start();
include_once '../../src/config/database.php';

if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Obtener todos los emprendedores para mostrar en el chat
$query_emprendedores = "SELECT id_emprendedor, nombre1, apellido1 FROM emprendedor";
$stmt_emprendedores = $db->prepare($query_emprendedores);
$stmt_emprendedores->execute();
$emprendedores = $stmt_emprendedores->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat con Emprendedores</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .chat-box {
            height: 400px;
            overflow-y: auto;
            border: 1px solid #ccc;
            padding: 10px;
            margin-bottom: 10px;
        }

        .message {
            margin-bottom: 10px;
        }

        .message.admin {
            text-align: right;
            color: blue;
        }

        .message.emprendedor {
            text-align: left;
            color: green;
        }

        .message img {
            max-width: 100%;
            border-radius: 8px;
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h3>Chat con Emprendedores</h3>

        <!-- Seleccionar emprendedor -->
        <select id="emprendedorSelect" class="form-select mb-3">
            <option value="">Selecciona un emprendedor</option>
            <?php foreach ($emprendedores as $emprendedor): ?>
                <option value="<?php echo $emprendedor['id_emprendedor']; ?>">
                    <?php echo $emprendedor['nombre1'] . ' ' . $emprendedor['apellido1']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Chat box -->
        <div class="chat-box" id="chat-box"></div>

        <!-- Formulario de chat -->
        <form id="form-chat" enctype="multipart/form-data">
            <input type="hidden" name="id_emprendedor" id="id_emprendedor">
            <input type="hidden" name="enviado_por" value="admin">
            <div class="input-group mb-3">
                <input type="text" id="mensaje" name="mensaje" class="form-control" placeholder="Escribe tu mensaje..." disabled>
                <input type="file" name="imagen" id="imagen" class="form-control" accept="image/*" disabled>
                <button type="submit" class="btn btn-primary" disabled>Enviar</button>
            </div>
        </form>
    </div>

    <script src="chat.js"></script>
    <script>
        inicializarChat('emprendedor'); // Emprendedor específico
    </script>
</body>
</html>
