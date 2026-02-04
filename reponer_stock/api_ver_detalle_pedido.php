<?php
require_once '../conexion/conexion.php';

header('Content-Type: application/json');

$id = $_GET['id'] ?? null;
if (!$id) {
    echo json_encode(['ok' => false, 'error' => 'ID inválido']);
    exit;
}

/* =========================
   PEDIDO (CABECERA)
========================= */
$pedido = $conexion->prepare("
    SELECT 
    r.estado,
    r.fecha_pedido,
    r.fecha_llegada,
    r.costo_total,
    r.numero_factura,
    r.imagen_remito,
    r.observacion,
    p.empresa,
    p.vendedor,
    p.numero_vendedor
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
   DETALLE (PRODUCTOS + MARCA + IMAGEN)
========================= */
$detalle = $conexion->prepare("
    SELECT
        d.idreposicion_detalle,
        pr.nombre AS producto,
        m.nombre_marca AS marca,
        pr.imagen AS imagen,          -- ✅ NUEVO
        d.cantidad,
        d.codigo_proveedor
    FROM reposicion_detalle d
    JOIN producto pr 
        ON pr.idProducto = d.producto_idProducto
    LEFT JOIN marcas m
        ON m.idmarcas = pr.marcas_idmarcas
    WHERE d.reposicion_idreposicion = ?
");
$detalle->execute([$id]);

echo json_encode([
    'ok'              => true,
    'estado'          => $p['estado'],
    'proveedor'       => $p['empresa'],
    'fecha_pedido'    => date('d/m/Y H:i', strtotime($p['fecha_pedido'])),
    'fecha_llegada'   => $p['fecha_llegada']
        ? date('d/m/Y H:i', strtotime($p['fecha_llegada']))
        : null,
    'costo_total'     => $p['costo_total'],
    'numero_factura'  => $p['numero_factura'],
    'remito'          => $p['imagen_remito'],
    'vendedor'        => $p['vendedor'],
'numero_vendedor' => $p['numero_vendedor'],
    'productos'       => $detalle->fetchAll(PDO::FETCH_ASSOC)
]);
