<?php
date_default_timezone_set('America/Asuncion');
require_once '../../conexion/conexion.php';
require_once '../../vendor/autoload.php';
session_start();

/* ============================================
   VALIDACIONES
============================================ */
$desde = $_GET['desde'] ?? null;
$hasta = $_GET['hasta'] ?? null;

if (!$desde || !$hasta) {
    die('Fechas inválidas');
}

$encargado = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];

/* ============================================
   COTIZACIONES
============================================ */
$cot = $conexion->query("
    SELECT *
    FROM cotizacion
    ORDER BY fecha_actualizacion DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

$usd_ars = (float)$cot['usd_ars'];
$usd_pyg = (float)$cot['usd_pyg'];
$ars_pyg = (float)$cot['ars_pyg'];

/* EFECTIVO INICIAL (PYG) */
$inicial = (float)($_GET['inicial'] ?? 0);

/* ============================================
   CONVERSIONES
============================================ */
function convertir_desde_pyg($monto_pyg, $moneda, $usd_pyg, $ars_pyg){
    switch ($moneda) {
        case 'USD': return $monto_pyg / $usd_pyg;
        case 'ARS': return $monto_pyg / $ars_pyg;
        default:    return $monto_pyg;
    }
}

function convertir_a_pyg($monto, $moneda, $usd_pyg, $ars_pyg){
    switch ($moneda) {
        case 'USD': return $monto * $usd_pyg;
        case 'ARS': return $monto * $ars_pyg;
        default:    return $monto;
    }
}
function pdf_text($text){
    return mb_convert_encoding($text, 'ISO-8859-1', 'UTF-8');
}

/* ============================================
   PDF
============================================ */
class PDF extends FPDF {

    public $detalleHeader = false;

   function Header(){

    // LOGO
    $this->Image(
        __DIR__.'/../../imagenes/logo_motosshoppy.png',
        9,
        1,
        110,
        70
    );

    // 🔽 BAJAR TEXTO DEL COSTADO
    $this->SetY(25);   // probá 22–30 según gusto

    $this->SetFont('Arial','B',14);
    $this->Cell(0,10,'CIERRE DE CAJA - MOTOSHOPP',0,1,'R');

    $this->SetFont('Arial','',10);
    $this->Cell(0,6,'Sistema de Gestion Comercial',0,1,'R');

    $this->Ln(10);
    $this->Line(10,50,200,50);
    $this->Ln(6);



        // 👇 SI ESTAMOS EN DETALLE, REPETIMOS CABECERA
        if ($this->detalleHeader) {
            $this->SetFont('Arial','B',9);
            $this->SetFillColor(240,240,240);

            $this->Cell(33,8,'Fecha/Hora',1,0,'C',true);
            $this->Cell(50,8,'Producto',1,0,'C',true);
            $this->Cell(15,8,'Cant',1,0,'C',true);
            $this->Cell(28,8,'Precio Pagado',1,0,'C',true);
            $this->Cell(25,8,'Metodo',1,0,'C',true);
            $this->Cell(20,8,'Moneda',1,1,'C',true);
        }
    }

    function SectionTitle($text){
        $this->SetFont('Arial','B',11);
        $this->SetFillColor(230,230,230);
        $this->Cell(0,8,$text,0,1,'L',true);
        $this->Ln(2);
    }

    function TableHeader($headers){
        $this->SetFont('Arial','B',9);
        $this->SetFillColor(240,240,240);
        foreach($headers as $w=>$t){
            $this->Cell($w,8,$t,1,0,'C',true);
        }
        $this->Ln();
    }

    function ZebraRow($cols){
        static $fill=false;
        $this->SetFillColor(248,248,248);
        $this->SetFont('Arial','',9);
        foreach($cols as $w=>$t){
            $this->Cell($w,7,$t,1,0,'L',$fill);
        }
        $this->Ln();
        $fill=!$fill;
    }

    function CheckPageBreak($h){
        if($this->GetY() + $h > $this->PageBreakTrigger){
            $this->AddPage();
        }
    }
}


/* ============================================
   INIT PDF
============================================ */
$pdf = new PDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial','',10);
$pdf->Cell(
    0,
    6,
    pdf_text("Periodo: $desde - $hasta"),
    0,
    1
);
$pdf->Cell(0,6,"Encargado: $encargado",0,1);
$pdf->Cell(0,6,'Emitido: '.date('d/m/Y H:i'),0,1);
$pdf->Ln(4);

/* ============================================
   CONSULTAS
============================================ */
$stmtMetodo = $conexion->prepare("
    SELECT mp.nombre metodo,
           mo.codigo moneda,
           SUM(v.total) total_pyg
    FROM ventas v
    JOIN metodo_pago mp ON mp.idmetodo_pago = v.metodo_pago_idmetodo_pago
    JOIN moneda mo ON mo.idmoneda = v.moneda_idmoneda
    WHERE DATE(v.fecha) BETWEEN :d AND :h
    GROUP BY metodo, moneda
");
$stmtMetodo->execute([':d'=>$desde, ':h'=>$hasta]);

$stmtDetalle = $conexion->prepare("
    SELECT v.fecha, p.nombre producto,
           dv.cantidad, dv.precio_unitario,
           mp.nombre metodo, mo.codigo moneda
    FROM detalle_venta dv
    JOIN ventas v ON v.idVenta = dv.ventas_idVenta
    JOIN producto p ON p.idProducto = dv.producto_idProducto
    JOIN metodo_pago mp ON mp.idmetodo_pago = v.metodo_pago_idmetodo_pago
    JOIN moneda mo ON mo.idmoneda = v.moneda_idmoneda
    WHERE DATE(v.fecha) BETWEEN :d AND :h
    ORDER BY v.fecha ASC
");
$stmtDetalle->execute([':d'=>$desde, ':h'=>$hasta]);

/* ============================================
   RESUMEN POR MÉTODO
============================================ */
$pdf->SectionTitle('RESUMEN POR METODO DE PAGO');
$pdf->TableHeader([
    55=>'Metodo',
    25=>'Moneda',
    40=>'Total Pagado',
    45=>'Equivalente PYG'
]);

$totalGeneralPyg = 0;
$efectivoPyg = 0;
$efectivoUsd = 0;
$efectivoArs = 0;

while ($r = $stmtMetodo->fetch(PDO::FETCH_ASSOC)) {

    $totalMoneda = convertir_desde_pyg($r['total_pyg'], $r['moneda'], $usd_pyg, $ars_pyg);
    $equivalentePyg = convertir_a_pyg($totalMoneda, $r['moneda'], $usd_pyg, $ars_pyg);

    if (strtolower($r['metodo']) === 'efectivo') {
        if ($r['moneda'] === 'PYG') $efectivoPyg += $r['total_pyg'];
        if ($r['moneda'] === 'USD') $efectivoUsd += $totalMoneda;
        if ($r['moneda'] === 'ARS') $efectivoArs += $totalMoneda;
    }

    $pdf->ZebraRow([
        55 => ucfirst($r['metodo']),
        25 => $r['moneda'],
        40 => number_format($totalMoneda,2,',','.'),
        45 => number_format($equivalentePyg,0,',','.')
    ]);

    $totalGeneralPyg += $equivalentePyg;
}

/* ============================================
   RESUMEN DE CAJA
============================================ */
$pdf->Ln(4);
$pdf->SectionTitle('RESUMEN DE CAJA');

/* PYG */
$pdf->SetFont('Arial','B',10);
$pdf->Cell(150,8,'Caja en Guaranies (PYG)',1,1);

$pdf->SetFont('Arial','',10);
$pdf->Cell(110,8,'Efectivo inicial',1,0,'R');
$pdf->Cell(40,8,number_format($inicial,0,',','.'),1,1,'R');

$pdf->Cell(110,8,'Ingresos en efectivo',1,0,'R');
$pdf->Cell(40,8,number_format($efectivoPyg,0,',','.'),1,1,'R');

$pdf->SetFont('Arial','B',11);
$pdf->Cell(110,9,'EFECTIVO FINAL (PYG)',1,0,'R');
$pdf->Cell(40,9,number_format($inicial + $efectivoPyg,0,',','.'),1,1,'R');

/* USD */
$pdf->Ln(3);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(150,8,'Caja en Dolares (USD)',1,1);

$pdf->SetFont('Arial','',10);
$pdf->Cell(110,8,'Ingresos en efectivo',1,0,'R');
$pdf->Cell(40,8,number_format($efectivoUsd,2,',','.'),1,1,'R');

/* ARS */
$pdf->Ln(3);
$pdf->SetFont('Arial','B',10);
$pdf->Cell(150,8,'Caja en Pesos Argentinos (ARS)',1,1);

$pdf->SetFont('Arial','',10);
$pdf->Cell(110,8,'Ingresos en efectivo',1,0,'R');
$pdf->Cell(40,8,number_format($efectivoArs,2,',','.'),1,1,'R');

/* ============================================
   DETALLE
============================================ */
$pdf->CheckPageBreak(40);
$pdf->detalleHeader = true;

$pdf->SectionTitle('DETALLE DE OPERACIONES');

$pdf->TableHeader([
    33=>'Fecha/Hora',
    50=>'Producto',
    15=>'Cant',
    28=>'Precio Pagado',
    25=>'Metodo',
    20=>'Moneda'
]);

while ($d = $stmtDetalle->fetch(PDO::FETCH_ASSOC)) {
    $pdf->CheckPageBreak(8);
    $precio = convertir_desde_pyg($d['precio_unitario'], $d['moneda'], $usd_pyg, $ars_pyg);

    $pdf->ZebraRow([
        33=>$d['fecha'],
        50=>substr($d['producto'],0,28),
        15=>$d['cantidad'],
        28=>number_format($precio,2,',','.'),
        25=>$d['metodo'],
        20=>$d['moneda']
    ]);
}

/* ============================================
   TOTAL GENERAL
============================================ */
$pdf->CheckPageBreak(35);
$pdf->Ln(4);

$pdf->SetFont('Arial','B',12);
$pdf->Cell(110,10,'TOTAL GENERAL (PYG)',1,0,'R');
$pdf->Cell(40,10,number_format($totalGeneralPyg,0,',','.'),1,1,'R');

$pdf->Ln(3);
$pdf->SetFont('Arial','',9);
$pdf->MultiCell(
    0,
    5,
    pdf_text(
        "El TOTAL GENERAL (PYG) representa el equivalente en guaraníes del total de ventas realizadas en el período, considerando todas las monedas y métodos de pago.\nNo constituye efectivo disponible en caja."
    )
);

$pdf->Output('I','cierre_caja_motoshoppy.pdf');
