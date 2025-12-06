<?php
require_once '../conexion/conexion.php';
require_once '../vendor/setasign/fpdf/fpdf.php';

/* ============================================================
   ===============  CLASE COMPLETA FACTURA  ====================
   ============================================================ */

class FacturaParaguayaPDF extends FPDF
{
    public $cliNombre;
    public $cliApellido;
    public $cliDni;
    public $cliCelular;
    public $metodoPago;

    function conv($txt) {
        return mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
    }

    function mesEsp() {
        $meses = [
            'January'=>'ENERO','February'=>'FEBRERO','March'=>'MARZO','April'=>'ABRIL',
            'May'=>'MAYO','June'=>'JUNIO','July'=>'JULIO','August'=>'AGOSTO',
            'September'=>'SEPTIEMBRE','October'=>'OCTUBRE','November'=>'NOVIEMBRE','December'=>'DICIEMBRE'
        ];
        return $meses[date('F')];
    }

    /* ------------------------------------
     * FUNCIÓN OFICIAL PARA MULTICELL ALTURA
     * ------------------------------------ */
    function NbLines($w, $txt)
    {
        $cw = &$this->CurrentFont['cw'];
        if ($w == 0)
            $w = $this->w - $this->rMargin - $this->x;
        $wmax = ($w - 2 * $this->cMargin) * 1000 / $this->FontSize;
        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] == "\n")
            $nb--;
        $sep = -1;
        $i = 0;
        $j = 0;
        $l = 0;
        $nl = 1;
        while ($i < $nb)
        {
            $c = $s[$i];
            if ($c == "\n")
            {
                $i++;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax)
            {
                if ($sep == -1)
                {
                    if ($i == $j)
                        $i++;
                }
                else
                    $i = $sep + 1;
                $sep = -1;
                $j = $i;
                $l = 0;
                $nl++;
            }
            else
                $i++;
        }
        return $nl;
    }

    /* ------------------------------------
     * ENCABEZADO
     * ------------------------------------ */

    
    function Header()
    {
        // marco
        $this->Rect(10, 10, 190, 277);

        $this->SetFillColor(235,235,235);
        $this->Rect(10,10,190,32,'F');

        // logo
        $logo = "../uploads/img/logo_motosshoppy.jpg";
        if(file_exists($logo)){
            $this->Image($logo, 12, 12, 70, 28);
        }

        // titulo
        $this->SetXY(90, 14);
        $this->SetFont('Arial','B',16);
        $this->Cell(53,7,$this->conv('RECTIFICADORA PAIVA'),0,2,'C');

        $this->SetFont('Arial','',7);
        $this->MultiCell(60,3.2,$this->conv(
            "Reparación de tapas - Armado de cigüeñal\n".
            "Rectificación de cilindros\n".
            "Ruta N°1 Km 2,5 - Encarnación - Itapúa - Paraguay\n".
            "Cel: (0975) 651 002 - jgaravandino@gmail.com"
        ),0,'C');

        // timbrado
        $xR = 150;
        $yR = 12;

        $this->Rect($xR,$yR,48,28);

        $this->SetXY($xR+2,$yR+2);
        $this->SetFont('Arial','',7);
        $this->MultiCell(43,3,$this->conv(
            "TIMBRADO N°: 18.348.377\n".
            "Inicio: 02/10/2025\n".
            "Fin: 31/10/2026\n".
            "RUC: 3.281.779-7"
        ),0,'L');

        $this->SetXY($xR+2,$yR+16);
        $this->SetFont('Arial','B',12);
        $this->Cell(44,5,$this->conv('FACTURA'),0,2,'C');
        $this->Cell(44,5,$this->conv('N° ...-...-.......'),0,0,'C');

        $this->Ln(18);
    }

    /* ------------------------------------
     * DATOS DEL CLIENTE
     * ------------------------------------ */
    function datosCliente()
    {
        $this->SetFont('Arial','',8);
        $this->SetY($this->GetY());

        // FECHA
        $this->Cell(22,6,$this->conv('FECHA'),1,0,'L');
        $this->Cell(28,6,$this->conv(date("d/m/Y")),1,0,'L');

        $this->Cell(18,6,$this->conv('DE'),1,0,'L');
        $this->Cell(22,6,$this->conv($this->mesEsp()),1,0,'L');

        $this->Cell(12,6,$this->conv('DE 20'),1,0,'L');
        $this->Cell(14,6,$this->conv(substr(date("Y"),2)),1,0,'L');

        // metodo pago
        $this->Cell(39,6,$this->conv('MÉTODO DE PAGO'),1,0,'L');
        $met = $this->metodoPago ?? '---';
        $this->Cell(35,6,$this->conv($met),1,1,'C');

        // cliente
        $this->Cell(40,6,$this->conv('NOMBRE O RAZÓN SOCIAL:'),1,0,'L');
        $this->Cell(85,6,$this->conv($this->cliNombre." ".$this->cliApellido),1,0,'L');

        $this->Cell(22,6,$this->conv('C.I / RUC:'),1,0,'L');
        $this->Cell(43,6,$this->conv($this->cliDni),1,1,'L');

        // CEL
        $this->Cell(15,6,$this->conv('Celular:'),1,0,'L');
        $this->Cell(100,6,$this->conv($this->cliCelular),1,0,'L');
        $this->Cell(36,6,$this->conv('NOTA REMISIÓN N°:'),1,0,'L');
        $this->Cell(39,6,'',1,1,'L');

        $this->Ln(2);
    }

    /* ------------------------------------
     * TABLA ITEMS (CON ALTURA DINÁMICA)
     * ------------------------------------ */
    function tablaItems($items)
    {
        $wCant  = 12;
        $wNom   = 40;
        $wCat   = 30;
        $wMarca = 28;
        $wModelo= 25;
        $wPU    = 20;
        $wEx    = 15;
        $w5     = 10;
        $w10    = 10;
        $h      = 6;

        // encabezado
        $this->SetFont('Arial','B',8);
        $this->Cell($wCant,$h,$this->conv('CANT'),1,0,'C');
        $this->Cell($wNom,$h,$this->conv('NOMBRE'),1,0,'C');
        $this->Cell($wCat,$h,$this->conv('CATEGORÍA'),1,0,'C');
        $this->Cell($wMarca,$h,$this->conv('MARCA'),1,0,'C');
        $this->Cell($wModelo,$h,$this->conv('MODELO'),1,0,'C');
        $this->Cell($wPU,$h,$this->conv('P. UNIT'),1,0,'C');
        $this->Cell($wEx,$h,$this->conv('EXENTA'),1,0,'C');
        $this->Cell($w5,$h,$this->conv('5%'),1,0,'C');
        $this->Cell($w10,$h,$this->conv('10%'),1,1,'C');

        $this->SetFont('Arial','',8);

        $total10 = 0;

        foreach($items as $it){

    // calcular lineas para el nombre
    $txt = $this->conv($it['nombre']);
    $lineas = $this->NbLines($wNom, $txt);
    $hFila = max(6, $lineas * 5);

    // salto de página
    if($this->GetY() + $hFila > 255){
        $this->AddPage();
        $this->datosCliente();

        $this->SetFont('Arial','B',8);
        $this->Cell($wCant,$h,$this->conv('CANT'),1,0,'C');
        $this->Cell($wNom,$h,$this->conv('NOMBRE'),1,0,'C');
        $this->Cell($wCat,$h,$this->conv('CATEGORÍA'),1,0,'C');
        $this->Cell($wMarca,$h,$this->conv('MARCA'),1,0,'C');
        $this->Cell($wModelo,$h,$this->conv('MODELO'),1,0,'C');
        $this->Cell($wPU,$h,$this->conv('P. UNIT'),1,0,'C');
        $this->Cell($wEx,$h,$this->conv('EXENTA'),1,0,'C');
        $this->Cell($w5,$h,$this->conv('5%'),1,0,'C');
        $this->Cell($w10,$h,$this->conv('10%'),1,1,'C');
        $this->SetFont('Arial','',8);
    }

    // === CANT ===
    $this->Cell($wCant,$hFila,$it['cant'],1,0,'C');

    // === NOMBRE (MULTICELL) ===
    $x = $this->GetX();
    $y = $this->GetY();

    // SIN BORDE -> luego dibujamos el rect manual
    $this->MultiCell($wNom,5,$txt,0,'L');

    // regresar el cursor para seguir columnas
    $this->SetXY($x+$wNom, $y);

    // dibujar borde del nombre según altura dinámica
    $this->Rect($x, $y, $wNom, $hFila);

    // === RESTO COLUMNAS (MISMA ALTURA) ===
    $this->Cell($wCat,$hFila,$this->conv($it['categoria']),1,0,'L');
    $this->Cell($wMarca,$hFila,$this->conv($it['marca']),1,0,'L');
    $this->Cell($wModelo,$hFila,$this->conv($it['modelo']),1,0,'L');
    $this->Cell($wPU,$hFila,number_format($it['pu'],0,',','.'),1,0,'R');
    $this->Cell($wEx,$hFila,'0',1,0,'R');
    $this->Cell($w5,$hFila,'',1,0,'C');
    $this->Cell($w10,$hFila,'',1,1,'C');

    $total10 += $it['pu'];
}


        // PIE
        $this->Ln(2);

        $valorParcial = round($total10 - ($total10/11));
        $this->Cell(30,6,$this->conv('VALOR PARCIAL'),1,0,'L');
        $this->Cell(160,6,number_format($valorParcial,0,',','.'),1,1,'R');

        $this->Cell(40,6,$this->conv('TOTAL A PAGAR Gs.'),1,0,'L');
        $this->Cell(150,6,number_format($total10,0,',','.'),1,1,'R');

        $iva10 = $total10/11;

        $this->Cell(50,6,$this->conv('LIQUIDACIÓN DEL IVA:'),1,0,'L');
        $this->Cell(40,6,'5%',1,0,'C');
        $this->Cell(40,6,'10%',1,0,'C');
        $this->Cell(60,6,'TOTAL IVA',1,1,'C');

        $this->Cell(50,6,'',1,0);
        $this->Cell(40,6,'0',1,0,'R');

        $this->SetFont('ZapfDingbats','',11);
        $this->Cell(40,6,"3",1,0,'C');

        $this->SetFont('Arial','',8);
        $this->Cell(60,6,number_format($iva10,2,',','.'),1,1,'R');

    }
}

