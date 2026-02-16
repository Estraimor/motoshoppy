<?php
date_default_timezone_set('America/Asuncion');
require_once '../../conexion/conexion.php';
require_once '../../vendor/autoload.php';
session_start();

$desde = $_GET['desde'] ?? null;
$hasta = $_GET['hasta'] ?? null;

if (!$desde || !$hasta) {
    die('Fechas inválidas');
}

$encargado = $_SESSION['nombre'] . ' ' . $_SESSION['apellido'];

class PDF extends FPDF {

 function Header(){

    // --- LOGO ---
    $this->Image(
        __DIR__.'/../../imagenes/logo_motosshoppy.png',
        10,   // X
        -17,   // Y
        75  // ancho (ajustado)
    );

    // --- MOVER A LA DERECHA DEL LOGO ---
    $this->SetY(12);
    $this->SetX(60);

    // Título principal
    $this->SetFont('Arial','B',16);
    $this->Cell(130,8,'DETALLE DE OPERACIONES',0,1,'C');

    // Subtítulo
    $this->SetFont('Arial','',10);
    $this->SetX(60);
    $this->Cell(130,6,'MOTOSHOPP - Sistema de Gestion Comercial',0,1,'C');

    // --- ESPACIO ---
    $this->Ln(10);

    // Línea separadora
    $this->Line(10,35,200,35);

    $this->Ln(8);
}


function Footer(){
    $this->SetY(-18);
    $this->SetFont('Arial','I',8);
    $this->SetTextColor(120,120,120);

    $this->Cell(
        0,
        6,
        'Documento generado automaticamente por el sistema - '.date('d/m/Y H:i'),
        0,
        1,
        'C'
    );

    $this->Cell(
        0,
        6,
        'Pagina '.$this->PageNo().'/{nb}',
        0,
        0,
        'C'
    );
}


    function SectionTitle($text){
        $this->SetFont('Arial','B',11);
        $this->SetFillColor(230,230,230);
        $this->Cell(0,8,$text,0,1,'L',true);
        $this->Ln(2);
    }

    function TableHeader(){
        $this->SetFont('Arial','B',9);
        $this->SetFillColor(240,240,240);

        $this->Cell(30,8,'Fecha',1,0,'C',true);
        $this->Cell(50,8,'Producto',1,0,'C',true);
        $this->Cell(12,8,'Cant',1,0,'C',true);
        $this->Cell(30,8,'Precio',1,0,'C',true);
        $this->Cell(30,8,'Metodo',1,0,'C',true);
        $this->Cell(20,8,'Moneda',1,1,'C',true);
    }
}

$pdf = new PDF('P','mm','A4');
$pdf->AliasNbPages();
$pdf->AddPage();

/* ================= INFO GENERAL ================= */

$pdf->SetFont('Arial','',10);
$pdf->Cell(0,6,"Periodo: $desde - $hasta",0,1);
$pdf->Cell(0,6,"Encargado: $encargado",0,1);
$pdf->Cell(0,6,'Emitido: '.date('d/m/Y H:i'),0,1);
$pdf->Ln(6);

/* ================= CONSULTA ================= */

