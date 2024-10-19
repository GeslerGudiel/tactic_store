<?php
session_start();
include_once '../../src/config/database.php';

if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Obtener todos los clientes para mostrar en el chat
$query_clientes = "SELECT id_cliente, nombre1, apellido1 FROM cliente";
$stmt_clientes = $db->prepare($query_clientes);
$stmt_clientes->execute();
$clientes = $stmt_clientes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat con Clientes</title>
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

        .message.cliente {
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
        <h3>Chat con Clientes</h3>

        <!-- Seleccionar cliente -->
        <select id="clienteSelect" class="form-select mb-3">
            <option value="">Selecciona un cliente</option>
            <?php foreach ($clientes as $cliente): ?>
                <option value="<?php echo $cliente['id_cliente']; ?>">
                    <?php echo $cliente['nombre1'] . ' ' . $cliente['apellido1']; ?>
                </option>
            <?php endforeach; ?>
        </select>

        <!-- Chat box -->
        <div class="chat-box" id="chat-box"></div>

        <!-- Formulario de chat -->
        <form id="form-chat" enctype="multipart/form-data">
            <input type="hidden" name="id_cliente" id="id_cliente">
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
        inicializarChat('cliente'); // Cliente específico
    </script>
</body>
</html>