/* ============================================================
   ===============    CARGA DE LA VENTA    =====================
   ============================================================ */

$id = intval($_GET['id'] ?? 0);
if ($id <= 0) die("ID inválido");

$sql = $conexion->prepare("
    SELECT 
        v.*, 
        mp.nombre AS metodo_pago_nombre,
        mn.codigo AS moneda_codigo,
        c.nombre AS cliNombre, 
        c.apellido AS cliApellido,
        c.dni AS cliDni, 
        c.celular AS cliCelular
    FROM ventas v
    LEFT JOIN clientes c 
        ON c.idCliente = v.clientes_idCliente
    LEFT JOIN metodo_pago mp 
        ON mp.idmetodo_pago = v.metodo_pago_idmetodo_pago
    LEFT JOIN moneda mn 
        ON mn.idmoneda = v.moneda_idmoneda
    WHERE v.idVenta = ?
");




$sql->execute([$id]);
$venta = $sql->fetch(PDO::FETCH_ASSOC);
if (!$venta) die("Venta no encontrada");

$sql2 = $conexion->prepare("
    SELECT 
        d.cantidad,
        d.precio_unitario,
        p.nombre,
        COALESCE(m.nombre_marca, '-') AS nombre_marca,
        COALESCE(c.nombre_categoria, '-') AS nombre_categoria,
        COALESCE(p.modelo, '-') AS modelo
    FROM detalle_venta d
    JOIN producto p ON p.idProducto = d.producto_idProducto
    LEFT JOIN categoria c ON p.Categoria_idCategoria = c.idCategoria
    LEFT JOIN marcas m ON p.marcas_idmarcas = m.idmarcas
    WHERE d.ventas_idVenta = ?
");
$sql2->execute([$id]);

$rows = $sql2->fetchAll(PDO::FETCH_ASSOC);

$items = [];
foreach ($rows as $r) {

    $nombre     = str_replace(['–','—'],'-',$r['nombre']);
    $categoria  = str_replace(['–','—'],'-',$r['nombre_categoria']);
    $marca      = str_replace(['–','—'],'-',$r['nombre_marca']);
    $modelo     = str_replace(['–','—'],'-',$r['modelo']);

    $items[] = [
    'cant'      => intval($r['cantidad']),
    'nombre'    => $nombre,
    'categoria' => $categoria,
    'marca'     => $marca,
    'modelo'    => $modelo,
    'pu'        => intval($r['precio_unitario']) * intval($r['cantidad'])
];

}


$pdf = new FacturaParaguayaPDF('P','mm','A4');

$pdf->cliNombre   = $venta['cliNombre'];
$pdf->cliApellido = $venta['cliApellido'];
$pdf->cliDni      = $venta['cliDni'];
$pdf->cliCelular  = $venta['cliCelular'];
$metodo = $venta['metodo_pago_nombre'] ?? '';
$moneda = $venta['moneda_codigo'] ?? '';

if ($moneda !== '') {
    $pdf->metodoPago = $metodo . " (" . $moneda . ")";
} else {
    $pdf->metodoPago = $metodo;
}



$pdf->AddPage();

// COPIA 1 - CLIENTE
$pdf->SetFont('Arial','B',10);
$pdf->Ln(2);

$pdf->datosCliente();
$pdf->tablaItems($items);

/* ============================================================
   ===============   COPIA PARA EL EMPLEADOR   =================
   ============================================================ */

// Nueva hoja
$pdf->AddPage();

// TEXTO DE COPIA
$pdf->SetFont('Arial','B',12);
$pdf->Cell(190,8, $pdf->conv('COPIA PARA EL EMPLEADOR'),0,1,'C');
$pdf->Ln(2);

// Volvemos a imprimir todo el encabezado y datos del cliente
$pdf->datosCliente();

// Tabla de ítems nuevamente
$pdf->tablaItems($items);

echo "<pre>";
print_r($venta);
echo "</pre>";
exit;
if (ob_get_length()) ob_clean();
$pdf->Output('I',"factura_$id.pdf");
exit;
