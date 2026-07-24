<?php
date_default_timezone_set('America/Asuncion');
require_once '../conexion/conexion.php';
require_once '../vendor/setasign/fpdf/fpdf.php';

/* ======================
   PARÁMETROS
====================== */
$id  = intval($_GET['id'] ?? 0);
$dni_get = isset($_GET['dni']) && $_GET['dni'] !== '' ? trim($_GET['dni']) : '';

if ($id <= 0) {
    die('ID inválido.');
}

/* ======================
   CONSULTA VENTA
====================== */
$qVenta = $conexion->prepare("
  SELECT 
    v.*, 
    u.usuario, 
    u.nombre, 
    c.dni AS dni_cliente,
    mp.nombre AS metodo_pago_nombre

  FROM ventas v
  LEFT JOIN usuario u ON u.idusuario = v.usuario_idusuario
  LEFT JOIN clientes c ON c.idCliente = v.clientes_idCliente
  LEFT JOIN metodo_pago mp 
    ON mp.idmetodo_pago = v.metodo_pago_idmetodo_pago

  WHERE v.idVenta = ?
");
$qVenta->execute([$id]);
$venta = $qVenta->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    die('Venta no encontrada.');
}

/* ======================
   DETALLE
====================== */
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

/* ======================
   DNI FINAL
====================== */
$dni_final = '-';
if (!empty($venta['dni_cliente'])) {
    $dni_final = $venta['dni_cliente'];
} elseif (!empty($dni_get)) {
    $dni_final = $dni_get;
}

/* ======================
   HELPERS
====================== */
function conv($txt) {
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', (string)$txt);
}

function money($n) {
    return number_format((float)$n, 0, ',', '.');
}

/* ======================
   RENDER TICKET
====================== */
function renderTicket(FPDF $pdf, $venta, $items, $dni_final) {

    // Ajuste fino real hacia la izquierda
    $offsetX = -8;

    // Estructura base del ticket
    $leftBase   = 5;
    $rightBase  = 75;

    // Se mueve todo el bloque, pero nunca a negativo
    $left  = max(1, $leftBase + $offsetX);     // 5 + (-3) = 2
    $right = $rightBase + $offsetX;            // 75 + (-3) = 72
    $ancho = $right - $left;                   // 70 mm útiles

    // Columnas tabla
    $wProducto = 40;
    $wCant     = 10;
    $wPrecio   = $ancho - $wProducto - $wCant; // resto

    // TITULO
    $pdf->SetFont('Arial', 'B', 10);
    $pdf->SetX($left);
    $pdf->Cell($ancho, 6, conv('NOTA DE PRESUPUESTO N° ' . $venta['idVenta']), 0, 1, 'C');
        $fecha_py = date('d/m/Y H:i:s');
    // DATOS
    $pdf->SetFont('Arial', '', 8);
    $pdf->SetX($left);
    $pdf->Cell($ancho, 4, conv('Fecha: ' . $fecha_py), 0, 1, 'L');
    $pdf->SetX($left);
    $pdf->Cell($ancho, 4, conv('CI/RUC: ' . $dni_final), 0, 1, 'L');
    $pdf->SetX($left);
    $pdf->Cell($ancho, 4, conv('Vendedor: ' . $venta['nombre']), 0, 1, 'L');
    $pdf->SetX($left);
$pdf->Cell($ancho, 4, conv('Forma de pago: ' . $venta['metodo_pago_nombre']), 0, 1, 'L');

    $pdf->Ln(3);

    // TABLA CABECERA
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->SetX($left);
    $pdf->Cell($wProducto, 5, conv('Producto'), 0, 0, 'L');
    $pdf->Cell($wCant, 5, conv('Cant'), 0, 0, 'C');
    $pdf->Cell($wPrecio, 5, conv('Gs.'), 0, 1, 'R');

    $pdf->Line($left, $pdf->GetY(), $right, $pdf->GetY());

    // TABLA DETALLE
    $pdf->SetFont('Arial', '', 8);
    $total = 0;

    foreach ($items as $it) {
        $sub = $it['cantidad'] * $it['precio_unitario'];
        $total += $sub;

        $texto = $it['producto'];
        if (!empty($it['marca'])) {
            $texto .= ' (' . $it['marca'] . ')';
        }

        $y = $pdf->GetY();

        // Producto
        $pdf->SetXY($left, $y);
        $pdf->MultiCell($wProducto, 4, conv($texto), 0, 'L');

        $alto = $pdf->GetY() - $y;
        if ($alto < 4) {
            $alto = 4;
        }

        // Cantidad
        $pdf->SetXY($left + $wProducto, $y);
        $pdf->Cell($wCant, $alto, conv($it['cantidad']), 0, 0, 'C');

        // Precio
        $pdf->SetXY($left + $wProducto + $wCant, $y);
        $pdf->Cell($wPrecio, $alto, conv(money($sub)), 0, 1, 'R');
    }

    // TOTAL
    $pdf->Line($left, $pdf->GetY(), $right, $pdf->GetY());

    $pdf->SetFont('Arial', 'B', 11);
    $pdf->SetX($left);
    $pdf->Cell($wProducto + $wCant, 7, '', 0, 0);
    $pdf->Cell($wPrecio, 7, conv('TOTAL: Gs. ' . money($total)), 0, 1, 'R');

    $pdf->Ln(2);

    // FOOTER
    $pdf->SetFont('Arial', 'I', 8);
    $pdf->SetX($left);
    $pdf->Cell($ancho, 4, conv('Gracias por su compra'), 0, 1, 'C');
    $pdf->SetX($left);
    $pdf->Cell($ancho, 4, conv('Seguinos en Instagram'), 0, 1, 'C');

    $pdf->Ln(3);
}

/* ======================
   GENERAR PDF ORIGINAL (se muestra/imprime)
====================== */
$pdf = new FPDF('P', 'mm', [80, 297]);
$pdf->SetMargins(2, 5, 2);
$pdf->SetAutoPageBreak(true, 2);
$pdf->AddPage();

renderTicket($pdf, $venta, $items, $dni_final);

/* ======================
   GENERAR PDF COPIA ARCHIVO (se guarda en el servidor, no se imprime)
====================== */
$pdfArchivo = new FPDF('P', 'mm', [80, 297]);
$pdfArchivo->SetMargins(2, 5, 2);
$pdfArchivo->SetAutoPageBreak(true, 2);
$pdfArchivo->AddPage();

$pdfArchivo->SetFont('Arial', 'B', 10);
$pdfArchivo->Cell(0, 5, conv('COPIA ARCHIVO'), 0, 1, 'C');
$pdfArchivo->Ln(2);

renderTicket($pdfArchivo, $venta, $items, $dni_final);

$dirArchivo = __DIR__ . '/copias_archivo';
if (!is_dir($dirArchivo)) {
    mkdir($dirArchivo, 0775, true);
}
$pdfArchivo->Output('F', $dirArchivo . "/ticket_{$id}_archivo.pdf");

$pdf->Output('I', "ticket_$id.pdf");
exit;