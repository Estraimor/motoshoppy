<?php
require_once '../conexion/conexion.php';

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['ok' => false]);
    exit;
}

// Pedido
$pedido = $conexion->prepare("
    SELECT r.fecha_pedido, r.fecha_llegada, p.empresa
    FROM reposicion r
    JOIN proveedores p ON p.idproveedores = r.proveedores_idproveedores
    WHERE r.idreposicion = ?
");
$pedido->execute([$id]);
$p = $pedido->fetch(PDO::FETCH_ASSOC);

// Detalle
$detalle = $conexion->prepare("
    SELECT pr.nombre, d.cantidad
    FROM reposicion_detalle d
    JOIN producto pr ON pr.idProducto = d.producto_idProducto
    WHERE d.reposicion_idreposicion = ?
");
$detalle->execute([$id]);

echo json_encode([
    'ok' => true,
    'proveedor' => $p['empresa'],
    'fecha_pedido' => date('d/m/Y H:i', strtotime($p['fecha_pedido'])),
    'fecha_llegada' => $p['fecha_llegada']
        ? date('d/m/Y H:i', strtotime($p['fecha_llegada']))
        : null,
    'productos' => $detalle->fetchAll(PDO::FETCH_ASSOC)
]);
