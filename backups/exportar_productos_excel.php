<?php
require '../vendor/autoload.php';
require '../conexion/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// =============================
// CONSULTA
// =============================

$sql = "
SELECT
    p.nombre,
    p.codigo,
    c.nombre_categoria,
    m.nombre_marca,
    p.modelo,
    p.peso_ml,
    p.peso_g,
    p.descripcion,
    p.precio_expuesto,
    CONCAT(
        u.lugar,
        IF(
            u.estante IS NOT NULL
            AND u.estante != '',
            CONCAT(' - Estante ', u.estante),
            ''
        )
    ) AS ubicacion
FROM producto p
LEFT JOIN categoria c
       ON p.Categoria_idCategoria = c.idCategoria
LEFT JOIN marcas m
       ON p.marcas_idmarcas = m.idmarcas
LEFT JOIN ubicacion_producto u
       ON p.ubicacion_producto_idubicacion_producto = u.idubicacion_producto
ORDER BY c.nombre_categoria, p.nombre
";

$stmt = $conexion->prepare($sql);
$stmt->execute();
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =============================
// AGRUPAR POR CATEGORÍA
// =============================

$porCategoria = [];
foreach ($productos as $p) {
    $cat = $p['nombre_categoria'] ?: 'Sin categoría';
    $porCategoria[$cat][] = $p;
}

// =============================
// HELPER: nombre de hoja válido y único
// =============================

function sheetTitleSeguro(string $nombre, array &$usados): string {
    $limpio = preg_replace('/[\\\\\/\?\*\[\]:]/', '-', $nombre);
    $limpio = trim($limpio) !== '' ? trim($limpio) : 'Sin categoria';
    $limpio = mb_substr($limpio, 0, 31);

    $base = $limpio;
    $i = 2;
    while (in_array($limpio, $usados, true)) {
        $sufijo = ' (' . $i . ')';
        $limpio = mb_substr($base, 0, 31 - mb_strlen($sufijo)) . $sufijo;
        $i++;
    }
    $usados[] = $limpio;
    return $limpio;
}

// =============================
// HELPER: limpiar descripción (JSON -> texto)
// =============================

function descripcionTexto($descripcionRaw): string {
    if (empty($descripcionRaw)) {
        return '';
    }

    $desc = json_decode($descripcionRaw, true);

    if (!is_array($desc)) {
        return $descripcionRaw;
    }

    $texto = '';
    foreach ($desc as $k => $v) {
        if (is_array($v)) {
            $v = implode(', ', $v);
        }
        $texto .= ucfirst($k) . ": " . $v . " | ";
    }
    return $texto;
}

// =============================
// CREAR EXCEL (UNA HOJA POR CATEGORÍA)
// =============================

$spreadsheet = new Spreadsheet();
$headers = [
    'Producto', 'Código', 'Marca', 'Modelo',
    'Peso ML', 'Peso G', 'Descripción', 'Precio Venta', 'Ubicación'
];

$nombresUsados = [];
$indiceHoja = 0;

foreach ($porCategoria as $categoria => $items) {

    $sheet = ($indiceHoja === 0)
        ? $spreadsheet->getActiveSheet()
        : $spreadsheet->createSheet();

    $sheet->setTitle(sheetTitleSeguro($categoria, $nombresUsados));

    // Encabezados
    $sheet->fromArray($headers, NULL, 'A1');
    $sheet->getStyle('A1:I1')->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '212529']],
    ]);

    // Datos
    $fila = 2;
    foreach ($items as $p) {
        $sheet->setCellValue("A$fila", $p['nombre']);
        $sheet->setCellValue("B$fila", $p['codigo']);
        $sheet->setCellValue("C$fila", $p['nombre_marca']);
        $sheet->setCellValue("D$fila", $p['modelo']);
        $sheet->setCellValue("E$fila", $p['peso_ml']);
        $sheet->setCellValue("F$fila", $p['peso_g']);
        $sheet->setCellValue("G$fila", descripcionTexto($p['descripcion']));
        $sheet->setCellValue("H$fila", $p['precio_expuesto']);
        $sheet->setCellValue("I$fila", $p['ubicacion']);
        $fila++;
    }

    // Formato moneda (columna H)
    $sheet->getStyle("H2:H" . ($fila - 1))
          ->getNumberFormat()
          ->setFormatCode('"$"#,##0');

    // Auto size columnas
    foreach (range('A', 'I') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }

    // Bordes
    $sheet->getStyle("A1:I" . ($fila - 1))->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    ]);

    $indiceHoja++;
}

$spreadsheet->setActiveSheetIndex(0);

// =============================
// DESCARGA
// =============================

$filename = "Reporte_Productos_MotoShoppy_" . date('Y-m-d_H-i-s') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
