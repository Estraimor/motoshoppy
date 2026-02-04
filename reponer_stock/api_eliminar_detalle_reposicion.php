<?php
require_once '../conexion/conexion.php';

$idDetalle = $_POST['id_detalle'] ?? null;

if (!$idDetalle) {
    echo json_encode(['ok' => false]);
    exit;
}

/* obtener id del pedido */
$stmt = $conexion->prepare("
    SELECT reposicion_idreposicion
    FROM reposicion_detalle
    WHERE idreposicion_detalle = ?
");
$stmt->execute([$idDetalle]);
$idReposicion = $stmt->fetchColumn();

if (!$idReposicion) {
    echo json_encode(['ok' => false]);
    exit;
}

/* eliminar item */
$del = $conexion->prepare("
    DELETE FROM reposicion_detalle
    WHERE idreposicion_detalle = ?
");
$del->execute([$idDetalle]);

/* verificar si quedan productos */
$check = $conexion->prepare("
    SELECT COUNT(*) 
    FROM reposicion_detalle
    WHERE reposicion_idreposicion = ?
");
$check->execute([$idReposicion]);
$cantidad = (int)$check->fetchColumn();

/* si no quedan → cancelar pedido */
if ($cantidad === 0) {
    $upd = $conexion->prepare("
        UPDATE reposicion
        SET estado = 'cancelado'
        WHERE idreposicion = ?
    ");
    $upd->execute([$idReposicion]);

    echo json_encode([
        'ok' => true,
        'pedido_cancelado' => true
    ]);
    exit;
}

echo json_encode(['ok' => true]);
