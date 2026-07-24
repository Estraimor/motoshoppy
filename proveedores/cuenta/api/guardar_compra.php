<?php
header('Content-Type: application/json');
session_start();
require_once '../../../conexion/conexion.php';

if (empty($_SESSION['idusuario'])) {
    echo json_encode(['ok' => false, 'msg' => 'Sesión expirada.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

$proveedorId    = intval($data['proveedor_id'] ?? 0);
$fechaCompra    = trim($data['fecha_compra'] ?? '');
$monto          = (float)($data['monto'] ?? 0);
$descripcion    = trim($data['descripcion'] ?? '');
$numeroFactura  = trim($data['numero_factura'] ?? '');
$tieneFactura   = !empty($data['tiene_factura']) ? 1 : 0;

if ($proveedorId <= 0 || $fechaCompra === '' || $monto <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'Datos inválidos.']);
    exit;
}

try {
    $stmt = $conexion->prepare("
        INSERT INTO factura_proveedor (
            proveedores_idproveedores, fecha_compra, descripcion,
            monto, numero_factura, tiene_factura, estado_pago, monto_pagado
        ) VALUES (?, ?, ?, ?, ?, ?, 'pendiente', 0)
    ");
    $stmt->execute([
        $proveedorId,
        $fechaCompra,
        $descripcion !== '' ? $descripcion : null,
        $monto,
        $numeroFactura !== '' ? $numeroFactura : null,
        $tieneFactura
    ]);

    echo json_encode(['ok' => true]);
} catch (Exception $e) {
    echo json_encode(['ok' => false, 'msg' => 'Error al guardar: ' . $e->getMessage()]);
}
