<?php
session_start();
include_once '../../src/config/database.php';
include_once '../../src/config/funciones.php'; // Incluir la función de notificaciones

$database = new Database();
$db = $database->getConnection();

if (!isset($_SESSION['id_cliente'])) {
    $_SESSION['message'] = [
        'type' => 'warning',
        'text' => 'Debes iniciar sesión para realizar un pedido.'
    ];
    header("Location: login_cliente.php");
    exit;
}

// Verificar que el carrito no esté vacío
if (empty($_SESSION['carrito'])) {
    $_SESSION['message'] = [
        'type' => 'warning',
        'text' => 'Tu carrito está vacío. No puedes realizar un pedido.'
    ];
    header("Location: carrito.php");
    exit;
}

// Capturar el método de pago seleccionado
$metodo_pago = $_POST['metodo_pago'];

try {
    echo "Paso 1: Iniciando la transacción<br>";

    $db->beginTransaction();

    // Calcular la fecha límite para el depósito bancario si aplica
    $fecha_limite = null;
    if ($metodo_pago === 'deposito_bancario') {
        $fecha_limite = date('Y-m-d H:i:s', strtotime('+24 hours'));
    }

    // Insertar un nuevo pedido en la tabla `pedido`
    echo "Paso 2: Insertando pedido<br>";
    $query_pedido = "INSERT INTO pedido (id_cliente, fecha_pedido, estado_pedido, direccion_envio, telefono_contacto, fecha_limite)
                     VALUES (:id_cliente, :fecha_pedido, :estado_pedido, :direccion_envio, :telefono_contacto, :fecha_limite)";
    $stmt_pedido = $db->prepare($query_pedido);

    $id_cliente = $_SESSION['id_cliente'];
    $fecha_pedido = date('Y-m-d H:i:s');
    $estado_pedido = 'Pendiente';  // Estado inicial del pedido

    $direccion_envio = htmlspecialchars(strip_tags($_POST['direccion_envio']));
    $telefono_contacto = htmlspecialchars(strip_tags($_POST['telefono_contacto']));

    $stmt_pedido->bindParam(':id_cliente', $id_cliente);
    $stmt_pedido->bindParam(':fecha_pedido', $fecha_pedido);
    $stmt_pedido->bindParam(':estado_pedido', $estado_pedido);
    $stmt_pedido->bindParam(':direccion_envio', $direccion_envio);
    $stmt_pedido->bindParam(':telefono_contacto', $telefono_contacto);
    $stmt_pedido->bindParam(':fecha_limite', $fecha_limite);

    if ($stmt_pedido->execute()) {
        echo "Paso 3: Pedido insertado correctamente<br>";

        // Obtener el ID del pedido recién insertado
        $id_pedido = $db->lastInsertId();

        // Calcular el monto total del pedido
        $monto = 0;

        // Insertar cada producto del carrito en la tabla `detalle_pedido`
        foreach ($_SESSION['carrito'] as $id_producto => $detalle) {
            echo "Paso 4: Procesando producto ID $id_producto<br>";

            $query_producto = "
            SELECT p.nombre_producto, 
                   p.precio, 
                   p.stock, 
                   p.id_emprendedor, 
                   pr.precio_oferta 
            FROM producto p
            LEFT JOIN promocion pr 
                ON p.id_producto = pr.id_producto 
                AND pr.estado = 'Activo' 
                AND pr.fecha_fin >= CURDATE()
            WHERE p.id_producto = :id_producto
            FOR UPDATE";
            $stmt_producto = $db->prepare($query_producto);
            $stmt_producto->bindParam(':id_producto', $id_producto);
            $stmt_producto->execute();
            $producto = $stmt_producto->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                throw new Exception("Producto no encontrado: ID $id_producto");
            }

            $nombre_producto = $producto['nombre_producto'];
            $precio_unitario = $producto['precio_oferta'] ?? $producto['precio'];
            $cantidad = $detalle['cantidad'];
            $subtotal = $precio_unitario * $cantidad;
            $id_emprendedor = $producto['id_emprendedor'];  // Obtener el id_emprendedor

            if ($producto['stock'] < $cantidad) {
                throw new Exception("Stock insuficiente para el producto: $nombre_producto");
            }

            // Disminuir el stock del producto
            $nuevo_stock = $producto['stock'] - $cantidad;
            $query_actualizar_stock = "UPDATE producto SET stock = :nuevo_stock WHERE id_producto = :id_producto";
            $stmt_actualizar_stock = $db->prepare($query_actualizar_stock);
            $stmt_actualizar_stock->bindParam(':nuevo_stock', $nuevo_stock);
            $stmt_actualizar_stock->bindParam(':id_producto', $id_producto);
            $stmt_actualizar_stock->execute();

            // Verificar si el stock es 0 y actualizar el estado del producto
            if ($nuevo_stock == 0) {
                $query_actualizar_estado = "UPDATE producto SET estado = 'no disponible' WHERE id_producto = :id_producto";
                $stmt_actualizar_estado = $db->prepare($query_actualizar_estado);
                $stmt_actualizar_estado->bindParam(':id_producto', $id_producto);
                $stmt_actualizar_estado->execute();

                // **Agregar notificación automática aquí**
                $titulo = "Producto sin Stock";
                $mensaje = "El producto con ID #" . $id_producto . " con nombre " . $nombre_producto . " ha quedado sin stock y su estado ha cambiado a No disponible.";
                agregarNotificacion($db, null, $id_emprendedor, $titulo, $mensaje);
            }

            // Obtener el precio original del producto
            $query_precio_original = "
                SELECT nombre_producto, precio, id_emprendedor, stock 
                FROM producto 
                WHERE id_producto = :id_producto FOR UPDATE";

            $stmt_precio_original = $db->prepare($query_precio_original);
            $stmt_precio_original->bindParam(':id_producto', $id_producto);
            $stmt_precio_original->execute();
            $producto = $stmt_precio_original->fetch(PDO::FETCH_ASSOC);

            if (!$producto) {
                throw new Exception("Producto no encontrado: ID $id_producto");
            }

            $precio_original = $producto['precio'];  // Precio base del producto
            $id_emprendedor = $producto['id_emprendedor'];

            // Verificar si hay promoción activa para el producto
            $query_promocion = "
                SELECT precio_oferta 
                FROM promocion 
                WHERE id_producto = :id_producto 
                AND estado = 'Activo' 
                AND fecha_fin >= CURDATE()
                ";
            $stmt_promocion = $db->prepare($query_promocion);
            $stmt_promocion->bindParam(':id_producto', $id_producto);
            $stmt_promocion->execute();
            $promocion = $stmt_promocion->fetch(PDO::FETCH_ASSOC);

            if ($promocion) {
                // Si hay promoción, usar el precio de oferta
                $precio_unitario = $promocion['precio_oferta'];
                $descuento_aplicado = $precio_original - $precio_unitario; // Monto descontado
            } else {
                // Sin promoción, usar el precio original
                $precio_unitario = $precio_original;
                $descuento_aplicado = 0;
            }

            // Calcular subtotal
            $subtotal = $precio_unitario * $cantidad;

            // Insertar el detalle del pedido
            $query_detalle = "
                INSERT INTO detalle_pedido (
                    id_pedido, id_producto, id_emprendedor, cantidad, 
                    precio_unitario, subtotal, nombre_producto, descuento_aplicado
                ) VALUES (
                    :id_pedido, :id_producto, :id_emprendedor, :cantidad, 
                    :precio_unitario, :subtotal, :nombre_producto, :descuento_aplicado
                )
                ";
            $stmt_detalle = $db->prepare($query_detalle);
            $stmt_detalle->bindParam(':id_pedido', $id_pedido);
            $stmt_detalle->bindParam(':id_producto', $id_producto);
            $stmt_detalle->bindParam(':id_emprendedor', $id_emprendedor);
            $stmt_detalle->bindParam(':cantidad', $cantidad);
            $stmt_detalle->bindParam(':precio_unitario', $precio_unitario);
            $stmt_detalle->bindParam(':subtotal', $subtotal);
            $stmt_detalle->bindParam(':nombre_producto', $producto['nombre_producto']);
            $stmt_detalle->bindParam(':descuento_aplicado', $descuento_aplicado);
            $stmt_detalle->execute();


            // Calcular y registrar la comisión para el emprendedor
            $tasa_comision = 0.88;  //
            $monto_comision = $subtotal * $tasa_comision;

            $query_comision = "INSERT INTO comision (id_emprendedor, id_pedido, monto_comision, fecha_comision)
                               VALUES (:id_emprendedor, :id_pedido, :monto_comision, :fecha_comision)";
            $stmt_comision = $db->prepare($query_comision);
            $stmt_comision->bindParam(':id_emprendedor', $id_emprendedor);
            $stmt_comision->bindParam(':id_pedido', $id_pedido);
            $stmt_comision->bindParam(':monto_comision', $monto_comision);
            $stmt_comision->bindParam(':fecha_comision', $fecha_pedido);
            $stmt_comision->execute();

            // **Agregar notificación automática aquí**
            $titulo = "Nuevo pedido recibido";
            $mensaje = "Has recibido un nuevo pedido con ID #" . $id_pedido . " que incluye el producto " . $nombre_producto;
            agregarNotificacion($db, null, $id_emprendedor, $titulo, $mensaje);

            // Sumar al monto total
            $monto += $subtotal;
        }

        echo "Paso 5: Todos los productos procesados<br>";

        // Validar y procesar el pago
        if ($metodo_pago === 'tarjeta') {
            // Lógica para procesar el pago con tarjeta
            $estado_pago = 'Completado';
        } elseif ($metodo_pago === 'deposito_bancario') {
            // Lógica para manejar el depósito bancario
            $estado_pago = 'Pendiente';

            if (isset($_POST['subir_despues']) && $_POST['subir_despues'] === 'on') {
                // El cliente decidió subir el comprobante después
                $nombre_archivo = null; // No se sube el archivo aún

                // **AGREGAR LA ALERTA AQUÍ**
                $_SESSION['show_comprobante_alert'] = true;
            } else {
                // El cliente sube el comprobante inmediatamente
                if (isset($_FILES['imagen_comprobante']) && $_FILES['imagen_comprobante']['error'] === UPLOAD_ERR_OK) {
                    $extension = pathinfo($_FILES['imagen_comprobante']['name'], PATHINFO_EXTENSION);
                    $nombre_archivo = 'comprobante_' . $id_pedido . '.' . $extension;
                    $ruta_destino = '../../uploads/comprobantes/' . $nombre_archivo;

                    if (!move_uploaded_file($_FILES['imagen_comprobante']['tmp_name'], $ruta_destino)) {
                        throw new Exception("Error al subir el comprobante de depósito.");
                    }
                } else {
                    throw new Exception("El comprobante es obligatorio para el método de depósito bancario.");
                }
            }
        } else {
            throw new Exception("Método de pago no válido.");
        }

        // Insertar el pago en la tabla `pago`
        $query_pago = "INSERT INTO pago (id_pedido, metodo_pago, monto, fecha_pago, estado_pago, imagen_comprobante)
                       VALUES (:id_pedido, :metodo_pago, :monto, :fecha_pago, :estado_pago, :imagen_comprobante)";
        $stmt_pago = $db->prepare($query_pago);
        $stmt_pago->bindParam(':id_pedido', $id_pedido);
        $stmt_pago->bindParam(':metodo_pago', $metodo_pago);
        $stmt_pago->bindParam(':monto', $monto);
        $stmt_pago->bindParam(':fecha_pago', $fecha_pedido);
        $stmt_pago->bindParam(':estado_pago', $estado_pago);
        $stmt_pago->bindParam(':imagen_comprobante', $nombre_archivo);
        $stmt_pago->execute();

        // Confirmar la transacción
        $db->commit();

        // Limpiar el carrito
        unset($_SESSION['carrito']);

        // Establecer mensaje de éxito y redirigir a la página de confirmación
        $_SESSION['pedido_confirmado'] = true;
        $_SESSION['ultimo_pedido_id'] = $id_pedido;
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => 'Tu pedido ha sido realizado con éxito.'
        ];
        header("Location: confirmacion_pedido.php");
        exit;
    } else {
        throw new Exception("Error al insertar el pedido.");
    }
} catch (Exception $e) {
    // Revertir la transacción en caso de error
    $db->rollBack();
    $_SESSION['message'] = [
        'type' => 'error',
        'text' => 'Hubo un problema al procesar tu pedido. ' . $e->getMessage()
    ];
    header("Location: checkout.php");
    exit;
}
