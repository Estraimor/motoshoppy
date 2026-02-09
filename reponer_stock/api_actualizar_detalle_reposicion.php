<?php
require_once '../settings/bootstrap.php';
header('Content-Type: application/json');

$idDetalle       = $_POST['id_detalle'] ?? null;
$cantidad        = $_POST['cantidad'] ?? null;
$codigoProveedor = $_POST['codigo_proveedor'] ?? null;

/* =========================
   VALIDACIONES BÁSICAS
========================= */
if (
    !$idDetalle ||
    !is_numeric($cantidad) ||
    $cantidad <= 0
) {
    echo json_encode(['ok' => false, 'error' => 'Datos inválidos']);
    exit;
}

/* =========================
   VALIDAR ESTADO DEL PEDIDO
========================= */
$stmt = $conexion->prepare("
    SELECT r.estado
    FROM reposicion_detalle rd
    JOIN reposicion r 
        ON r.idreposicion = rd.reposicion_idreposicion
    WHERE rd.idreposicion_detalle = ?
");
$stmt->execute([$idDetalle]);
$estado = $stmt->fetchColumn();

if ($estado !== 'pedido') {
    echo json_encode([
        'ok' => false,
        'error' => 'El pedido ya fue impactado y no puede editarse'
    ]);
    exit;
}

/* =========================
   OBTENER ESTADO ANTES (AUDITORÍA)
========================= */
$antesStmt = $conexion->prepare("
    SELECT 
        rd.idreposicion_detalle,
        rd.cantidad,
        rd.codigo_proveedor,
        r.idreposicion
    FROM reposicion_detalle rd
    JOIN reposicion r 
        ON r.idreposicion = rd.reposicion_idreposicion
    WHERE rd.idreposicion_detalle = ?
");
$antesStmt->execute([$idDetalle]);
$antes = $antesStmt->fetch(PDO::FETCH_ASSOC);

if (!$antes) {
    echo json_encode(['ok' => false, 'error' => 'Detalle no encontrado']);
    exit;
}

/* =========================
   ACTUALIZAR DETALLE
========================= */
$upd = $conexion->prepare("
    UPDATE reposicion_detalle
    SET 
        cantidad = ?,
        codigo_proveedor = ?
    WHERE idreposicion_detalle = ?
");

$upd->execute([
    $cantidad,
    $codigoProveedor !== '' ? trim($codigoProveedor) : null,
    $idDetalle
]);

/* =========================
   AUDITORÍA
========================= */
$despues = [
    'cantidad' => (int)$cantidad,
    'codigo_proveedor' => $codigoProveedor !== '' ? trim($codigoProveedor) : null
];

auditoria(
    $conexion,
    'UPDATE',
    'reposiciones',
    'reposicion_detalle',
    $idDetalle,
    'Modificó cantidad / código de proveedor en reposición',
    $antes,
    $despues,
    $antes['idreposicion'],   // 👈 A QUIÉN AFECTÓ
    'reposicion'              // 👈 TABLA AFECTADA
);

/* =========================
   RESPUESTA
========================= */
echo json_encode(['ok' => true]);
