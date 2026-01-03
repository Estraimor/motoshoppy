<?php
require_once '../../conexion/conexion.php';
require_once '../../vendor/autoload.php';
session_start();

$desde = $_GET['desde'] ?? null;
$hasta = $_GET['hasta'] ?? null;

if(!$desde || !$hasta){
    die("Fechas inválidas");
}

$encargado = $_SESSION['nombre']." ".$_SESSION['apellido'];

/* ============================================
   OBTENER ÚLTIMA COTIZACIÓN
============================================ */
$cot = $conexion->query("
    SELECT * FROM cotizacion
    ORDER BY fecha_actualizacion DESC
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

$usd_ars = $cot['usd_ars'];
$usd_pyg = $cot['usd_pyg'];
$ars_pyg = $cot['ars_pyg'];

/* ============================================
   CONVERSIÓN DESDE PYG → MONEDA DE PAGO
============================================ */
function convertir_desde_pyg($monto_pyg, $moneda, $usd_pyg, $ars_pyg){
    switch($moneda){

        case 'USD':   // PYG → USD
            return $monto_pyg / $usd_pyg;

        case 'ARS':   // PYG → ARS
            return $monto_pyg / $ars_pyg;

        case 'PYG':
        default:
            return $monto_pyg;
    }
}

/* ============================================
   CONVERSIÓN DESDE MONEDA DE PAGO → PYG
============================================ */
function convertir_a_pyg($monto, $moneda, $usd_pyg, $ars_pyg){
    switch($moneda){

        case 'USD':   // USD → PYG
            return $monto * $usd_pyg;

        case 'ARS':   // ARS → PYG
            return $monto * $ars_pyg;

        case 'PYG':
        default:
            return $monto;
    }
}

/* ============================================
   PDF
============================================ */
class PDF extends FPDF {

    function Header(){
        $this->Image(__DIR__.'/../../imagenes/logo_motosshoppy.jpg', 12, 8, 60, 40);

        $this->SetFont('Arial','B',14);
        $this->Cell(0,10,'CIERRE DE CAJA - MOTOSHOPPY',0,1,'R');
        $this->SetFont('Arial','',10);
        $this->Cell(0,6,'Sistema de Gestion Comercial',0,1,'R');
        $this->Ln(20);
        $this->Line(10,50,200,50);
        $this->Ln(4);
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
}

$pdf = new PDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->AddPage();

$pdf->SetFont('Arial','',10);
$pdf->Cell(0,6,"Periodo: $desde → $hasta",0,1);
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
JOIN metodo_pago mp ON mp.idmetodo_pago=v.metodo_pago_idmetodo_pago
JOIN moneda mo ON mo.idmoneda=v.moneda_idmoneda
WHERE DATE(v.fecha) BETWEEN :d AND :h
GROUP BY metodo, moneda
");
$stmtMetodo->execute([':d'=>$desde,':h'=>$hasta]);

$stmtDetalle = $conexion->prepare("
SELECT v.fecha, p.nombre producto,
       dv.cantidad, dv.precio_unitario,
       mp.nombre metodo, mo.codigo moneda
FROM detalle_venta dv
JOIN ventas v ON v.idVenta=dv.ventas_idVenta
JOIN producto p ON p.idProducto=dv.producto_idProducto
JOIN metodo_pago mp ON mp.idmetodo_pago=v.metodo_pago_idmetodo_pago
JOIN moneda mo ON mo.idmoneda=v.moneda_idmoneda
WHERE DATE(v.fecha) BETWEEN :d AND :h
ORDER BY v.fecha ASC
");
$stmtDetalle->execute([':d'=>$desde,':h'=>$hasta]);

/* ============================================
   RESUMEN POR MÉTODO + EQUIVALENTE EN PYG
============================================ */
$pdf->SectionTitle('RESUMEN POR METODO DE PAGO');
$pdf->TableHeader([
    55=>'Metodo',
    25=>'Moneda',
    40=>'Total Pagado',
    45=>'Equivalente PYG'
]);

$totalGeneralPyg = 0;

while($r=$stmtMetodo->fetch(PDO::FETCH_ASSOC)){

    $totalMoneda = convertir_desde_pyg(
        $r['total_pyg'],
        $r['moneda'],
        $usd_pyg,$ars_pyg
    );

    $equivalentePyg = convertir_a_pyg(
        $totalMoneda,
        $r['moneda'],
        $usd_pyg,$ars_pyg
    );

    $pdf->ZebraRow([
        55=>ucfirst($r['metodo']),
        25=>$r['moneda'],
        40=>number_format($totalMoneda,2,',','.'),
        45=>number_format($equivalentePyg,2,',','.')
    ]);

    $totalGeneralPyg += $equivalentePyg;
}

$pdf->Ln(4);

/* ============================================
   DETALLE DE OPERACIONES (precio en moneda de pago)
============================================ */
$pdf->SectionTitle('DETALLE DE OPERACIONES');
$pdf->TableHeader([
    33=>'Fecha/Hora',
    50=>'Producto',
    15=>'Cant',
    28=>'Precio Pagado',
    25=>'Metodo',
    20=>'Moneda'
]);

while($d=$stmtDetalle->fetch(PDO::FETCH_ASSOC)){

    $precioMoneda = convertir_desde_pyg(
        $d['precio_unitario'],
        $d['moneda'],
        $usd_pyg,$ars_pyg
    );

    $pdf->ZebraRow([
        33=>$d['fecha'],
        50=>substr($d['producto'],0,28),
        15=>$d['cantidad'],
        28=>number_format($precioMoneda,2,',','.'),
        25=>$d['metodo'],
        20=>$d['moneda']
    ]);
}

$pdf->Ln(4);

/* ============================================
   TOTAL GENERAL (PYG)
============================================ */
$pdf->SetFont('Arial','B',12);
$pdf->Cell(110,10,'TOTAL GENERAL (PYG)',1,0,'R');
$pdf->Cell(40,10,number_format($totalGeneralPyg,2,',','.'),1,1,'R');

$pdf->Output('I','cierre_caja_motoshoppy.pdf');
