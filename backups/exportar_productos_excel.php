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
// CREAR EXCEL
// =============================

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Productos');

// =============================
// ENCABEZADOS
// =============================

$headers = [
    'Producto',
    'Código',
    'Categoría',
    'Marca',
    'Modelo',
    'Peso ML',
    'Peso G',
    'Descripción',
    'Precio Venta',
    'Ubicación'
];

$sheet->fromArray($headers, NULL, 'A1');

// =============================
// ESTILO ENCABEZADO
// =============================

$sheet->getStyle('A1:J1')->applyFromArray([
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF']
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '212529']
    ]
]);

// =============================
// CARGAR DATOS
// =============================

$fila = 2;

foreach ($productos as $p) {

    // limpiar json descripcion
    $descripcion = '';
    if($p['descripcion']){
        $desc = json_decode($p['descripcion'], true);
        if(is_array($desc)){
            foreach($desc as $k => $v){
                $descripcion .= ucfirst($k) . ": " . $v . " | ";
            }
        } else {
            $descripcion = $p['descripcion'];
        }
    }

    $sheet->setCellValue("A$fila", $p['nombre']);
    $sheet->setCellValue("B$fila", $p['codigo']);
    $sheet->setCellValue("C$fila", $p['nombre_categoria']);
    $sheet->setCellValue("D$fila", $p['nombre_marca']);
    $sheet->setCellValue("E$fila", $p['modelo']);
    $sheet->setCellValue("F$fila", $p['peso_ml']);
    $sheet->setCellValue("G$fila", $p['peso_g']);
    $sheet->setCellValue("H$fila", $descripcion);
    $sheet->setCellValue("I$fila", $p['precio_expuesto']);
    $sheet->setCellValue("J$fila", $p['ubicacion']);

    $fila++;
}

// =============================
// FORMATO MONEDA (solo columna I)
// =============================

$sheet->getStyle("I2:I" . ($fila-1))
      ->getNumberFormat()
      ->setFormatCode('"$"#,##0.00');

// =============================
// AUTO SIZE COLUMNAS
// =============================

foreach(range('A','J') as $col){
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// =============================
// BORDES
// =============================

$sheet->getStyle("A1:J" . ($fila-1))
      ->applyFromArray([
          'borders' => [
              'allBorders' => [
                  'borderStyle' => Border::BORDER_THIN
              ]
          ]
      ]);

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