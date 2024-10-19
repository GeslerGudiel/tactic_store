<?php
session_start();

// Verificar si el usuario tiene rol de administrador o superadmin
if (!isset($_SESSION['usuario_rol']) || ($_SESSION['usuario_rol'] !== 'administrador' && $_SESSION['usuario_rol'] !== 'superadmin')) {
    header("Location: ../auth/login_admin.php");
    exit;
}

// Incluir la conexión a la base de datos
include_once '../../src/config/database.php';

try {
    $database = new Database();
    $db = $database->getConnection();

    // Consulta para obtener los negocios y sus datos relacionados
    $query_negocios = "
        SELECT n.*, e.nombre1, e.apellido1, d.departamento, d.municipio, d.localidad
        FROM negocio n
        JOIN emprendedor e ON n.id_emprendedor = e.id_emprendedor
        JOIN direccion d ON n.id_direccion = d.id_direccion
        ORDER BY n.fecha_creacion DESC";

    $stmt_negocios = $db->prepare($query_negocios);
    $stmt_negocios->execute();
    $negocios = $stmt_negocios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p>Error al obtener los negocios: " . $e->getMessage() . "</p>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Negocios Registrados</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        .table-responsive {
            overflow-x: auto;
        }

        .btn-custom {
            background-color: #3498db;
            color: white;
        }

        .btn-custom:hover {
            background-color: #2980b9;
            color: white;
        }

        @media (max-width: 768px) {
            .table-responsive {
                font-size: 0.9rem;
            }
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1 class="mb-4 text-center"><i class="fas fa-store"></i> Negocios Registrados</h1>

        <!-- Búsqueda y Filtros -->
        <div class="row filter-container d-flex align-items-center justify-content-between mb-4">
            <div class="col-md-4">
                <label for="searchTerm" class="form-label"><i class="fas fa-search"></i> Buscar Negocio</label>
                <input type="text" id="searchTerm" class="form-control" placeholder="Nombre, Emprendedor o Dirección">
            </div>
            <div class="col-md-4">
                <label for="estado" class="form-label"><i class="fas fa-filter"></i> Tienda Física</label>
                <select id="estado" class="form-select">
                    <option value="">Todas</option>
                    <option value="1">Si</option>
                    <option value="2">No</option>
                </select>
            </div>
            <div class="col-md-4 d-flex justify-content-end">
                <button id="btn-filtrar" class="btn btn-primary mt-4"><i class="fas fa-filter"></i> Filtrar</button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th class="text-center"><i class="fas fa-hashtag"></i> #</th>
                        <th><i class="fas fa-briefcase"></i> Nombre del Negocio</th>
                        <th><i class="fas fa-user"></i> Emprendedor</th>
                        <th><i class="fas fa-map-marker-alt"></i> Dirección</th>
                        <th><i class="fas fa-file-alt"></i> Patente de Comercio</th>
                        <th class="text-center"><i class="fas fa-store-alt"></i> Tienda Física</th>
                        <th><i class="fas fa-calendar-alt"></i> Fecha de Creación</th>
                    </tr>
                </thead>
                <tbody id="negocios-list">
                    <!-- Aquí se cargará dinámicamente el contenido -->
                </tbody>
            </table>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function () {
        // Función para cargar todos los registros al cargar la página
        function cargarNegocios() {
            $.ajax({
                url: 'buscar_negocios.php',
                type: 'GET',
                data: {
                    searchTerm: '',
                    estado: ''
                },
                success: function (response) {
                    $('#negocios-list').html(response); // Cargar todos los negocios en la tabla
                },
                error: function () {
                    alert('No se pudieron cargar los negocios.');
                }
            });
        }

        // Llamar a la función para cargar todos los registros al cargar la página
        cargarNegocios();

        // Función para el filtro y búsqueda de negocios
        $('#btn-filtrar').on('click', function () {
            var searchTerm = $('#searchTerm').val();
            var estado = $('#estado').val();

            $.ajax({
                url: 'buscar_negocios.php',
                type: 'GET',
                data: {
                    searchTerm: searchTerm,
                    estado: estado
                },
                success: function (response) {
                    $('#negocios-list').html(response);
                },
                error: function () {
                    alert('No se pudieron cargar los negocios.');
                }
            });
        });

        // Búsqueda al presionar Enter
        $('#searchTerm').keypress(function (event) {
            if (event.which === 13) {
                $('#btn-filtrar').click();
            }
        });

        // Al presionar Enter en el campo de filtro de estado
        $('#estado').on('keypress', function(event) {
                if (event.which === 13) { // Verificar si se presionó la tecla Enter
                    $('#btn-filtrar').click(); // Disparar el clic en el botón de filtrar
                }
            });
    });
</script>

</body>

</html>