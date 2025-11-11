<?php
require_once '../conexion/conexion.php';
require_once '../vendor/setasign/fpdf/fpdf.php';

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die('ID inválido.');

// === OBTENER DATOS DE VENTA ===
$qVenta = $conexion->prepare("
  SELECT v.*, u.usuario, c.apellido, c.nombre, c.dni, c.celular
  FROM ventas v
  LEFT JOIN usuario u ON u.idusuario = v.usuario_id
  LEFT JOIN clientes c ON c.idCliente = v.cliente_id
  WHERE v.idVenta = ?
");
$qVenta->execute([$id]);
$venta = $qVenta->fetch(PDO::FETCH_ASSOC);

if (!$venta) die('Venta no encontrada.');

// === DETALLE DE PRODUCTOS ===
$qDetalle = $conexion->prepare("
  SELECT d.*, p.nombre AS producto
  FROM detalle_venta d
  JOIN producto p ON p.idProducto = d.producto_id
  WHERE d.venta_id = ?
");
$qDetalle->execute([$id]);
$items = $qDetalle->fetchAll(PDO::FETCH_ASSOC);

// === FUNCIONES AUXILIARES ===
function conv($txt) {
    return mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
}

function encabezado($pdf) {
    $pdf->SetFont('Arial', 'B', 16);
    $pdf->Cell(0, 8, conv('MOTOSHOPPY'), 0, 1, 'C');
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 6, conv('Av. Roca 1234 - Posadas, Misiones'), 0, 1, 'C');
    $pdf->Cell(0, 6, conv('Tel: (376) 482-0012 | motoshoppy.com'), 0, 1, 'C');
    $pdf->Ln(5);
}

function renderFactura($pdf, $venta, $items, $titulo) {
    // ✅ Agregar página antes de escribir
    $pdf->AddPage();

    encabezado($pdf);

    $pdf->SetFont('Arial', 'B', 14);
    $pdf->Cell(0, 10, conv("FACTURA $titulo"), 0, 1, 'C');
    $pdf->Ln(3);

    // === Datos de venta ===
    $pdf->SetFont('Arial', '', 10);
    $pdf->Cell(0, 8, conv('Fecha: ' . $venta['fecha']), 0, 1);
    $pdf->Cell(0, 8, conv('Vendedor: ' . $venta['usuario']), 0, 1);
    $pdf->Ln(4);
    $pdf->Cell(0, 8, conv('Cliente: ' . strtoupper(trim($venta['apellido'] . ' ' . $venta['nombre']))), 0, 1);
    $pdf->Cell(0, 8, conv('DNI: ' . ($venta['dni'] ?? '-') . ' | Cel: ' . ($venta['celular'] ?? '-')), 0, 1);
    $pdf->Ln(6);

    // === Encabezado tabla ===
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(100, 8, conv('Producto'), 1, 0, 'L');
    $pdf->Cell(25, 8, conv('Cant.'), 1, 0, 'C');
    $pdf->Cell(30, 8, conv('P. Unitario'), 1, 0, 'R');
    $pdf->Cell(35, 8, conv('Subtotal'), 1, 1, 'R');

    // === Filas ===
    $pdf->SetFont('Arial', '', 10);
    $total = 0;
    foreach ($items as $it) {
        $sub = $it['cantidad'] * $it['precio_unitario'];
        $total += $sub;
        $pdf->Cell(100, 8, conv($it['producto']), 1);
        $pdf->Cell(25, 8, $it['cantidad'], 1, 0, 'C');
        $pdf->Cell(30, 8, number_format($it['precio_unitario'], 2, ',', '.'), 1, 0, 'R');
        $pdf->Cell(35, 8, number_format($sub, 2, ',', '.'), 1, 1, 'R');
    }

    // === Total ===
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(155, 8, conv('TOTAL'), 1, 0, 'R');
    $pdf->Cell(35, 8, number_format($total, 2, ',', '.'), 1, 1, 'R');

    $pdf->Ln(10);
    $pdf->Cell(0, 8, conv('Firma: _________________________'), 0, 1, 'L');

    // === Línea separadora si hay duplicado ===
    if ($titulo === 'ORIGINAL') {
        $pdf->Ln(5);
        $pdf->SetDrawColor(200, 200, 200);
        $pdf->Line(10, $pdf->GetY(), 200, $pdf->GetY());
        $pdf->Ln(5);
    }
}

// === GENERAR PDF ===
$pdf = new FPDF();
renderFactura($pdf, $venta, $items, 'ORIGINAL');
renderFactura($pdf, $venta, $items, 'DUPLICADO');

if (ob_get_length()) ob_clean();
$pdf->Output('I', "factura_$id.pdf");
exit;
