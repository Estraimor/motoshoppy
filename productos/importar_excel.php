<?php
session_start();
require '../vendor/autoload.php';
require_once '../conexion/conexion.php';
require_once '../settings/auditoria.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json');

if (empty($_FILES['excel']['name'])) {
    echo json_encode(['ok' => false, 'msg' => 'No se recibió ningún archivo.']);
    exit;
}

$permitidas = ['xlsx', 'xls'];
$ext = strtolower(pathinfo($_FILES['excel']['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $permitidas)) {
    echo json_encode(['ok' => false, 'msg' => 'El archivo debe ser un Excel (.xlsx o .xls).']);
    exit;
}

try {
    $spreadsheet = IOFactory::load($_FILES['excel']['tmp_name']);
    $sheet = $spreadsheet->getSheetByName('Productos') ?: $spreadsheet->getActiveSheet();
    $filas = $sheet->toArray(null, true, true, false);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => 'No se pudo leer el archivo: ' . $e->getMessage()]);
    exit;
}

// Saco la fila de encabezado
array_shift($filas);

if (!$filas) {
    echo json_encode(['ok' => false, 'msg' => 'El archivo no tiene filas de datos.']);
    exit;
}

/* =========================
   CACHES DE CATEGORÍA / MARCA / UBICACIÓN
========================= */
$cacheCategorias = [];
$cacheMarcas     = [];
$cacheUbicaciones = [];

function buscarOcrearCategoria($conexion, &$cache, $nombre) {
    $nombre = trim($nombre);
    if ($nombre === '') return null;

    $clave = mb_strtolower($nombre);
    if (isset($cache[$clave])) return $cache[$clave];

    $stmt = $conexion->prepare("SELECT idCategoria FROM categoria WHERE LOWER(nombre_categoria) = ?");
    $stmt->execute([$clave]);
    $id = $stmt->fetchColumn();

    if (!$id) {
        $ins = $conexion->prepare("INSERT INTO categoria (nombre_categoria, estado) VALUES (?, 1)");
        $ins->execute([$nombre]);
        $id = $conexion->lastInsertId();
    }

    $cache[$clave] = $id;
    return $id;
}

function buscarOcrearMarca($conexion, &$cache, $nombre, $categoriaId) {
    $nombre = trim($nombre);
    if ($nombre === '' || !$categoriaId) return null;

    $clave = mb_strtolower($nombre) . '|' . $categoriaId;
    if (isset($cache[$clave])) return $cache[$clave];

    $stmt = $conexion->prepare("SELECT idmarcas FROM marcas WHERE LOWER(nombre_marca) = ? AND categoria_idCategoria = ?");
    $stmt->execute([mb_strtolower($nombre), $categoriaId]);
    $id = $stmt->fetchColumn();

    if (!$id) {
        $ins = $conexion->prepare("INSERT INTO marcas (nombre_marca, categoria_idCategoria, estado) VALUES (?, ?, 1)");
        $ins->execute([$nombre, $categoriaId]);
        $id = $conexion->lastInsertId();
    }

    $cache[$clave] = $id;
    return $id;
}

function buscarOcrearUbicacion($conexion, &$cache, $lugar, $estante) {
    $lugar = trim($lugar);
    $estante = trim($estante);
    if ($lugar === '') return null;

    $clave = mb_strtolower($lugar) . '|' . mb_strtolower($estante);
    if (isset($cache[$clave])) return $cache[$clave];

    $stmt = $conexion->prepare("SELECT idubicacion_producto FROM ubicacion_producto WHERE lugar = ? AND estante = ?");
    $stmt->execute([$lugar, $estante]);
    $id = $stmt->fetchColumn();

    if (!$id) {
        $ins = $conexion->prepare("INSERT INTO ubicacion_producto (lugar, estante) VALUES (?, ?)");
        $ins->execute([$lugar, $estante]);
        $id = $conexion->lastInsertId();
    }

    $cache[$clave] = $id;
    return $id;
}

/* =========================
   PROCESAR FILAS
========================= */
$resultado = [];
$creados = 0;

