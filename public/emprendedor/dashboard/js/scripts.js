// Función para alternar la visibilidad de la barra lateral
function toggleSidebar() {
  const sidebar = document.getElementById("sidebar");
  const content = document.getElementById("content");
  sidebar.classList.toggle("hidden");
  content.classList.toggle("full-width");
}

// Función para cargar contenido dinámicamente en el dashboard
function loadContent(page, queryParams = "") {
  const contentDiv = document.getElementById("dashboard-content");
  const overlay = document.getElementById("loading-overlay");

  overlay.style.display = "flex"; // Mostrar la capa de carga

  fetch(`${page}${queryParams}`)
    .then((response) => response.text())
    .then((data) => {
      overlay.style.display = "none"; // Ocultar la capa de carga
      contentDiv.innerHTML = data;

      // Reinstanciar eventos de clic después de que se cargue el contenido
      if (page.includes("ver_inventario.php")) {
        setOrderLinks(); // Reaplicar el evento de ordenación
      } else if (page.includes("notificaciones.php")) {
        attachNotificationHandlers(); // Reaplicar el evento para las notificaciones
      }

      // Verificar si estamos en la página de reportes
      if (page.includes("ver_reportes.php")) {
        // Cargar reportes.js dinámicamente
        const script = document.createElement("script");
        script.src = "reportes.js";
        document.body.appendChild(script);
      }
    })
    .catch((error) => {
      console.error("Error al cargar el contenido:", error);
      contentDiv.innerHTML =
        "<p>Hubo un error al cargar el contenido. Por favor, intenta de nuevo.</p>";
    });
}

// Función para cargar los scripts necesarios para los reportes
function loadReportScripts() {
  if (typeof Chart === "undefined") {
    const chartScript = document.createElement("script");
    chartScript.src = "https://cdn.jsdelivr.net/npm/chart.js";
    document.body.appendChild(chartScript);
  }

  const reportesScript = document.createElement("script");
  reportesScript.src = "reportes.js";
  document.body.appendChild(reportesScript);
}

// Función para manejar las notificaciones
function attachNotificationHandlers() {
  $(".mark-as-read").click(function () {
    var id = $(this).data("id");
    $.post(
      "marcar_notificacion_leida.php",
      { id_notificacion: id },
      function (response) {
        if (response.success) {
          Swal.fire({
            icon: "success",
            title: "Notificación marcada como leída",
            confirmButtonText: "Aceptar",
          });
          loadContent("notificaciones.php"); // Recargar las notificaciones después de marcar como leída
        } else {
          Swal.fire(
            "Error",
            "No se pudo marcar la notificación como leída",
            "error"
          );
        }
      },
      "json"
    ).fail(function (jqXHR, textStatus, errorThrown) {
      console.log("Error en la solicitud AJAX: " + textStatus, errorThrown);
      Swal.fire("Error", "Hubo un problema al procesar la solicitud.", "error");
    });
  });
}

// Intervalo para verificar notificaciones no leídas cada 60 segundos
setInterval(function () {
  $.get(
    "notificaciones_no_leidas.php",
    function (data) {
      if (data.no_leidas > 0) {
        $(".notification-badge").text(data.no_leidas).show();
      } else {
        $(".notification-badge").hide();
      }
    },
    "json"
  );
}, 60000);

// Función para configurar los enlaces de ordenamiento en la tabla de inventario
function setOrderLinks() {
  $(".order-link")
    .off("click")
    .on("click", function (e) {
      e.preventDefault();

      var order_by = $(this).data("order_by");
      var current_order_direction = $(this).data("order_direction");
      var new_order_direction =
        current_order_direction === "asc" ? "desc" : "asc";
      $(this).data("order_direction", new_order_direction);

      $.get(
        "ver_inventario.php",
        {
          ajax: 1,
          order_by: order_by,
          order_direction: new_order_direction,
        },
        function (data) {
          $("#tabla-inventario").html(data);

          // Actualizar el enlace de descarga de PDF con los nuevos parámetros de orden
          $("#pdf-link").attr(
            "href",
            "ver_inventario.php?download=pdf&order_by=" +
              order_by +
              "&order_direction=" +
              new_order_direction
          );

          // Reaplicar los eventos después de actualizar el contenido
          setOrderLinks();
        }
      );
    });
}