$stmt = $conexion->prepare("
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

$stmt->execute([':d'=>$desde, ':h'=>$hasta]);

$pdf->SectionTitle('REGISTRO DE VENTAS');
$pdf->TableHeader();

$pdf->SetFont('Arial','',9);
$pdf->SetFillColor(248,248,248);

$totalGeneral = 0;
$totalesMoneda = [];

$fill = false;

while ($d = $stmt->fetch(PDO::FETCH_ASSOC)) {

    $importe = $d['cantidad'] * $d['precio_unitario'];
    $totalGeneral += $importe;

    if(!isset($totalesMoneda[$d['moneda']])){
        $totalesMoneda[$d['moneda']] = 0;
    }

    $totalesMoneda[$d['moneda']] += $importe;

    $pdf->Cell(30,7,date('d/m/Y H:i', strtotime($d['fecha'])),1,0,'L',$fill);
    $pdf->Cell(50,7,substr($d['producto'],0,28),1,0,'L',$fill);
    $pdf->Cell(12,7,$d['cantidad'],1,0,'C',$fill);
    $pdf->Cell(30,7,number_format($importe,2,',','.'),1,0,'R',$fill);
    $pdf->Cell(30,7,ucfirst($d['metodo']),1,0,'L',$fill);
    $pdf->Cell(20,7,$d['moneda'],1,1,'C',$fill);

    $fill = !$fill;
}

/* ================= RESUMEN ================= */

$pdf->Ln(6);
$pdf->SectionTitle('RESUMEN DE TOTALES');

$pdf->SetFont('Arial','',10);

foreach($totalesMoneda as $moneda => $total){
    $pdf->Cell(120,8,"Total en $moneda",1,0,'R');
    $pdf->Cell(40,8,number_format($total,2,',','.'),1,1,'R');
}

$pdf->Ln(4);
$pdf->SetFont('Arial','B',11);
$pdf->Cell(120,9,'TOTAL GENERAL',1,0,'R');
$pdf->Cell(40,9,number_format($totalGeneral,2,',','.'),1,1,'R');

$pdf->Ln(4);
$pdf->SetFont('Arial','I',9);
$pdf->MultiCell(
    0,
    5,
    "El presente documento detalla todas las ventas registradas en el periodo seleccionado, incluyendo metodo de pago y moneda utilizada."
);

/* ================= PEDIDOS A PROVEEDORES ================= */

$stmtProv = $conexion->prepare("
    SELECT 
        r.fecha_llegada,
        pr.empresa AS proveedor,
        p.nombre AS producto,
        rd.cantidad,
        rd.costo,
        r.estado,
        r.numero_factura
    FROM reposicion_detalle rd
    JOIN reposicion r 
        ON r.idreposicion = rd.reposicion_idreposicion
    JOIN producto p 
        ON p.idProducto = rd.producto_idProducto
    JOIN proveedores pr 
        ON pr.idproveedores = r.proveedores_idproveedores
    WHERE DATE(r.fecha_llegada) BETWEEN :d AND :h
      AND r.estado = 'impactado'
    ORDER BY r.fecha_llegada ASC
");

$stmtProv->execute([
    ':d' => $desde,
    ':h' => $hasta
]);

$pdf->AddPage();
$pdf->SectionTitle('REGISTRO DE PEDIDOS A PROVEEDORES');

/* ===== HEADER TABLA ===== */

$pdf->SetFont('Arial','B',9);
$pdf->SetFillColor(240,240,240);

$pdf->Cell(25,8,'Fecha',1,0,'C',true);
$pdf->Cell(35,8,'Proveedor',1,0,'C',true);
$pdf->Cell(40,8,'Producto',1,0,'C',true);
$pdf->Cell(12,8,'Cant',1,0,'C',true);
$pdf->Cell(28,8,'Costo Unit.',1,0,'C',true);
$pdf->Cell(30,8,'Total',1,0,'C',true);
$pdf->Cell(20,8,'Factura',1,1,'C',true);

/* ===== CUERPO TABLA ===== */

$pdf->SetFont('Arial','',9);

$totalCompras = 0;
$fill = false;

while ($r = $stmtProv->fetch(PDO::FETCH_ASSOC)) {

    $costoUnit = (float)($r['costo'] ?? 0);
    $cantidad  = (int)($r['cantidad'] ?? 0);

    $total = $cantidad * $costoUnit;
    $totalCompras += $total;

    $pdf->Cell(25,7,date('d/m/Y', strtotime($r['fecha_llegada'])),1,0,'L',$fill);
    $pdf->Cell(35,7,substr($r['proveedor'],0,18),1,0,'L',$fill);
    $pdf->Cell(40,7,substr($r['producto'],0,22),1,0,'L',$fill);
    $pdf->Cell(12,7,$cantidad,1,0,'C',$fill);
    $pdf->Cell(28,7,number_format($costoUnit,2,',','.'),1,0,'R',$fill);
    $pdf->Cell(30,7,number_format($total,2,',','.'),1,0,'R',$fill);
    $pdf->Cell(20,7,$r['numero_factura'] ?? '-',1,1,'C',$fill);

    $fill = !$fill;
}


/* ================= RESULTADO OPERATIVO ================= */

$pdf->Ln(6);
$pdf->SectionTitle('RESULTADO OPERATIVO');

$gananciaBruta = $totalGeneral - $totalCompras;

$pdf->SetFont('Arial','',10);

$pdf->Cell(120,8,'Total Ventas',1,0,'R');
$pdf->Cell(40,8,number_format($totalGeneral,2,',','.'),1,1,'R');

$pdf->Cell(120,8,'Total Compras Proveedores',1,0,'R');
$pdf->Cell(40,8,number_format($totalCompras,2,',','.'),1,1,'R');

$pdf->SetFont('Arial','B',11);
$pdf->Cell(120,9,'RESULTADO BRUTO',1,0,'R');
$pdf->Cell(40,9,number_format($gananciaBruta,2,',','.'),1,1,'R');



$pdf->Output('I','detalle_operaciones.pdf');
