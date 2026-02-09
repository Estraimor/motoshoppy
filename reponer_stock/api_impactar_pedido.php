<?php
require_once '../conexion/conexion.php';
require_once '../settings/bootstrap.php'; // auditoria + helpers

session_start();
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
$estadoStmt = $conexion->prepare("
    SELECT estado
    FROM reposicion
    WHERE idreposicion = ?
");
$estadoStmt->execute([$id]);
$estadoActual = $estadoStmt->fetchColumn();

if ($estadoActual !== 'pedido') {
    echo json_encode(['ok' => false, 'error' => 'El pedido no puede impactarse']);
    exit;
}

/* ===============================
   TRANSACCIÓN
=============================== */
$conexion->beginTransaction();

try {

    /* ===============================
       ESTADO ANTES (AUDITORÍA)
    =============================== */
    $antesRepoStmt = $conexion->prepare("
        SELECT *
        FROM reposicion
        WHERE idreposicion = ?
    ");
    $antesRepoStmt->execute([$id]);
    $antesRepo = $antesRepoStmt->fetch(PDO::FETCH_ASSOC);

    /* ===============================
       CARPETA DE REMITOS
    =============================== */
    $carpeta = __DIR__ . "/remitos";
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

    /* ===============================
       SUBIR ARCHIVO
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
            throw new Exception('Archivo inválido');
        }

        $mime = mime_content_type($tmp);

        if (!isset($permitidos[$mime])) {
            throw new Exception('Formato no permitido');
        }

        $ext = $permitidos[$mime];
        $archivoNombre = 'remito_' . $id . '_' . date('Ymd_His') . '.' . $ext;

        if (!move_uploaded_file($tmp, $carpeta . '/' . $archivoNombre)) {
            throw new Exception('Error al guardar archivo');
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
       AUDITORÍA REPOSICIÓN
    =============================== */
    $despuesRepo = [
        'estado'          => 'impactado',
        'observacion'     => $observacion,
        'costo_total'     => $costo_total,
        'numero_factura'  => $numero_factura,
        'imagen_remito'   => $archivoNombre
    ];

    auditoria(
        $conexion,
        'UPDATE',
        'reposiciones',
        'reposicion',
        $id,
        'Impactó la reposición y actualizó datos',
        $antesRepo,
        $despuesRepo,
        $id,
        'reposicion'
    );

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

        /* stock antes */
        $stockAntesStmt = $conexion->prepare("
            SELECT cantidad_actual
            FROM stock_producto
            WHERE producto_idProducto = ?
        ");
        $stockAntesStmt->execute([$d['producto_idProducto']]);
        $stockAntes = $stockAntesStmt->fetchColumn();

        /* update stock */
        $updStock = $conexion->prepare("
            UPDATE stock_producto
            SET cantidad_actual = cantidad_actual + ?
            WHERE producto_idProducto = ?
        ");
        $updStock->execute([
            $d['cantidad'],
            $d['producto_idProducto']
        ]);

        /* auditoría stock */
        auditoria(
            $conexion,
            'UPDATE',
            'stock',
            'stock_producto',
            $d['producto_idProducto'],
            'Impactó stock por reposición',
            ['cantidad_actual' => $stockAntes],
            ['cantidad_actual' => $stockAntes + $d['cantidad']],
            $id,
            'reposicion'
        );
    }

    $conexion->commit();

    echo json_encode(['ok' => true]);

} catch (Exception $e) {

    $conexion->rollBack();

    echo json_encode([
        'ok' => false,
        'error' => $e->getMessage()
    ]);
}
