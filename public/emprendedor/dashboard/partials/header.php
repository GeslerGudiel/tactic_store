
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Emprendedor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> <!-- Asegúrate de que se carga aquí -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> <!-- SweetAlert -->
    <style>
        body {
            display: flex;
            height: 100vh;
            margin: 0;
            overflow: hidden;
            font-family: Arial, sans-serif;
        }

        /* Estilo para permitir el desplazamiento en el contenido dinámico */
        #dashboard-content {
            max-height: 100vh;
            /* Ajusta la altura máxima de la ventana */
            overflow-y: auto;
            /* Agrega el scroll vertical */
            padding-right: 15px;
            /* Evitar que el contenido se superponga con la barra de scroll */
        }


        .sidebar {
            width: 250px;
            background-color: #343a40;
            color: white;
            padding: 20px;
            flex-shrink: 0;
            transition: all 0.3s ease;
            position: relative;
            transition: width 0.4s ease-in-out;
        }

        .sidebar.hidden {
            width: 80px;
        }

        .sidebar.hidden .sidebar-item-text {
            display: none;
        }

        .sidebar.hidden i {
            margin-right: 0;
        }

        .sidebar a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 10px 15px;
            margin-bottom: 5px;
            border-radius: 4px;
            transition: background-color 0.2s ease;
        }

        .sidebar a:hover {
            background-color: #495057;
            transition: background-color 0.3s ease;
        }

        .content {
            flex-grow: 1;
            padding: 20px;
            transition: margin-left 0.3s ease;
        }

        .content.full-width {
            margin-left: 80px;
        }

        .banner {
            background-color: #28a745;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .toggle-btn {
            position: absolute;
            top: 15px;
            right: 15px;
            background-color: #495057;
            color: white;
            border: none;
            border-radius: 4px;
            padding: 5px 10px;
            cursor: pointer;
            z-index: 1000;
            transition: all 0.3s ease;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.7);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 1001;
        }

        .notification-badge {
            background-color: red;
            color: white;
            border-radius: 50%;
            padding: 5px 10px;
            font-size: 12px;
            position: relative;
            top: -10px;
            right: 10px;
        }
    </style>

</head>