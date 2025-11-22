<?php
require_once '../vendor/setasign/fpdf/fpdf.php';

class FacturaParaguayaPDF extends FPDF
{
    public $cliNombre;
    public $cliApellido;
    public $cliDni;
    public $cliCelular;

    // Conversión FPDF ISO
    function conv($txt) {
        return mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
    }

    // Mes en español
    function mesEsp() {
        $meses = [
            'January' => 'ENERO','February' => 'FEBRERO','March' => 'MARZO','April' => 'ABRIL',
            'May' => 'MAYO','June' => 'JUNIO','July' => 'JULIO','August' => 'AGOSTO',
            'September' => 'SEPTIEMBRE','October' => 'OCTUBRE','November' => 'NOVIEMBRE','December' => 'DICIEMBRE'
        ];
        return $meses[date('F')];
    }

    // -------------------------------------------------------------
    // HEADER
    // -------------------------------------------------------------
    function Header()
    {
        // Marco general
        $this->SetDrawColor(0,0,0);
        $this->Rect(10, 10, 190, 277);

        // Banner superior
        $this->SetFillColor(235, 235, 235);
        $this->Rect(10, 10, 190, 32, 'F');

        // Logo
        $logoPath = "../uploads/img/logo_motosshoppy.jpg";
        if (file_exists($logoPath)) {
            $this->Image($logoPath, 12, 12, 70, 28);
        }

        // Título
        $this->SetXY(90, 14);
        $this->SetFont('Arial', 'B', 16);
        $this->Cell(53, 7, $this->conv('RECTIFICADORA PAIVA'), 0, 2, 'C');

        $this->SetFont('Arial', '', 7);
        $this->MultiCell(60, 3.2, $this->conv(
            "Reparación de tapas - Armado de cigüeñal\n".
            "Rectificación de cilindros\n".
            "Ruta N°1 Km 2,5 - Encarnación - Itapúa - Paraguay\n".
            "Cel: (0975) 651 002 - jgaravandino@gmail.com"
        ), 0, 'C');

        // Cuadro timbrado derecha
        $xR = 150; $yR = 12;
        $this->Rect($xR, $yR, 48, 28);

        $this->SetXY($xR + 2, $yR + 2);
        $this->SetFont('Arial', '', 7);
        $this->MultiCell(43, 3, $this->conv(
            "TIMBRADO N°: 18.348.377\n".
            "Fecha Inicio Vigencia: 02/10/2025\n".
            "Fecha Fin Vigencia: 31/10/2026\n".
            "RUC: 3.281.779-7"
        ), 0, 'L');

        // Factura
        $this->SetXY($xR + 2, $yR + 16);
        $this->SetFont('Arial', 'B', 12);
        $this->Cell(44, 5, $this->conv('FACTURA'), 0, 2, 'C');
        $this->Cell(44, 5, $this->conv('N° ...-...-.......'), 0, 0, 'C');

        $this->Ln(18);
    }

    // -------------------------------------------------------------
    // DATOS CLIENTE
    // -------------------------------------------------------------
    function datosCliente()
    {
        $this->SetFont('Arial', '', 8);
        $this->SetY(45);

        // FECHA
        $this->Cell(22, 6, $this->conv('FECHA'), 1, 0, 'L');
        $this->Cell(28, 6, $this->conv(date("d/m/Y")), 1, 0, 'L');

        $this->Cell(18, 6, $this->conv('DE'), 1, 0, 'L');
        $this->Cell(22, 6, $this->conv($this->mesEsp()), 1, 0, 'L');

        $this->Cell(12, 6, $this->conv('DE 20'), 1, 0, 'L');
        $this->Cell(14, 6, $this->conv(substr(date("Y"),2)), 1, 0, 'L');

        // MÉTODO DE PAGO
$this->Cell(39, 6, $this->conv('MÉTODO DE PAGO'), 1, 0, 'L');

// valor del método, tomado de variable externa
$metodo = $this->metodoPago ?? '---';

$this->Cell(35, 6, $this->conv($metodo), 1, 1, 'C');


        // CLIENTE
        $this->Cell(40, 6, $this->conv('NOMBRE O RAZÓN SOCIAL:'), 1, 0, 'L');
        $this->Cell(85, 6, $this->conv($this->cliNombre . " " . $this->cliApellido), 1, 0, 'L');

        $this->Cell(22, 6, $this->conv('C.I. O RUC:'), 1, 0, 'L');
        $this->Cell(43, 6, $this->conv($this->cliDni), 1, 1, 'L');

        // DIRECCIÓN
        $this->Cell(20, 6, $this->conv('DIRECCIÓN:'), 1, 0, 'L');
        $this->Cell(100, 6, '', 1, 0, 'L');

        $this->Cell(36, 6, $this->conv('NOTA DE REMISIÓN N°:'), 1, 0, 'L');
        $this->Cell(34, 6, '', 1, 1, 'L');

        $this->Ln(2);
    }

