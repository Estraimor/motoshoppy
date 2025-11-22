<?php
require_once '../conexion/conexion.php';
require_once '../vendor/setasign/fpdf/fpdf.php';

/* ============================================================
   ===============  CLASE COMPLETA DE LA FACTURA  ===============
   ============================================================ */

class FacturaParaguayaPDF extends FPDF
{
    public $cliNombre;
    public $cliApellido;
    public $cliDni;
    public $cliCelular;
    public $metodoPago;

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
        $metodo = $this->metodoPago ?? '---';
        $this->Cell(35, 6, $this->conv($metodo), 1, 1, 'C');

        // CLIENTE
        $this->Cell(40, 6, $this->conv('NOMBRE O RAZÓN SOCIAL:'), 1, 0, 'L');
        $this->Cell(85, 6, $this->conv($this->cliNombre . " " . $this->cliApellido), 1, 0, 'L');

        $this->Cell(22, 6, $this->conv('C.I. O RUC:'), 1, 0, 'L');
        $this->Cell(43, 6, $this->conv($this->cliDni), 1, 1, 'L');

        // CELULAR
        $this->Cell(15, 6, $this->conv('Celular:'), 1, 0, 'L');
        $this->Cell(100, 6, $this->conv($this->cliCelular), 1, 0, 'L');

        $this->Cell(36, 6, $this->conv('NOTA DE REMISIÓN N°:'), 1, 0, 'L');
        $this->Cell(39, 6, '', 1, 1, 'L');

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

        $this->SetFont('Arial','B',8);
        $this->Cell($wCant,$h,$this->conv('CANT.'),1,0,'C');
        $this->Cell($wDesc,$h,$this->conv('CLASE DE MERCADERÍAS Y/O SERVICIOS'),1,0,'C');
        $this->Cell($wPU,$h,$this->conv('PRECIO UNITARIO'),1,0,'C');
        $this->Cell($wEx,$h,$this->conv('EXENTAS'),1,0,'C');
        $this->Cell($w5,$h,$this->conv('5%'),1,0,'C');
        $this->Cell($w10,$h,$this->conv('10% '),1,1,'C');

        $this->SetFont('Arial','',8);

        $total10 = 0;

        foreach ($items as $it) {

            $this->Cell($wCant,$h,$it['cant'],1,0,'C');
            $this->Cell($wDesc,$h,$this->conv($it['desc']),1,0,'L');

            $this->Cell($wPU,$h,number_format($it['pu'],0,',','.'),1,0,'R');

            $this->Cell($wEx,$h,'0',1,0,'R');

            $this->Cell($w5,$h,'',1,0,'C');

            $this->Cell($w10,$h,'',1,1,'C');

            $total10 += $it['pu'];
        }

        // Relleno
        $maxFilas = 5;
        for ($i = count($items); $i < $maxFilas; $i++) {
            $this->Cell($wCant,$h,'',1,0);
            $this->Cell($wDesc,$h,'',1,0);
            $this->Cell($wPU,$h,'',1,0);
            $this->Cell($wEx,$h,'',1,0);
            $this->Cell($w5,$h,'',1,0);
            $this->Cell($w10,$h,'',1,1);
        }

        $this->Ln(2);
        $this->SetFont('Arial', '', 8);

        $valorParcial = round($total10 - ($total10 / 11));
        $this->Cell(30,6,$this->conv('VALOR PARCIAL'),1,0,'L');
        $this->Cell(160,6,number_format($valorParcial,0,',','.'),1,1,'R');

        $this->Cell(40,6,$this->conv('TOTAL A PAGAR Gs.'),1,0,'L');
        $this->Cell(150,6,number_format($total10,0,',','.'),1,1,'R');

        $iva10calc = $total10 / 11;

        // IVA
        $this->SetFont('Arial','',8);
        $this->Cell(50,6,$this->conv('LIQUIDACIÓN DEL IVA:'),1,0,'L');
        $this->Cell(40,6,'5%',1,0,'C');
        $this->Cell(40,6,'10%',1,0,'C');
        $this->Cell(60,6,'TOTAL IVA',1,1,'C');

        $this->Cell(50,6,'',1,0);
        $this->Cell(40,6,'0',1,0,'R');

        $this->SetFont('ZapfDingbats','',11);
        $this->Cell(40,6,"3",1,0,'C');   // ✔

        $this->SetFont('Arial','',8);
        $this->Cell(60,6,number_format($iva10calc,0,',','.'),1,1,'R');

        $this->Ln(3);
    }
}

/* ============================================================
   ===============  FIN DE LA CLASE COMPLETA  =================
   ============================================================ */


/* ============================================================
   ===============     CARGA DE LA VENTA     ==================
   ============================================================ */

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die("ID inválido");

// Obtener cabecera
$sql = $conexion->prepare("
    SELECT v.*, c.nombre AS cliNombre, c.apellido AS cliApellido,
           c.dni AS cliDni, c.celular AS cliCelular
    FROM ventas v
    LEFT JOIN clientes c ON c.idCliente = v.cliente_id
    WHERE v.idVenta = ?
");
$sql->execute([$id]);
$venta = $sql->fetch(PDO::FETCH_ASSOC);
if (!$venta) die("Venta no encontrada");

// Obtener detalle
$sql2 = $conexion->prepare("
    SELECT d.cantidad, d.precio_unitario, 
       p.nombre,
       m.nombre_marca,
       c.nombre_categoria,
       p.modelo
FROM detalle_venta d
JOIN producto p ON p.idProducto = d.producto_id
join categoria c on p.Categoria_idCategoria = c.idCategoria
join marcas m on p.marcas_idmarcas = m.idmarcas 
WHERE d.venta_id = ?

");
$sql2->execute([$id]);
$rows = $sql2->fetchAll(PDO::FETCH_ASSOC);

$items = [];
foreach ($rows as $r) {
    // Construcción dinámica de descripción completa
$descripcion = $r['nombre'];

if (!empty($r['nombre_categoria'])) {
    $descripcion .= " – Categoría: " . $r['nombre_categoria'];
}

if (!empty($r['nombre_marca'])) {
    $descripcion .= " – Marca: " . $r['nombre_marca'];
}

if (!empty($r['modelo'])) {
    $descripcion .= " – Modelo: " . $r['modelo'];
}

$items[] = [
    'cant' => intval($r['cantidad']),
    'desc' => $descripcion,
    'pu'   => intval($r['precio_unitario']) * intval($r['cantidad']) // subtotal
];

}

/* ============================================================
   ===============      GENERAR FACTURA      ==================
   ============================================================ */

$pdf = new FacturaParaguayaPDF('P','mm','A4');

$pdf->cliNombre  = $venta['cliNombre'];
$pdf->cliApellido = $venta['cliApellido'];
$pdf->cliDni     = $venta['cliDni'];
$pdf->cliCelular = $venta['cliCelular'];
$pdf->metodoPago = $venta['metodo_pago'];

$pdf->AddPage();
$pdf->datosCliente();
$pdf->tablaItems($items);

if (ob_get_length()) ob_clean();
$pdf->Output('I', "factura_$id.pdf");
exit;
