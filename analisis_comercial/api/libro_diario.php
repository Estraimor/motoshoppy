<?php
require_once '../../conexion/conexion.php';
require_once '../../vendor/autoload.php';

$desde = $_GET['desde'] ?? null;
$hasta = $_GET['hasta'] ?? null;

if(!$desde || !$hasta){
    die('Fechas inválidas');
}

class PDF extends FPDF {

    function Header(){
        // LOGO
        $this->Image(__DIR__.'/../../imagenes/logo_motosshoppy.jpg', 12, 2, 60, 40);

        $this->SetFont('Arial','B',13);
        $this->Cell(0,6,'MOTOSHOPPY',0,1,'C');


        $this->SetFont('Arial','',10);
        $this->Cell(0,5,'Libro Diario',0,1,'C');

        $this->SetFont('Arial','',9);
        $this->Cell(0,5,'Periodo: '.$_GET['desde'].' al '.$_GET['hasta'],0,1,'C');

        $this->Ln(4);
        $this->Line(10,50,200,50);
        $this->Ln(20);
    }

    function Footer(){
        $this->SetY(-15);
        $this->SetFont('Arial','I',8);
        $this->Cell(0,10,'Pagina '.$this->PageNo().'/{nb}',0,0,'C');
    }
}

$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',9);

/* ============================
   CABECERA TABLA
============================ */
$pdf->SetFillColor(230,230,230);
$pdf->SetFont('Arial','B',9);

$pdf->Cell(30,7,'Fecha',1,0,'C',true);
$pdf->Cell(90,7,'Concepto',1,0,'C',true);
$pdf->Cell(35,7,'Debe',1,0,'C',true);
$pdf->Cell(35,7,'Haber',1,1,'C',true);

$pdf->SetFont('Arial','',9);

/* ============================
   MOVIMIENTOS
============================ */
$movimientos = [];

/* ---------- VENTAS ---------- */
$sqlVentas = "
    SELECT 
        v.fecha,
        v.total,
        mp.nombre AS metodo,
        m.codigo AS moneda
    FROM ventas v
    JOIN metodo_pago mp ON mp.idmetodo_pago = v.metodo_pago_idmetodo_pago
    JOIN moneda m ON m.idmoneda = v.moneda_idmoneda
    WHERE v.fecha BETWEEN :desde AND :hasta
";

$stmt = $conexion->prepare($sqlVentas);
$stmt->execute([
    ':desde' => $desde.' 00:00:00',
    ':hasta' => $hasta.' 23:59:59'
]);

while ($v = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $movimientos[] = [
        'fecha'    => $v['fecha'],
        'concepto' => 'Venta - '.$v['metodo'].' '.$v['moneda'],
        'debe'     => $v['total'],
        'haber'    => 0
    ];
}


/* ---------- REPOSICIONES IMPACTADAS ---------- */
$sqlRepo = "
    SELECT 
        r.fecha_llegada AS fecha,
        r.costo_total,
        p.empresa AS proveedor
    FROM reposicion r
    JOIN proveedores p ON p.idproveedores = r.proveedores_idproveedores
    WHERE 
        r.estado = 'impactado'
        AND r.fecha_llegada BETWEEN :desde AND :hasta
";

$stmt = $conexion->prepare($sqlRepo);
$stmt->execute([
    ':desde' => $desde.' 00:00:00',
    ':hasta' => $hasta.' 23:59:59'
]);

while($r = $stmt->fetch(PDO::FETCH_ASSOC)){
    $movimientos[] = [
        'fecha' => $r['fecha'],
        'concepto' => 'Reposición proveedor '.$r['proveedor'],
        'debe' => 0,
        'haber' => $r['costo_total']
    ];
}


/* ---------- ORDEN CRONOLÓGICO ---------- */
usort($movimientos, function($a,$b){
    return strtotime($a['fecha']) <=> strtotime($b['fecha']);
});

/* ============================
   IMPRIMIR FILAS
============================ */
$totalDebe  = 0;
$totalHaber = 0;

foreach($movimientos as $m){

    $pdf->Cell(30,7,date('d/m/Y',strtotime($m['fecha'])),1);
    $pdf->Cell(90,7,$m['concepto'],1);

    if($m['debe'] > 0){
        $pdf->Cell(35,7,'$ '.number_format((float)$m['debe'],2,',','.'),1,0,'R');
        $pdf->Cell(35,7,'',1);
        $totalDebe += $m['debe'];
    } else {
        $pdf->Cell(35,7,'',1);
        $pdf->Cell(35,7,'$ '.number_format((float)$m['haber'],2,',','.'),1,0,'R');
        $totalHaber += $m['haber'];
    }

    $pdf->Ln();
}

/* ============================
   TOTALES
============================ */
$pdf->SetFont('Arial','B',9);
$pdf->Cell(120,7,'TOTALES',1);
$pdf->Cell(35,7,'$ '.number_format($totalDebe,2,',','.'),1,0,'R');
$pdf->Cell(35,7,'$ '.number_format($totalHaber,2,',','.'),1,1,'R');

$pdf->Output('I','Libro_Diario.pdf');
