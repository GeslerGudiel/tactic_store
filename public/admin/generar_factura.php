<?php
require('../../src/config/fpdf/fpdf.php'); // Incluye la librería FPDF
include_once '../../src/config/database.php';

function generarFacturaPDF($pedido, $detalles_pedido, $cliente, $emprendedor, $pago) {
    $pdf = new FPDF();
    $pdf->AddPage();
    
    // Encabezado de la factura
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 10, 'Factura', 0, 1, 'C');
    $pdf->SetFont('Arial', '', 12);

    // Datos del emprendedor
    $pdf->Cell(100, 10, 'Emprendedor: ' . $emprendedor['nombre1'] . ' ' . $emprendedor['apellido1'], 0, 1);
    $pdf->Cell(100, 10, 'Direccion: ' . (isset($emprendedor['direccion']) ? $emprendedor['direccion'] : 'No disponible'), 0, 1);
    $pdf->Cell(100, 10, 'Telefono: ' . $emprendedor['telefono1'], 0, 1);

    // Datos del cliente
    $pdf->Cell(100, 10, 'Cliente: ' . $cliente['nombre1'] . ' ' . $cliente['apellido1'], 0, 1);
    $pdf->Cell(100, 10, 'Direccion: ' . (isset($cliente['direccion']) ? $cliente['direccion'] : 'No disponible'), 0, 1);
    $pdf->Cell(100, 10, 'Telefono: ' . $cliente['telefono1'], 0, 1);
    
    // Información del pedido
    $pdf->Ln(10);
    $pdf->Cell(100, 10, 'Fecha del Pedido: ' . $pedido['fecha_pedido'], 0, 1);
    $pdf->Cell(100, 10, 'ID del Pedido: ' . $pedido['id_pedido'], 0, 1);
    
    // Tabla de productos
    $pdf->Ln(10);
    $pdf->Cell(60, 10, 'Producto', 1);
    $pdf->Cell(20, 10, 'Cant.', 1);
    $pdf->Cell(30, 10, 'Precio Unitario', 1);
    $pdf->Cell(30, 10, 'Subtotal', 1);
    $pdf->Ln();
    
    foreach ($detalles_pedido as $detalle) {
        $pdf->Cell(60, 10, $detalle['nombre_producto'], 1);
        $pdf->Cell(20, 10, $detalle['cantidad'], 1);
        $pdf->Cell(30, 10, 'Q' . number_format($detalle['precio_unitario'], 2), 1);
        $pdf->Cell(30, 10, 'Q' . number_format($detalle['subtotal'], 2), 1);
        $pdf->Ln();
    }
    
    // Total del pedido
    $total_factura = 0;
    foreach ($detalles_pedido as $detalle) {
        $total_factura += $detalle['subtotal'];
    }
    
    $pdf->Ln(10);
    $pdf->Cell(60, 10, 'Total', 1);
    $pdf->Cell(30, 10, 'Q' . number_format($total_factura, 2), 1); // Usamos el total calculado aquí
    
    // Método de pago y estado del pago
    $pdf->Ln(20);
    $pdf->Cell(100, 10, 'Metodo de Pago: ' . $pago['metodo_pago'], 0, 1);
    $pdf->Cell(100, 10, 'Estado del Pago: ' . $pago['estado_pago'], 0, 1);
    
    // Guardar el PDF en el servidor
    $ruta_factura = "../../uploads/facturas/factura_" . $pedido['id_pedido'] . ".pdf";
    $pdf->Output('F', $ruta_factura);

    return $ruta_factura;
}
?>
