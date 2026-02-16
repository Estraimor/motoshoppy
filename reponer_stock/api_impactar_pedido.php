<?php
require_once '../conexion/conexion.php';
require_once '../settings/bootstrap.php';

header('Content-Type: application/json');

/* ===============================
   DATOS POST
=============================== */
$id             = $_POST['idreposicion'] ?? null;
$observacion    = $_POST['observacion'] ?? null;
$numero_factura = $_POST['numero_factura'] ?? null;

if (!$id) {
    echo json_encode(['ok' => false, 'error' => 'ID inválido']);
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

$conexion->beginTransaction();

try {

    /* ===============================
       ACTUALIZAR PRECIOS UNITARIOS
    =============================== */
    $precios = json_decode($_POST['precios'] ?? '[]', true);

    if (!$precios || !is_array($precios)) {
        throw new Exception('No se recibieron precios válidos');
    }

    foreach ($precios as $p) {

        if (
            empty($p['id_detalle']) ||
            !isset($p['precio_unitario']) ||
            $p['precio_unitario'] <= 0
        ) {
            throw new Exception('Precio unitario inválido');
        }

        $updPrecio = $conexion->prepare("
            UPDATE reposicion_detalle
            SET precio_unitario = ?
            WHERE idreposicion_detalle = ?
        ");

        $updPrecio->execute([
            floatval($p['precio_unitario']),
            intval($p['id_detalle'])
        ]);
    }

    /* ===============================
       OBTENER TOTAL REAL AUTOMÁTICO
    =============================== */
    $totalStmt = $conexion->prepare("
        SELECT SUM(cantidad * precio_unitario)
        FROM reposicion_detalle
        WHERE reposicion_idreposicion = ?
    ");
    $totalStmt->execute([$id]);
    $costo_total = $totalStmt->fetchColumn() ?: 0;

    if ($costo_total <= 0) {
        throw new Exception('El total del pedido es inválido');
    }

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
       CARPETA REMITOS
    =============================== */
    $carpeta = __DIR__ . "/remitos";
    if (!is_dir($carpeta)) {
        mkdir($carpeta, 0777, true);
    }

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
        'Impactó la reposición',
        $antesRepo,
        $despuesRepo,
        $id,
        'reposicion'
    );

    /* ===============================
       IMPACTAR STOCK
    =============================== */
    $detalles = $conexion->prepare("
        SELECT producto_idProducto, cantidad, precio_unitario
        FROM reposicion_detalle
        WHERE reposicion_idreposicion = ?
    ");
    $detalles->execute([$id]);

    foreach ($detalles as $d) {

        $stockAntesStmt = $conexion->prepare("
            SELECT cantidad_actual
            FROM stock_producto
            WHERE producto_idProducto = ?
        ");
        $stockAntesStmt->execute([$d['producto_idProducto']]);
        $stockAntes = $stockAntesStmt->fetchColumn();

        $updStock = $conexion->prepare("
    UPDATE stock_producto
    SET cantidad_actual = cantidad_actual + ?
    WHERE producto_idProducto = ?
");


        $updStock->execute([
    $d['cantidad'],
    $d['producto_idProducto']
]);

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
