<?php
require_once '../conexion/conexion.php';

header('Content-Type: application/json');

$id = $_POST['idreposicion'] ?? null;
$observacion = $_POST['observacion'] ?? null;

if (!$id) {
    echo json_encode(['ok' => false, 'error' => 'ID inválido']);
    exit;
}

/* ===============================
   CARPETA ÚNICA DE REMITOS
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

    // Nombre único + referencia al pedido
    $archivoNombre = 'remito_' . $id . '_' . date('Ymd_His') . '.' . $ext;

    if (!move_uploaded_file($tmp, $carpeta . '/' . $archivoNombre)) {
        echo json_encode(['ok' => false, 'error' => 'Error al guardar archivo']);
        exit;
    }
}

/* ===============================
   ACTUALIZAR REPOSICIÓN
=============================== */
$sql = "
    UPDATE reposicion
    SET estado = 'impactado',
        fecha_llegada = NOW(),
        imagen_remito = ?,
        observacion = ?
    WHERE idreposicion = ?
";
$stmt = $conexion->prepare($sql);
$stmt->execute([$archivoNombre, $observacion, $id]);

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
    $upd = $conexion->prepare("
        UPDATE stock_producto
        SET cantidad_actual = cantidad_actual + ?
        WHERE producto_idProducto = ?
    ");
    $upd->execute([
        $d['cantidad'],
        $d['producto_idProducto']
    ]);
}

echo json_encode(['ok' => true]);
