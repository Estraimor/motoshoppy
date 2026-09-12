<?php
header('Content-Type: application/json');
session_start();
require_once '../../../conexion/conexion.php';

if (empty($_SESSION['idusuario'])) {
    echo json_encode(['ok' => false, 'msg' => 'Sesión expirada.']);
    exit;
}

$facturaId = intval($_GET['factura_id'] ?? 0);

if ($facturaId <= 0) {
    echo json_encode(['ok' => false, 'msg' => 'ID inválido.']);
    exit;
}

$stmt = $conexion->prepare("
    SELECT
        p.fecha_pago,
        p.monto,
        p.numero_comprobante,
        u.nombre,
        u.apellido
    FROM pagos_factura_proveedor p
    LEFT JOIN usuario u ON u.idusuario = p.usuario_idusuario
    WHERE p.factura_proveedor_idFacturaProveedor = ?
    ORDER BY p.fecha_pago DESC, p.idpago DESC
");
$stmt->execute([$facturaId]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pagos = array_map(function ($r) {
    return [
        'fecha_pago'         => date('d/m/Y', strtotime($r['fecha_pago'])),
        'monto'              => (float)$r['monto'],
        'numero_comprobante' => $r['numero_comprobante'],
        'usuario'            => trim(($r['nombre'] ?? '') . ' ' . ($r['apellido'] ?? '')) ?: null
    ];
}, $rows);

echo json_encode(['ok' => true, 'pagos' => $pagos]);