$(document).ready(function () {
  // Manejar el envío del formulario de registro de venta local
  $("#formRegistrarVentaLocal").submit(function (event) {
    event.preventDefault(); // Evitar el envío del formulario tradicional

    $.ajax({
      url: "/comercio_electronico/public/emprendedor/venta_local/procesar_venta_local.php",
      method: "POST",
      data: $(this).serialize(),
      dataType: "json",
      success: function (response) {
        if (response.success) {
          Swal.fire("Éxito", response.message, "success");
          $("#formRegistrarVentaLocal")[0].reset(); // Limpiar el formulario
        } else {
          Swal.fire("Error", response.message, "error");
        }
      },
      error: function () {
        Swal.fire("Error", "Hubo un problema al registrar la venta.", "error");
      },
    });
  });
});

$(document).ready(function () {
  let carrito = [];

  // Evento para agregar producto al carrito
  $(".btn-agregar-carrito").click(function (event) {
    event.preventDefault(); // Evitar el comportamiento predeterminado

    const id = $(this).data("id");
    const nombre = $(this).data("nombre");
    const precio = parseFloat($(this).data("precio"));
    const cantidad = parseInt($(`.cantidad-producto[data-id="${id}"]`).val());

    // Validar la cantidad
    if (cantidad < 1 || isNaN(cantidad)) {
      Swal.fire("Error", "La cantidad debe ser al menos 1.", "error");
      return;
    }

    // Verificar si el producto ya está en el carrito
    const productoExistente = carrito.find((p) => p.id === id);
    if (productoExistente) {
      // Actualizar la cantidad si el producto ya está en el carrito
      productoExistente.cantidad += cantidad;
    } else {
      // Agregar nuevo producto al carrito
      carrito.push({ id, nombre, precio, cantidad });
    }

    // Actualizar la vista del carrito
    actualizarCarrito();

    // Mostrar alerta de éxito
    Swal.fire("Éxito", "Producto agregado al carrito.", "success");
  });

  // Función para actualizar el contenido del carrito
  function actualizarCarrito() {
    const tbody = $("#carritoTable tbody");
    tbody.empty(); // Limpiar la tabla del carrito

    let total = 0;
    carrito.forEach((producto) => {
      const subtotal = producto.precio * producto.cantidad;
      total += subtotal;

      // Generar una fila para el producto en el carrito
      const fila = `
              <tr>
                  <td>${producto.nombre}</td>
                  <td>Q${producto.precio.toFixed(2)}</td>
                  <td>${producto.cantidad}</td>
                  <td>Q${subtotal.toFixed(2)}</td>
                  <td>
                      <button class="btn btn-danger btn-eliminar" data-id="${
                        producto.id
                      }">
                          <i class="fas fa-trash"></i> Eliminar
                      </button>
                  </td>
              </tr>
          `;
      tbody.append(fila);
    });

    // Agregar fila con el total
    tbody.append(`
          <tr>
              <td colspan="3" class="text-end"><strong>Total</strong></td>
              <td><strong>Q${total.toFixed(2)}</strong></td>
              <td></td>
          </tr>
      `);
  }

  // Evento para eliminar un producto del carrito
  $("#carritoTable").on("click", ".btn-eliminar", function (event) {
    event.preventDefault(); // Evitar el comportamiento predeterminado
    const id = $(this).data("id");
    // Filtrar el carrito para eliminar el producto seleccionado
    carrito = carrito.filter((producto) => producto.id !== id);
    actualizarCarrito();
    Swal.fire("Éxito", "Producto eliminado del carrito.", "success");
  });
});

// Cargar el contenido inicial si la página está especificada
window.addEventListener("load", function () {
  const page =
    new URLSearchParams(window.location.search).get("page") || "default";
  const pagina = new URLSearchParams(window.location.search).get("pagina") || 1;
  const buscar =
    new URLSearchParams(window.location.search).get("buscar") || "";

  if (page !== "default") {
    loadContent(`${page}.php`, `?pagina=${pagina}&buscar=${buscar}`);
  }
});
