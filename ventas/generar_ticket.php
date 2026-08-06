<?php
date_default_timezone_set('America/Asuncion');
require_once '../conexion/conexion.php';
require_once '../vendor/setasign/fpdf/fpdf.php';

/* ======================
   PARÁMETROS
====================== */
$id  = intval($_GET['id'] ?? 0);
$dni_get = isset($_GET['dni']) && $_GET['dni'] !== '' ? trim($_GET['dni']) : '';
$descarga = isset($_GET['download']) && $_GET['download'] == '1';

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
    mp.nombre AS metodo_pago_nombre,
    mo.codigo AS moneda_codigo

  FROM ventas v
  LEFT JOIN usuario u ON u.idusuario = v.usuario_idusuario
  LEFT JOIN clientes c ON c.idCliente = v.clientes_idCliente
  LEFT JOIN metodo_pago mp
    ON mp.idmetodo_pago = v.metodo_pago_idmetodo_pago
  LEFT JOIN moneda mo
    ON mo.idmoneda = v.moneda_idmoneda

  WHERE v.idVenta = ?
");
$qVenta->execute([$id]);
$venta = $qVenta->fetch(PDO::FETCH_ASSOC);

if (!$venta) {
    die('Venta no encontrada.');
}

/* ======================
   COTIZACIÓN ACTUAL (para convertir el total si se cobró en ARS/USD)
====================== */
$qCot = $conexion->query("SELECT usd_pyg, ars_pyg FROM cotizacion ORDER BY id DESC LIMIT 1");
$cotizacion = $qCot->fetch(PDO::FETCH_ASSOC) ?: ['usd_pyg' => 0, 'ars_pyg' => 0];

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
function renderTicket(FPDF $pdf, $venta, $items, $dni_final, $cotizacion) {

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

    // Si se pagó en ARS o USD, mostramos el equivalente según la cotización
    $monedaCodigo = strtoupper(trim($venta['moneda_codigo'] ?? ''));

    if ($monedaCodigo === 'USD' && !empty($cotizacion['usd_pyg'])) {
        $convertido = $total / (float)$cotizacion['usd_pyg'];
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetX($left);
        $pdf->Cell($ancho, 5, conv('Pagado en USD: US$ ' . number_format($convertido, 2, ',', '.')), 0, 1, 'R');
    } elseif ($monedaCodigo === 'ARS' && !empty($cotizacion['ars_pyg'])) {
        $convertido = $total / (float)$cotizacion['ars_pyg'];
        $pdf->SetFont('Arial', '', 9);
        $pdf->SetX($left);
        $pdf->Cell($ancho, 5, conv('Pagado en ARS: $ ' . money($convertido)), 0, 1, 'R');
    }

    $pdf->Ln(2);
}

/* ======================
   GENERAR PDF ORIGINAL (se muestra/imprime)
====================== */
$pdf = new FPDF('P', 'mm', [80, 297]);
$pdf->SetMargins(2, 5, 2);
$pdf->SetAutoPageBreak(true, 2);
$pdf->AddPage();

renderTicket($pdf, $venta, $items, $dni_final, $cotizacion);

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

renderTicket($pdfArchivo, $venta, $items, $dni_final, $cotizacion);

$dirArchivo = __DIR__ . '/copias_archivo';
if (!is_dir($dirArchivo)) {
    mkdir($dirArchivo, 0775, true);
}
$pdfArchivo->Output('F', $dirArchivo . "/ticket_{$id}_archivo.pdf");

$pdf->Output($descarga ? 'D' : 'I', "ticket_$id.pdf");
exit;