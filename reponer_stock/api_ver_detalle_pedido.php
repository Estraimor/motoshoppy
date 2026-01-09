<?php
require_once '../conexion/conexion.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['ok' => false, 'error' => 'ID inválido']);
    exit;
}

/* =========================
   PEDIDO
========================= */
$pedido = $conexion->prepare("
    SELECT 
        r.fecha_pedido,
        r.fecha_llegada,
        r.costo_total,
        r.imagen_remito,
        p.empresa
    FROM reposicion r
    JOIN proveedores p 
        ON p.idproveedores = r.proveedores_idproveedores
    WHERE r.idreposicion = ?
");
$pedido->execute([$id]);
$p = $pedido->fetch(PDO::FETCH_ASSOC);

if (!$p) {
    echo json_encode(['ok' => false, 'error' => 'Pedido no encontrado']);
    exit;
}

/* =========================
   DETALLE
========================= */
$detalle = $conexion->prepare("
    SELECT pr.nombre, d.cantidad
    FROM reposicion_detalle d
    JOIN producto pr 
        ON pr.idProducto = d.producto_idProducto
    WHERE d.reposicion_idreposicion = ?
");
$detalle->execute([$id]);

echo json_encode([
    'ok' => true,
    'proveedor'     => $p['empresa'],
    'fecha_pedido'  => date('d/m/Y H:i', strtotime($p['fecha_pedido'])),
    'fecha_llegada' => $p['fecha_llegada']
        ? date('d/m/Y H:i', strtotime($p['fecha_llegada']))
        : null,
    'costo_total'   => $p['costo_total'],
    'remito'        => $p['imagen_remito'],
    'productos'     => $detalle->fetchAll(PDO::FETCH_ASSOC)
]);
