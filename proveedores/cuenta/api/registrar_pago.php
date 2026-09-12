<?php
header('Content-Type: application/json');
session_start();
require_once '../../../conexion/conexion.php';
require_once '../../../settings/auditoria.php';

if (empty($_SESSION['idusuario'])) {
    echo json_encode(['ok' => false, 'msg' => 'Sesión expirada.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$id                 = intval($data['id'] ?? 0);
$monto              = (float)($data['monto'] ?? 0);
$fecha_pago         = trim($data['fecha_pago'] ?? '') ?: date('Y-m-d');
$numero_comprobante = trim($data['numero_comprobante'] ?? '') ?: null;

if ($id <= 0 || $monto <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Datos inválidos.']);
    exit;
}

try {
    $conexion->beginTransaction();

    $stmt = $conexion->prepare("SELECT monto, monto_pagado, estado_pago FROM factura_proveedor WHERE idFacturaProveedor = ?");
    $stmt->execute([$id]);
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registro) {
        $conexion->rollBack();
        echo json_encode(['ok' => false, 'msg' => 'Registro no encontrado.']);
        exit;
    }

    $saldo = (float)$registro['monto'] - (float)$registro['monto_pagado'];
    if ($monto > $saldo + 0.01) {
        $conexion->rollBack();
        echo json_encode([
            'ok'  => false,
            'msg' => 'El monto no puede superar el saldo pendiente (₲' . number_format($saldo, 0, ',', '.') . ').'
        ]);
        exit;
    }

    $nuevoPagado = (float)$registro['monto_pagado'] + $monto;
    $nuevoEstado = ($nuevoPagado >= (float)$registro['monto']) ? 'pagado' : 'parcial';

    $upd = $conexion->prepare("
        UPDATE factura_proveedor
        SET monto_pagado = ?, estado_pago = ?
        WHERE idFacturaProveedor = ?
    ");
    $upd->execute([$nuevoPagado, $nuevoEstado, $id]);

    auditoria(
        $conexion,
        'UPDATE',
        'proveedores',
        'factura_proveedor',
        $id,
        "Registró pago a proveedor por " . number_format($monto, 2, ',', '.'),
        [
            'monto_pagado' => (float)$registro['monto_pagado'],
            'estado_pago'  => $registro['estado_pago']
        ],
        [
            'monto_pagado' => $nuevoPagado,
            'estado_pago'  => $nuevoEstado
        ]
    );

    $insPago = $conexion->prepare("
        INSERT INTO pagos_factura_proveedor
            (factura_proveedor_idFacturaProveedor, usuario_idusuario, fecha_pago, monto, numero_comprobante)
        VALUES (?, ?, ?, ?, ?)
    ");
    $insPago->execute([$id, $_SESSION['idusuario'], $fecha_pago, $monto, $numero_comprobante]);
    $idPago = $conexion->lastInsertId();

    auditoria(
        $conexion,
        'INSERT',
        'proveedores',
        'pagos_factura_proveedor',
        $idPago,
        "Registró pago de ₲" . number_format($monto, 0, ',', '.') . " del " . $fecha_pago,
        null,
        [
            'factura_proveedor_idFacturaProveedor' => $id,
            'fecha_pago'                           => $fecha_pago,
            'monto'                                => $monto,
            'numero_comprobante'                   => $numero_comprobante
        ]
    );

    $conexion->commit();

    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    if ($conexion->inTransaction()) {
        $conexion->rollBack();
    }
    echo json_encode(['ok' => false, 'msg' => 'Error al registrar el pago: ' . $e->getMessage()]);
}
