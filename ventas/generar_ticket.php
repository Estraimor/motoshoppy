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
  SELECT v.*, u.usuario, c.dni AS dni_cliente, u.nombre
  FROM ventas v
  LEFT JOIN usuario u ON u.idusuario = v.usuario_idusuario
  LEFT JOIN clientes c ON c.idCliente = v.clientes_idCliente
  WHERE v.idVenta = ?
");
$qVenta->execute([$id]);
$venta = $qVenta->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    die('Venta no encontrada.');
}

$qDetalle = $conexion->prepare("
  SELECT 
      d.*, 
      p.nombre AS producto,
      m.nombre_marca AS marca
  FROM detalle_venta d
  JOIN producto p ON p.idProducto = d.producto_idProducto
  LEFT JOIN marcas m ON m.idmarcas = p.marcas_idmarcas
  WHERE d.ventas_idVenta = ?
");
$qDetalle->execute([$id]);
$items = $qDetalle->fetchAll(PDO::FETCH_ASSOC);

// ======================
// DNI final a imprimir
// ======================
$dni_final = '-';
if (!empty($venta['dni_cliente'])) {
    $dni_final = $venta['dni_cliente'];
} elseif (!empty($dni_get)) {
    $dni_final = $dni_get;
}

// ======================
// Helpers
// ======================
function conv($txt) {
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
    $pdf->Cell(0, 5, conv('Vendedor: ' . $venta['nombre']), 0, 1);
    $pdf->Ln(3);

    // Encabezado tabla
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(40, 5, conv('Producto'), 0, 0);
    $pdf->Cell(10, 5, conv('Cant'), 0, 0, 'C');
    $pdf->Cell(20, 5, conv('Subtotal Gs.'), 0, 1, 'R');
    $pdf->Line(5, $pdf->GetY(), 75, $pdf->GetY());

    $pdf->SetFont('Arial', '', 8);
    $total = 0;

    foreach ($items as $it) {

        $sub = (float)$it['cantidad'] * (float)$it['precio_unitario'];
        $total += $sub;

        $y_inicio = $pdf->GetY();

        // Texto producto + marca
        $textoProducto = $it['producto'];
        if (!empty($it['marca'])) {
            $textoProducto .= ' (' . $it['marca'] . ')';
        }

        // Producto (con salto automático)
        $pdf->MultiCell(40, 5, conv($textoProducto), 0);

        $y_fin = $pdf->GetY();
        $alto = $y_fin - $y_inicio;

        // Cantidad
        $pdf->SetXY(45, $y_inicio);
        $pdf->Cell(10, $alto, conv($it['cantidad']), 0, 0, 'C');

        // Subtotal
        $pdf->SetXY(55, $y_inicio);
        $pdf->Cell(20, $alto, conv(money($sub)), 0, 1, 'R');
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
$pdf = new FPDF('P', 'mm', [80, 297]);
$pdf->SetMargins(5, 5, 5);
$pdf->AddPage();

// Original
renderTicketBloque($pdf, $venta, $items, $dni_final, false);

// Separador
$pdf->Ln(5);
$pdf->SetFont('Arial', '', 8);
$pdf->Cell(0, 5, conv(str_repeat('.', 50)), 0, 1, 'C');
$pdf->Cell(0, 5, conv('Corte aquí'), 0, 1, 'C');
$pdf->Ln(5);

// Duplicado
renderTicketBloque($pdf, $venta, $items, $dni_final, true);

// Limpieza
if (ob_get_length()) { ob_end_clean(); }
$pdf->Output('I', "ticket_$id.pdf");
exit;
