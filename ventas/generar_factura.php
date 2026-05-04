<?php
// Desactivar output buffering automático para evitar conflictos con FPDF
ob_start();

ini_set('display_errors', 'Off'); // 🔥 OFF en producción para no romper el PDF
ini_set('display_startup_errors', 'Off');
error_reporting(E_ALL);

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
    public $cliDireccion;
    public $nroFactura;
    public $condicionVenta = 'CONTADO';

    function conv($txt) {
        return mb_convert_encoding($txt, 'ISO-8859-1', 'UTF-8');
    }

    // Línea punteada entre x1,y1 → x2,y2
    function LineaDotted($x1, $y1, $x2, $y2, $gap = 1.5) {
        $len = sqrt(pow($x2-$x1,2) + pow($y2-$y1,2));
        $n   = floor($len / $gap);
        for ($i = 0; $i <= $n; $i++) {
            if ($i % 2 == 0) {
                $px = $x1 + ($i / $n) * ($x2 - $x1);
                $py = $y1 + ($i / $n) * ($y2 - $y1);
                $this->SetLineWidth(0.3);
                $this->Line($px, $py, min($px + 0.8, $x2), $py);
            }
        }
        $this->SetLineWidth(0.2);
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
        $i   = 0;
        $j   = 0;
        $l   = 0;
        $nl  = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c == "\n") {
                $i++;
                $sep = -1;
                $j   = $i;
                $l   = 0;
                $nl++;
                continue;
            }
            if ($c == ' ')
                $sep = $i;
            $l += $cw[$c];
            if ($l > $wmax) {
                if ($sep == -1) {
                    if ($i == $j)
                        $i++;
                } else
                    $i = $sep + 1;
                $sep = -1;
                $j   = $i;
                $l   = 0;
                $nl++;
            } else
                $i++;
        }
        return $nl;
    }

    /* ------------------------------------
     * ENCABEZADO
     * ------------------------------------ */
    function Header()
    {
        // MARCO GENERAL
        $this->Rect(10, 10, 190, 277);

        // FONDO HEADER
        $this->SetFillColor(235, 235, 235);
        $this->Rect(10, 10, 190, 35, 'F');
        $this->Rect(10, 10, 190, 35);

        // LOGO
        $logo = "../imagenes/logo nuevo motoshop.png";
        if (file_exists($logo)) {
            $this->Image($logo, 12, 11, 95);
        }

        // BLOQUE DERECHO - NOMBRE EMPRESA
        $this->SetXY(105, 12);
        $this->SetFont('Arial', 'B', 14);
        $this->Cell(60, 6, $this->conv('RECTIFICADORA PAIVA'), 0, 1, 'L');

        $this->SetFont('Arial', '', 9);
        $this->SetX(105);
        $this->MultiCell(60, 5, $this->conv(
            "- Reparación de tapas\n" .
            "- Armado de cigüeñal\n" .
            "- Rectificación de cilindros"
        ), 0, 'L');

        $this->SetFont('Arial', 'B', 8);
        $this->SetX(105);
        $this->MultiCell(60, 4, $this->conv(
            "Ruta PY01 Km. 2 - Encarnación\n" .
            "0975 651 002"
        ), 0, 'L');

        // CAJA FACTURA (esquina superior derecha) — borde izquierdo punteado
        $this->Rect(165, 10, 35, 30);
        // Sobreescribir borde izquierdo con línea punteada
        $this->LineaDotted(165, 10, 165, 40, 1.8);

        // Timbrado
        $this->SetXY(166, 12);
        $this->SetFont('Arial', '', 6);
        $this->MultiCell(33, 3, $this->conv(
            "TIMBRADO N°: 18.348.377\n" .
            "Inicio: 02/10/2025\n" .
            "Fin: 31/10/2026\n" .
            "RUC: 3.281.779-7"
        ), 0, 'L');

        // Número de factura
        $nro = $this->nroFactura ?? '001-001-0000001';

        $this->SetXY(165, 26);
        $this->SetFont('Arial', 'B', 10);
        $this->Cell(35, 5, $this->conv('FACTURA'), 0, 0, 'C');

        $this->SetXY(165, 31);
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(35, 5, $this->conv('N° ' . $nro), 0, 0, 'C');

        $this->Ln(12);
    }

    /* ------------------------------------
     * DATOS DEL CLIENTE
     * ------------------------------------ */
    function datosCliente()
    {
        $this->SetFont('Arial', '', 8);
        $this->SetY($this->GetY());

        // FECHA
        $this->Cell(22, 6, $this->conv('FECHA'), 1, 0, 'L');
        $this->Cell(28, 6, $this->conv(date("d/m/Y")), 1, 0, 'L');
        $this->Cell(18, 6, $this->conv('DE'), 1, 0, 'L');
        $this->Cell(22, 6, $this->conv($this->mesEsp()), 1, 0, 'L');
        $this->Cell(12, 6, $this->conv('DE 20'), 1, 0, 'L');
        $this->Cell(14, 6, $this->conv(substr(date("Y"), 2)), 1, 0, 'L');
        $this->Cell(39, 6, $this->conv('MÉTODO DE PAGO'), 1, 0, 'L');
        $met = $this->metodoPago ?? '---';
        // Ajustar fuente según largo del texto
        $this->SetFont('Arial', '', strlen($met) > 22 ? 6 : 8);
        $this->Cell(35, 6, $this->conv($met), 1, 1, 'C');
        $this->SetFont('Arial', '', 8);

        // CLIENTE
        $this->Cell(40, 6, $this->conv('NOMBRE O RAZÓN SOCIAL:'), 1, 0, 'L');
        $this->Cell(85, 6, $this->conv($this->cliNombre . " " . $this->cliApellido), 1, 0, 'L');
        $this->Cell(22, 6, $this->conv('C.I / RUC:'), 1, 0, 'L');
        $this->Cell(43, 6, $this->conv($this->cliDni), 1, 1, 'L');

        // CELULAR
        $this->Cell(15, 6, $this->conv('Celular:'), 1, 0, 'L');
        $this->Cell(60, 6, $this->conv($this->cliCelular ?? ''), 1, 0, 'L');

        // DIRECCIÓN — viene de $_GET['dir'] pasado desde el front
        $this->Cell(20, 6, $this->conv('Dirección:'), 1, 0, 'L');
        $this->Cell(95, 6, $this->conv($this->cliDireccion ?? ''), 1, 1, 'L');

        // CONDICIÓN DE VENTA
        $condicion  = strtoupper($this->condicionVenta ?? '');
        $chkContado = (strpos($condicion, 'CONTADO') !== false) ? '[X]' : '[ ]';
        $chkCredito = (strpos($condicion, 'CREDITO') !== false) ? '[X]' : '[ ]';
        $this->Cell(45, 6, $this->conv('CONDICION DE VENTA:'), 1, 0, 'L');
        $this->SetFont('Arial', 'B', 8);
        $this->Cell(22, 6, $this->conv("$chkContado CONTADO"), 0, 0, 'L');
        $this->Cell(22, 6, $this->conv("$chkCredito CREDITO"), 0, 0, 'L');
        $this->SetFont('Arial', '', 8);
        $this->Cell(101, 6, '', 1, 1, 'L');

        // REMISIÓN
        $this->Cell(36, 6, $this->conv('NOTA REMISIÓN N°:'), 1, 0, 'L');
        $this->Cell(154, 6, '', 1, 1, 'L');

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
        $wModelo = 25;
        $wPU    = 20;
        $wEx    = 15;
        $w5     = 10;
        $w10    = 10;
        $h      = 6;

        // ENCABEZADO
        $this->SetFont('Arial', 'B', 8);
        $this->Cell($wCant,  $h, $this->conv('CANT'),      1, 0, 'C');
        $this->Cell($wNom,   $h, $this->conv('NOMBRE'),    1, 0, 'C');
        $this->Cell($wCat,   $h, $this->conv('CATEGORÍA'), 1, 0, 'C');
        $this->Cell($wMarca, $h, $this->conv('MARCA'),     1, 0, 'C');
        $this->Cell($wModelo,$h, $this->conv('MODELO'),    1, 0, 'C');
        $this->Cell($wPU,    $h, $this->conv('P. UNIT'),   1, 0, 'C');
        $this->Cell($wEx,    $h, $this->conv('EXENTA'),    1, 0, 'C');
        $this->Cell($w5,     $h, $this->conv('5%'),        1, 0, 'C');
        $this->Cell($w10,    $h, $this->conv('10%'),       1, 1, 'C');

        $this->SetFont('Arial', '', 8);

        $total10 = 0;

        foreach ($items as $it) {

            $txtNom   = $this->conv($it['nombre']);
            $txtCat   = $this->conv($it['categoria']);
            $txtMarca = $this->conv($it['marca']);
            $txtMod   = $this->conv($it['modelo']);

            $lineasNom   = $this->NbLines($wNom,    $txtNom);
            $lineasCat   = $this->NbLines($wCat,    $txtCat);
            $lineasMarca = $this->NbLines($wMarca,  $txtMarca);
            $lineasMod   = $this->NbLines($wModelo, $txtMod);

            $maxLineas = max($lineasNom, $lineasCat, $lineasMarca, $lineasMod);
            $hFila     = max(6, $maxLineas * 5);

            // SALTO DE PÁGINA
            if ($this->GetY() + $hFila > 255) {
                $this->AddPage();
                $this->datosCliente();

                $this->SetFont('Arial', 'B', 8);
                $this->Cell($wCant,  $h, 'CANT',      1, 0, 'C');
                $this->Cell($wNom,   $h, 'NOMBRE',    1, 0, 'C');
                $this->Cell($wCat,   $h, 'CATEGORÍA', 1, 0, 'C');
                $this->Cell($wMarca, $h, 'MARCA',     1, 0, 'C');
                $this->Cell($wModelo,$h, 'MODELO',    1, 0, 'C');
                $this->Cell($wPU,    $h, 'P. UNIT',   1, 0, 'C');
                $this->Cell($wEx,    $h, 'EXENTA',    1, 0, 'C');
                $this->Cell($w5,     $h, '5%',        1, 0, 'C');
                $this->Cell($w10,    $h, '10%',       1, 1, 'C');
                $this->SetFont('Arial', '', 8);
            }

            // CANT
            $this->Cell($wCant, $hFila, $it['cant'], 1, 0, 'C');

            // NOMBRE
            $xNom = $this->GetX(); $yNom = $this->GetY();
            $this->MultiCell($wNom, 5, $txtNom, 0, 'L');
            $this->SetXY($xNom + $wNom, $yNom);
            $this->Rect($xNom, $yNom, $wNom, $hFila);

            // CATEGORÍA
            $xCat = $this->GetX(); $yCat = $this->GetY();
            $this->MultiCell($wCat, 5, $txtCat, 0, 'L');
            $this->SetXY($xCat + $wCat, $yCat);
            $this->Rect($xCat, $yCat, $wCat, $hFila);

            // MARCA
            $xMarca = $this->GetX(); $yMarca = $this->GetY();
            $this->MultiCell($wMarca, 5, $txtMarca, 0, 'L');
            $this->SetXY($xMarca + $wMarca, $yMarca);
            $this->Rect($xMarca, $yMarca, $wMarca, $hFila);

            // MODELO
            $xMod = $this->GetX(); $yMod = $this->GetY();
            $this->MultiCell($wModelo, 5, $txtMod, 0, 'L');
            $this->SetXY($xMod + $wModelo, $yMod);
            $this->Rect($xMod, $yMod, $wModelo, $hFila);

            // PRECIOS
            $this->Cell($wPU, $hFila, number_format($it['pu'], 2, ',', '.'), 1, 0, 'R');
            $this->Cell($wEx, $hFila, '0', 1, 0, 'R');
            $this->Cell($w5,  $hFila, '',  1, 0, 'C');
            $this->Cell($w10, $hFila, '',  1, 1, 'C');

            $total10 += ($it['pu'] * $it['cant']);
        }

        // TOTALES
        $this->Ln(2);

        $valorParcial = round($total10 - ($total10 / 11));
        $this->Cell(30, 6, $this->conv('VALOR PARCIAL'), 1, 0, 'L');
        $this->Cell(160, 6, number_format($valorParcial, 2, ',', '.'), 1, 1, 'R');

        $this->Cell(40, 6, $this->conv('TOTAL A PAGAR Gs.'), 1, 0, 'L');
        $this->Cell(150, 6, number_format($total10, 2, ',', '.'), 1, 1, 'R');

        $iva10 = $total10 / 11;

        $this->Cell(50, 6, $this->conv('LIQUIDACIÓN DEL IVA:'), 1, 0, 'L');
        $this->Cell(40, 6, '5%',        1, 0, 'C');
        $this->Cell(40, 6, '10%',       1, 0, 'C');
        $this->Cell(60, 6, 'TOTAL IVA', 1, 1, 'C');

        $this->Cell(50, 6, '', 1, 0);
        $this->Cell(40, 6, '0', 1, 0, 'R');

        $this->SetFont('ZapfDingbats', '', 11);
        $this->Cell(40, 6, "3", 1, 0, 'C');

        $this->SetFont('Arial', '', 8);
        $this->Cell(60, 6, number_format($iva10, 2, ',', '.'), 1, 1, 'R');
    }
}

