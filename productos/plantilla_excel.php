<?php
require '../vendor/autoload.php';
require_once '../conexion/conexion.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

$categorias = $conexion->query("SELECT nombre_categoria FROM categoria WHERE estado = 1 ORDER BY nombre_categoria")
    ->fetchAll(PDO::FETCH_COLUMN);
$marcas = $conexion->query("SELECT DISTINCT nombre_marca FROM marcas WHERE estado = 1 ORDER BY nombre_marca")
    ->fetchAll(PDO::FETCH_COLUMN);

$headers = [
    'Codigo', 'Nombre', 'Categoria', 'Marca', 'Modelo',
    'Precio Costo', 'Precio Expuesto', 'Peso (ml)',
    'Lugar', 'Estante', 'Stock minimo', 'Cantidad deposito', 'Cantidad exhibida'
];

$ejemplo = [
    'C-001', 'Casco Integral XR', 'Cascos', 'LS2', 'XR-500',
    150000, 250000, '',
    'Depósito A', 'Estante 3', 2, 10, 3
];

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Productos');

$sheet->fromArray($headers, NULL, 'A1');
$sheet->fromArray($ejemplo, NULL, 'A2');

$sheet->getStyle('A1:M1')->applyFromArray([
    'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '212529']],
]);

foreach (range('A', 'M') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

/* =========================
   HOJA "Categorias" (editable)
   Lista las categorías existentes; se pueden agregar nuevas
   al final y van a aparecer solas en el desplegable de Productos.
========================= */
$hojaCat = $spreadsheet->createSheet();
$hojaCat->setTitle('Categorias');
$hojaCat->fromArray(['Categoria'], NULL, 'A1');
if ($categorias) {
    $hojaCat->fromArray(array_map(fn($v) => [$v], $categorias), NULL, 'A2');
}
$hojaCat->getStyle('A1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '212529']],
]);
$hojaCat->getColumnDimension('A')->setWidth(30);

/* =========================
   HOJA "Marcas" (editable)
   Igual que Categorias, pero para marcas.
========================= */
$hojaMarca = $spreadsheet->createSheet();
$hojaMarca->setTitle('Marcas');
$hojaMarca->fromArray(['Marca'], NULL, 'A1');
if ($marcas) {
    $hojaMarca->fromArray(array_map(fn($v) => [$v], $marcas), NULL, 'A2');
}
$hojaMarca->getStyle('A1')->applyFromArray([
    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '212529']],
]);
$hojaMarca->getColumnDimension('A')->setWidth(30);

/* =========================
   DESPLEGABLES EN "Categoria" (col C) Y "Marca" (col D) DE PRODUCTOS
   Apuntan directo a las hojas Categorias / Marcas: si agregás una fila
   nueva ahí (hasta la fila 1000), aparece sola en el desplegable.
   No bloquean texto libre: lo que no exista se crea solo al importar.
========================= */
for ($fila = 2; $fila <= 500; $fila++) {

    $validCat = $sheet->getCell('C' . $fila)->getDataValidation();
    $validCat->setType(DataValidation::TYPE_LIST);
    $validCat->setErrorStyle(DataValidation::STYLE_INFORMATION);
    $validCat->setAllowBlank(true);
    $validCat->setShowDropDown(true);
    $validCat->setShowErrorMessage(false);
    $validCat->setShowInputMessage(true);
    $validCat->setPromptTitle('Categoría');
    $validCat->setPrompt('Elegí una de la hoja "Categorias" o escribí una nueva ahí.');
    $validCat->setFormula1('Categorias!$A$2:$A$1000');

    $validMarca = $sheet->getCell('D' . $fila)->getDataValidation();
    $validMarca->setType(DataValidation::TYPE_LIST);
    $validMarca->setErrorStyle(DataValidation::STYLE_INFORMATION);
    $validMarca->setAllowBlank(true);
    $validMarca->setShowDropDown(true);
    $validMarca->setShowErrorMessage(false);
    $validMarca->setShowInputMessage(true);
    $validMarca->setPromptTitle('Marca');
    $validMarca->setPrompt('Elegí una de la hoja "Marcas" o escribí una nueva ahí.');
    $validMarca->setFormula1('Marcas!$A$2:$A$1000');
}

$notas = $spreadsheet->createSheet();
$notas->setTitle('Instrucciones');
$notas->fromArray([
    ['Instrucciones para completar la plantilla'],
    [''],
    ['- No cambie ni elimine los encabezados de la hoja "Productos".'],
    ['- Nombre y Categoria son obligatorios en cada fila.'],
    ['- Las columnas Categoria y Marca de "Productos" tienen un desplegable que lista lo que ya existe.'],
    ['- ¿Necesitás una categoría o marca nueva? Escribila al final de la lista en la hoja "Categorias" o'],
    ['  "Marcas" (debajo de las que ya están). Va a aparecer automáticamente en el desplegable de Productos.'],
    ['- También podés escribirla directo en la celda de Productos sin pasar por esas hojas: se crea igual.'],
    ['- Lugar / Estante son opcionales: si se completan, se crea la ubicación si no existe.'],
    ['- Los campos numéricos vacíos se guardan como 0 o vacío según corresponda.'],
    ['- Borre la fila de ejemplo antes de importar sus productos reales.'],
], NULL, 'A1');
$notas->getStyle('A1')->getFont()->setBold(true)->setSize(13);
$notas->getColumnDimension('A')->setWidth(90);

$spreadsheet->setActiveSheetIndex(0);

$filename = 'Plantilla_Productos_MotoShoppy.xlsx';

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
