<?php
require_once '../conexion/conexion.php';

header('Content-Type: application/json');

/* ===============================
   DATOS POST
=============================== */
$id             = $_POST['idreposicion'] ?? null;
$observacion    = $_POST['observacion'] ?? null;
$costo_total    = $_POST['costo_total'] ?? null;
$numero_factura = $_POST['numero_factura'] ?? null;

/* ===============================
   VALIDACIONES BÁSICAS
=============================== */
if (!$id) {
    echo json_encode(['ok' => false, 'error' => 'ID inválido']);
    exit;
}

if ($costo_total !== null && !is_numeric($costo_total)) {
    echo json_encode(['ok' => false, 'error' => 'Costo inválido']);
    exit;
}

if (!$numero_factura) {
    echo json_encode(['ok' => false, 'error' => 'Falta número de factura']);
    exit;
}

/* ===============================
   VERIFICAR ESTADO ACTUAL
=============================== */
$estadoActual = $conexion->prepare("
    SELECT estado
    FROM reposicion
    WHERE idreposicion = ?
");
$estadoActual->execute([$id]);

if ($estadoActual->fetchColumn() !== 'pedido') {
    echo json_encode(['ok' => false, 'error' => 'El pedido no puede impactarse']);
    exit;
}

/* ===============================
   CARPETA DE REMITOS
=============================== */
$carpeta = __DIR__ . "/remitos";
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

/* ===============================
   SUBIR ARCHIVO (PDF / JPG / PNG)
=============================== */
$archivoNombre = null;

if (!empty($_FILES['remito']['name'])) {

    $permitidos = [
        'application/pdf' => 'pdf',
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png'
    ];

    $tmp = $_FILES['remito']['tmp_name'];

    if (!is_uploaded_file($tmp)) {
        echo json_encode(['ok' => false, 'error' => 'Archivo inválido']);
        exit;
    }

    $mime = mime_content_type($tmp);

    if (!isset($permitidos[$mime])) {
        echo json_encode([
            'ok' => false,
            'error' => 'Formato no permitido. Solo PDF, JPG o PNG'
        ]);
        exit;
    }

    $ext = $permitidos[$mime];

    $archivoNombre = 'remito_' . $id . '_' . date('Ymd_His') . '.' . $ext;

    if (!move_uploaded_file($tmp, $carpeta . '/' . $archivoNombre)) {
        echo json_encode(['ok' => false, 'error' => 'Error al guardar archivo']);
        exit;
    }
}

/* ===============================
   ACTUALIZAR REPOSICIÓN
=============================== */
$updRepo = $conexion->prepare("
    UPDATE reposicion
    SET
        estado = 'impactado',
        fecha_llegada = NOW(),
        imagen_remito = ?,
        observacion = ?,
        costo_total = ?,
        numero_factura = ?
    WHERE idreposicion = ?
");

$updRepo->execute([
    $archivoNombre,
    $observacion,
    $costo_total,
    $numero_factura,
    $id
]);

/* ===============================
   IMPACTAR STOCK
=============================== */
$detalles = $conexion->prepare("
    SELECT producto_idProducto, cantidad
    FROM reposicion_detalle
    WHERE reposicion_idreposicion = ?
");
$detalles->execute([$id]);

foreach ($detalles as $d) {
    $updStock = $conexion->prepare("
        UPDATE stock_producto
        SET cantidad_actual = cantidad_actual + ?
        WHERE producto_idProducto = ?
    ");
    $updStock->execute([
        $d['cantidad'],
        $d['producto_idProducto']
    ]);
}

echo json_encode(['ok' => true]);