/* ============================================================
   ===============    CARGA DE LA VENTA    =====================
   ============================================================ */

// 🔥 CORRECCIÓN 1: Leer dirección y número de factura desde GET
$id             = intval($_GET['id']       ?? 0);
$direccion      = trim($_GET['dir']       ?? '');
$nroFactura     = trim($_GET['nro']       ?? '001-001-0000001');
$condicionVenta = trim($_GET['condicion'] ?? 'CONTADO');

if ($id <= 0) die("ID inválido");

$sql = $conexion->prepare("
    SELECT 
        v.*, 
        mp.nombre   AS metodo_pago_nombre,
        mn.codigo   AS moneda_codigo,
        c.nombre    AS cliNombre, 
        c.apellido  AS cliApellido,
        c.dni       AS cliDni, 
        c.celular   AS cliCelular
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
        COALESCE(m.nombre_marca, '-')      AS nombre_marca,
        COALESCE(c.nombre_categoria, '-')  AS nombre_categoria,
        COALESCE(p.modelo, '-')            AS modelo
    FROM detalle_venta d
    JOIN producto p ON p.idProducto = d.producto_idProducto
    LEFT JOIN categoria c ON p.Categoria_idCategoria = c.idCategoria
    LEFT JOIN marcas m    ON p.marcas_idmarcas = m.idmarcas
    WHERE d.ventas_idVenta = ?
");
$sql2->execute([$id]);

$rows  = $sql2->fetchAll(PDO::FETCH_ASSOC);
$items = [];

foreach ($rows as $r) {
    $items[] = [
        'cant'      => (int)$r['cantidad'],
        'nombre'    => str_replace(['–', '—'], '-', $r['nombre']),
        'categoria' => str_replace(['–', '—'], '-', $r['nombre_categoria']),
        'marca'     => str_replace(['–', '—'], '-', $r['nombre_marca']),
        'modelo'    => str_replace(['–', '—'], '-', $r['modelo']),
        'pu'        => (float)$r['precio_unitario'],
    ];
}

/* ============================================================
   ===============    GENERAR PDF    ===========================
   ============================================================ */

$pdf = new FacturaParaguayaPDF('P', 'mm', 'A4');

// 🔥 CORRECCIÓN 2: Asignar TODAS las propiedades ANTES de AddPage()
$pdf->cliNombre   = $venta['cliNombre']   ?? '';
$pdf->cliApellido = $venta['cliApellido'] ?? '';
$pdf->cliDni      = $venta['cliDni']      ?? '';
$pdf->cliCelular  = $venta['cliCelular']  ?? '';
$pdf->cliDireccion  = $direccion;
$pdf->nroFactura    = $nroFactura;
$pdf->condicionVenta = strtoupper($condicionVenta);

$metodo = $venta['metodo_pago_nombre'] ?? '---';
$moneda = $venta['moneda_codigo']      ?? '';
$pdf->metodoPago = strtoupper($metodo);
if ($moneda !== '') {
    $pdf->metodoPago .= " - " . strtoupper($moneda);
}

// ── COPIA CLIENTE ──────────────────────────────────────────
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 10);
$pdf->Ln(2);
$pdf->datosCliente();
$pdf->tablaItems($items);

// ── COPIA EMPLEADOR ────────────────────────────────────────
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(190, 8, $pdf->conv('COPIA PARA EL EMPLEADOR'), 0, 1, 'C');
$pdf->Ln(2);
$pdf->datosCliente();
$pdf->tablaItems($items);

// 🔥 CORRECCIÓN 3: Limpiar buffer correctamente antes de enviar PDF
if (ob_get_level()) {
    ob_end_clean();
}

$pdf->Output('I', "factura_$id.pdf");
exit;