$stmtProducto = $conexion->prepare("
    INSERT INTO producto
        (Categoria_idCategoria, marcas_idmarcas, codigo, nombre, modelo,
         precio_costo, precio_expuesto, peso_ml, ubicacion_producto_idubicacion_producto)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

$stmtStock = $conexion->prepare("
    INSERT INTO stock_producto (producto_idProducto, stock_minimo, cantidad_actual, cantidad_exhibida)
    VALUES (?, ?, ?, ?)
");

foreach ($filas as $i => $fila) {

    $numeroFila = $i + 2; // +1 por índice base 0, +1 por el encabezado ya descartado

    // Fila vacía: la salteamos sin reportar error
    if (!array_filter($fila, fn($v) => trim((string)$v) !== '')) {
        continue;
    }

    $codigo          = trim((string)($fila[0] ?? ''));
    $nombre          = trim((string)($fila[1] ?? ''));
    $nombreCategoria = trim((string)($fila[2] ?? ''));
    $nombreMarca     = trim((string)($fila[3] ?? ''));
    $modelo          = trim((string)($fila[4] ?? ''));
    $precioCosto     = is_numeric($fila[5] ?? null) ? (float)$fila[5] : null;
    $precioExpuesto  = is_numeric($fila[6] ?? null) ? (float)$fila[6] : null;
    $pesoMl          = is_numeric($fila[7] ?? null) ? (int)$fila[7] : null;
    $lugar           = trim((string)($fila[8] ?? ''));
    $estante         = trim((string)($fila[9] ?? ''));
    $stockMinimo     = is_numeric($fila[10] ?? null) ? (int)$fila[10] : 0;
    $cantidadActual  = is_numeric($fila[11] ?? null) ? (int)$fila[11] : 0;
    $cantidadExhib   = is_numeric($fila[12] ?? null) ? (int)$fila[12] : 0;

    if ($nombre === '' || $nombreCategoria === '') {
        $resultado[] = ['fila' => $numeroFila, 'ok' => false, 'msg' => 'Falta Nombre o Categoría'];
        continue;
    }

    try {
        $categoriaId = buscarOcrearCategoria($conexion, $cacheCategorias, $nombreCategoria);
        $marcaId     = $nombreMarca !== '' ? buscarOcrearMarca($conexion, $cacheMarcas, $nombreMarca, $categoriaId) : null;
        $ubicacionId = $lugar !== '' ? buscarOcrearUbicacion($conexion, $cacheUbicaciones, $lugar, $estante) : null;

        $conexion->beginTransaction();

        $stmtProducto->execute([
            $categoriaId, $marcaId, $codigo ?: null, $nombre, $modelo ?: null,
            $precioCosto, $precioExpuesto, $pesoMl, $ubicacionId
        ]);
        $idProducto = $conexion->lastInsertId();

        $stmtStock->execute([$idProducto, $stockMinimo, $cantidadActual, $cantidadExhib]);

        auditoria(
            $conexion,
            'INSERT',
            'productos',
            'producto',
            $idProducto,
            "Creó producto por importación Excel: {$nombre}",
            null,
            [
                'Categoria_idCategoria' => $categoriaId,
                'marcas_idmarcas'       => $marcaId,
                'codigo'                => $codigo,
                'nombre'                => $nombre,
                'modelo'                => $modelo,
                'precio_costo'          => $precioCosto,
                'precio_expuesto'       => $precioExpuesto
            ]
        );

        auditoria(
            $conexion,
            'INSERT',
            'productos',
            'stock_producto',
            $idProducto,
            "Cargó stock inicial por importación Excel: {$nombre}",
            null,
            ['stock_minimo' => $stockMinimo, 'cantidad_actual' => $cantidadActual, 'cantidad_exhibida' => $cantidadExhib]
        );

        $conexion->commit();

        $resultado[] = ['fila' => $numeroFila, 'ok' => true, 'msg' => "Creado: {$nombre}"];
        $creados++;

    } catch (Exception $e) {
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        $resultado[] = ['fila' => $numeroFila, 'ok' => false, 'msg' => 'Error: ' . $e->getMessage()];
    }
}

echo json_encode([
    'ok'       => true,
    'creados'  => $creados,
    'total'    => count($resultado),
    'detalle'  => $resultado
]);
