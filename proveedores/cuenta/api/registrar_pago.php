<?php
header('Content-Type: application/json');
session_start();
require_once '../../../conexion/conexion.php';

if (empty($_SESSION['idusuario'])) {
    echo json_encode(['ok' => false, 'msg' => 'Sesión expirada.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$id     = intval($data['id'] ?? 0);
$monto  = (float)($data['monto'] ?? 0);

if ($id <= 0 || $monto <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Datos inválidos.']);
    exit;
}

try {
    $stmt = $conexion->prepare("SELECT monto, monto_pagado FROM factura_proveedor WHERE idFacturaProveedor = ?");
    $stmt->execute([$id]);
    $registro = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$registro) {
        echo json_encode(['ok' => false, 'msg' => 'Registro no encontrado.']);
        exit;
    }

    $saldo = (float)$registro['monto'] - (float)$registro['monto_pagado'];
    if ($monto > $saldo) {
        $monto = $saldo;
    }

    $nuevoPagado = (float)$registro['monto_pagado'] + $monto;
    $nuevoEstado = ($nuevoPagado >= (float)$registro['monto']) ? 'pagado' : 'parcial';

    $upd = $conexion->prepare("
        UPDATE factura_proveedor
        SET monto_pagado = ?, estado_pago = ?
        WHERE idFacturaProveedor = ?
    ");
    $upd->execute([$nuevoPagado, $nuevoEstado, $id]);

    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => 'Error al registrar el pago: ' . $e->getMessage()]);
}