    // -------------------------------------------------------------
    // TABLA ITEMS
    // -------------------------------------------------------------
    function tablaItems($items)
    {
        $wCant = 15;
        $wDesc = 80;
        $wPU   = 25;
        $wEx   = 23;
        $w5    = 23;
        $w10   = 24;
        $h     = 6;

        // Check compatible en ISO-8859-1
        $check = "3";
        // ENCABEZADO
        $this->SetFont('Arial','B',8);
        $this->Cell($wCant,$h,$this->conv('CANT.'),1,0,'C');
        $this->Cell($wDesc,$h,$this->conv('CLASE DE MERCADERÍAS Y/O SERVICIOS'),1,0,'C');
        $this->Cell($wPU,$h,$this->conv('PRECIO UNITARIO'),1,0,'C');
        $this->Cell($wEx,$h,$this->conv('EXENTAS'),1,0,'C');
        $this->Cell($w5,$h,$this->conv('5%'),1,0,'C');
        $this->Cell($w10,$h,$this->conv('10% '),1,1,'C');

        $this->SetFont('Arial','',8);

        // TOTALES
        $total10 = 0;

        foreach ($items as $it) {

    $this->Cell($wCant,$h,$it['cant'],1,0,'C');
    $this->Cell($wDesc,$h,$this->conv($it['desc']),1,0,'L');

    $this->Cell($wPU,$h,number_format($it['pu'],0,',','.'),1,0,'R');

    // EXENTAS
    $this->Cell($wEx,$h,'0',1,0,'R');

    // IVA 5% VACÍO
    $this->Cell($w5,$h,'',1,0,'C');

    // IVA 10% → SIN CHECK
    $this->Cell($w10,$h,'',1,1,'C');

    $total10 += $it['pu'];
}


        // RELLENO — SOLO 5 FILAS
        $maxFilas = 5;
        for ($i = count($items); $i < $maxFilas; $i++) {
            $this->Cell($wCant,$h,'',1,0);
            $this->Cell($wDesc,$h,'',1,0);
            $this->Cell($wPU,$h,'',1,0);
            $this->Cell($wEx,$h,'',1,0);
            $this->Cell($w5,$h,'',1,0);
            $this->Cell($w10,$h,'',1,1);
        }

        // TOTALES
        $this->Ln(2);
        $this->SetFont('Arial', '', 8);

        $valorParcial = $total10 - ($total10 / 11); // precio sin IVA 10%
$valorParcial = round($valorParcial);

$this->Cell(30,6,$this->conv('VALOR PARCIAL'),1,0,'L');
$this->Cell(160,6,number_format($valorParcial,0,',','.'),1,1,'R');


        $this->Cell(40,6,$this->conv('TOTAL A PAGAR Gs.'),1,0,'L');
        $this->Cell(150,6,number_format($total10,0,',','.'),1,1,'R');

        // IVA
$iva10calc = $total10 / 11;

// ENCABEZADO
$this->SetFont('Arial','',8);
$this->Cell(50,6,$this->conv('LIQUIDACIÓN DEL IVA:'),1,0,'L');
$this->Cell(40,6,'5%',1,0,'C');
$this->Cell(40,6,'10%',1,0,'C');
$this->Cell(60,6,'TOTAL IVA',1,1,'C');

// FILA DE VALORES
$this->Cell(50,6,'',1,0);

// IVA 5%
$this->Cell(40,6,'0',1,0,'R');

// IVA 10% con ✔
$this->SetFont('ZapfDingbats','',11);
$this->Cell(40,6,"3",1,0,'C');   // ✔

$this->SetFont('Arial','',8);

// TOTAL IVA
$this->Cell(60,6,number_format($iva10calc,0,',','.'),1,1,'R');


        $this->Ln(3);
    }
}

// ======================================================
// DATOS DEL FORMULARIO
// ======================================================
$cliNombre = $_POST['cliNombreFactura'] ?? 'Juan';
$cliApellido = $_POST['cliApellidoFactura'] ?? 'García';
$cliDni = $_POST['cliDniFactura'] ?? '3.456.789-0';
$cliCelular = $_POST['cliCelularFactura'] ?? '0972 445566';

// ITEMS DE PRUEBA
$items = [
    ['cant'=>2,'desc'=>'Servicio rectificación cilindro','pu'=>250000],
    ['cant'=>1,'desc'=>'Cambio de aros y pistón','pu'=>180000],
    ['cant'=>1,'desc'=>'Rectificación tapa cilindro','pu'=>220000],
];

// GENERACIÓN PDF
$pdf = new FacturaParaguayaPDF('P','mm','A4');

$pdf->cliNombre = $cliNombre;
$pdf->cliApellido = $cliApellido;
$pdf->cliDni = $cliDni;
$pdf->cliCelular = $cliCelular;

// ORIGINAL + DUPLICADO
$pdf->AddPage();
$pdf->datosCliente();
$pdf->tablaItems($items);

$pdf->AddPage();
$pdf->datosCliente();
$pdf->tablaItems($items);

if (ob_get_length()) ob_clean();
$pdf->Output('I','factura_paraguaya_final.pdf');
exit;
