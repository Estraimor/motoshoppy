<?php
require_once '../conexion/conexion.php';
require_once '../vendor/setasign/fpdf/fpdf.php';

// ======================
// Parámetros de entrada
// ======================
$id  = intval($_GET['id'] ?? 0);
$dni_get = isset($_GET['dni']) && $_GET['dni'] !== '' ? trim($_GET['dni']) : '';

if ($id <= 0) {
    die('ID inválido.');
}

// ======================
// Consultas a BD
// ======================
$qVenta = $conexion->prepare("
  SELECT v.*, u.usuario, c.dni AS dni_cliente
  FROM ventas v
  LEFT JOIN usuario u ON u.idusuario = v.usuario_id
  LEFT JOIN clientes c ON c.idCliente = v.cliente_id
  WHERE v.idVenta = ?
");
$qVenta->execute([$id]);
$venta = $qVenta->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    die('Venta no encontrada.');
}

$qDetalle = $conexion->prepare("
  SELECT d.*, p.nombre AS producto
  FROM detalle_venta d
  JOIN producto p ON p.idProducto = d.producto_id
  WHERE d.venta_id = ?
");
$qDetalle->execute([$id]);
$items = $qDetalle->fetchAll(PDO::FETCH_ASSOC);

// ======================
// DNI final a imprimir
// ======================
$dni_final = '-';
if (!empty($venta['dni_cliente'])) {
    $dni_final = $venta['dni_cliente'];        // si hay cliente asociado a la venta
} elseif (!empty($dni_get)) {
    $dni_final = $dni_get;                      // si vino por GET desde el modal (ticket)
}

// ======================
// Helpers
// ======================
function conv($txt) {
    // Pasar a ISO-8859-1 para FPDF estándar
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string)$txt);
}
function money($n) {
    return number_format((float)$n, 0, ',', '.');
}

// ======================
// Render de cada bloque
// ======================
function renderTicketBloque(FPDF $pdf, array $venta, array $items, string $dni_final, bool $esDuplicado = false): void {
    // Encabezado
    $pdf->SetFont('Arial', 'B', 13);
    $pdf->Cell(0, 6, conv('MOTOSHOPPY'), 0, 1, 'C');

    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 5, conv('Av. Roca 1234 - Posadas, Misiones'), 0, 1, 'C');
    $pdf->Cell(0, 5, conv('Tel: (376) 482-0012 | motoshoppy.com'), 0, 1, 'C');
    $pdf->Ln(2);

    // Título
    $pdf->SetFont('Arial', 'B', 10);
    $titulo = 'TICKET N° ' . $venta['idVenta'] . ($esDuplicado ? ' (DUPLICADO)' : '');
    $pdf->Cell(0, 6, conv($titulo), 0, 1, 'C');
    $pdf->Ln(1);

    // Datos generales
    $pdf->SetFont('Arial', '', 8);
    $pdf->Cell(0, 5, conv('Fecha: ' . $venta['fecha']), 0, 1);
    $pdf->Cell(0, 5, conv('DNI Cliente: ' . $dni_final), 0, 1);
    $pdf->Cell(0, 5, conv('Vendedor: ' . $venta['usuario']), 0, 1);
    $pdf->Ln(3);

    // Tabla de ítems
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(40, 5, conv('Producto'), 0, 0);
    $pdf->Cell(10, 5, conv('Cant'), 0, 0, 'C');
    $pdf->Cell(25, 5, conv('Subtotal Gs.'), 0, 1, 'R');
    $pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY());

    $pdf->SetFont('Arial', '', 8);
    $total = 0;
    foreach ($items as $it) {
        $sub = (float)$it['cantidad'] * (float)$it['precio_unitario'];
        $total += $sub;

        // Producto
        $pdf->Cell(40, 5, conv($it['producto']), 0, 0);
        // Cantidad
        $pdf->Cell(10, 5, conv((string)$it['cantidad']), 0, 0, 'C');
        // Subtotal
        $pdf->Cell(25, 5, conv(money($sub)), 0, 1, 'R');
    }

    // Total
    $pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY());
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->Cell(0, 7, conv('TOTAL: Gs. ' . money($total)), 0, 1, 'R');
    $pdf->Ln(3);

    // Footer
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->Cell(0, 5, conv('Gracias por su compra'), 0, 1, 'C');

    if ($esDuplicado) {
        $pdf->Ln(3);
        $pdf->SetFont('Arial', 'I', 7);
        $pdf->Cell(0, 5, conv('MOTOSHOPPY - Copia válida del ticket original'), 0, 1, 'C');
    }
}

// ======================
// Generar PDF
// ======================
$pdf = new FPDF('P', 'mm', [80, 297]); // ancho ticket 80mm, alto suficiente para original+duplicado
$pdf->SetMargins(5, 5, 5);
$pdf->AddPage();

// Original
renderTicketBloque($pdf, $venta, $items, $dni_final, false);

// Separador de corte
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, conv(str_repeat('.', 50)), 0, 1, 'C');
$pdf->Cell(0, 5, conv('Corte aquí'), 0, 1, 'C');
$pdf->Ln(5);

// Duplicado
renderTicketBloque($pdf, $venta, $items, $dni_final, true);

// Limpieza de buffers por si hubo espacio/blancos previos
if (ob_get_length()) { ob_end_clean(); }
$pdf->Output('I', "ticket_$id.pdf");
exit;
