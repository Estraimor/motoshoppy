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

$stmt = $conexion->query("
    SELECT m.idmarcas, m.nombre_marca, m.estado,
           c.nombre_categoria,
           (SELECT COUNT(*) FROM producto p WHERE p.marcas_idmarcas = m.idmarcas) AS cant_productos
    FROM marcas m
    LEFT JOIN categoria c ON m.categoria_idCategoria = c.idCategoria
    ORDER BY m.idmarcas DESC
");
$marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =============================
// CREAR EXCEL
// =============================

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Marcas');

// =============================
// TÍTULO
// =============================

$sheet->setCellValue('A1', 'Motoshoppy — Marcas');
$sheet->mergeCells('A1:E1');
$sheet->getStyle('A1')->applyFromArray([
    'font'      => ['bold' => true, 'size' => 16, 'color' => ['rgb' => '1E3A5F']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
]);
$sheet->getRowDimension(1)->setRowHeight(24);

$sheet->setCellValue('A2', 'Generado: ' . date('d/m/Y H:i'));
$sheet->mergeCells('A2:E2');
$sheet->getStyle('A2')->applyFromArray([
    'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '666666']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
]);

// =============================
// ENCABEZADOS
// =============================

$headers = ['ID', 'Categoría', 'Marca', 'Productos', 'Estado'];
$sheet->fromArray($headers, NULL, 'A4');

$sheet->getStyle('A4:E4')->applyFromArray([
    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A5F']],
]);
$sheet->getRowDimension(4)->setRowHeight(20);

// =============================
// CARGAR DATOS
// =============================

$fila = 5;

foreach ($marcas as $m) {
    $sheet->setCellValue("A$fila", $m['idmarcas']);
    $sheet->setCellValue("B$fila", $m['nombre_categoria'] ?? 'Sin categoría');
    $sheet->setCellValue("C$fila", $m['nombre_marca']);
    $sheet->setCellValue("D$fila", (int)$m['cant_productos']);
    $sheet->setCellValue("E$fila", $m['estado'] ? 'Activo' : 'Inactivo');

    // Franjas alternadas
    if ($fila % 2 == 0) {
        $sheet->getStyle("A$fila:D$fila")->applyFromArray([
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEF2F7']],
        ]);
    }

    // Badge de estado
    $estadoFondo = $m['estado'] ? 'C6EFCE' : 'FFC7CE';
    $estadoTexto = $m['estado'] ? '2E7D32' : '9C0006';
    $sheet->getStyle("E$fila")->applyFromArray([
        'font'      => ['bold' => true, 'color' => ['rgb' => $estadoTexto]],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $estadoFondo]],
    ]);

    $sheet->getStyle("A$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("D$fila")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    $fila++;
}

// =============================
// BORDES
// =============================

$sheet->getStyle("A4:E" . ($fila - 1))->applyFromArray([
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color'       => ['rgb' => 'CCCCCC'],
        ],
    ],
]);

// =============================
// AUTO SIZE COLUMNAS
// =============================

foreach (range('A', 'E') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// =============================
// FIJAR ENCABEZADO
// =============================

$sheet->freezePane('A5');

// =============================
// DESCARGA
// =============================

$filename = "Marcas_Motoshoppy_" . date('Y-m-d_H-i-s') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
