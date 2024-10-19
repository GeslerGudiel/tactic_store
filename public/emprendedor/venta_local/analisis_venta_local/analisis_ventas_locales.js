$(document).ready(function () {
    function cargarDatos(fechaInicio, fechaFin) {
        $.ajax({
            url: '/comercio_electronico/public/emprendedor/venta_local/analisis_venta_local/analisis_ventas_locales_data.php',
            type: 'GET',
            data: { fecha_inicio: fechaInicio, fecha_fin: fechaFin },
            success: function (data) {
                // Solo reemplaza el contenido del área de resultados
                $('#resultado-analisis').html(data);
            },
            error: function () {
                Swal.fire('Error', 'No se pudo cargar el análisis.', 'error');
            }
        });
    }

    // Cargar los datos del mes actual al inicio
    const fechaInicioDefault = new Date().toISOString().slice(0, 8) + '01';
    const fechaFinDefault = new Date().toISOString().slice(0, 10);
    cargarDatos(fechaInicioDefault, fechaFinDefault);

    // Evento al hacer clic en "Filtrar"
    $('#aplicar-filtro').click(function () {
        const fechaInicio = $('#fecha_inicio').val();
        const fechaFin = $('#fecha_fin').val();

        if (!fechaInicio || !fechaFin) {
            Swal.fire('Advertencia', 'Selecciona ambas fechas.', 'warning');
            return;
        }

        cargarDatos(fechaInicio, fechaFin);
    });

    // Evento al hacer clic en "Limpiar"
    $('#limpiar-filtro').click(function () {
        $('#fecha_inicio').val(fechaInicioDefault);
        $('#fecha_fin').val(fechaFinDefault);
        cargarDatos(fechaInicioDefault, fechaFinDefault);
    });
});
