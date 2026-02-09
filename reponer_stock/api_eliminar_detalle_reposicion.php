<?php
require_once '../settings/bootstrap.php'; // incluye auditoria + helpers

header('Content-Type: application/json');

$idDetalle = $_POST['id_detalle'] ?? null;

if (!$idDetalle) {
    echo json_encode(['ok' => false, 'error' => 'ID inválido']);
    exit;
}

/* =========================
   OBTENER DETALLE ANTES
========================= */
$antesStmt = $conexion->prepare("
    SELECT 
        rd.idreposicion_detalle,
        rd.cantidad,
        rd.codigo_proveedor,
        rd.reposicion_idreposicion
    FROM reposicion_detalle rd
    WHERE rd.idreposicion_detalle = ?
");
$antesStmt->execute([$idDetalle]);
$antesDetalle = $antesStmt->fetch(PDO::FETCH_ASSOC);

if (!$antesDetalle) {
    echo json_encode(['ok' => false, 'error' => 'Detalle no encontrado']);
    exit;
}

$idReposicion = $antesDetalle['reposicion_idreposicion'];

/* =========================
   ELIMINAR ÍTEM
========================= */
$del = $conexion->prepare("
    DELETE FROM reposicion_detalle
    WHERE idreposicion_detalle = ?
");
$del->execute([$idDetalle]);

/* =========================
   AUDITORÍA: ELIMINAR DETALLE
========================= */
auditoria(
    $conexion,
    'DELETE',
    'reposiciones',
    'reposicion_detalle',
    $idDetalle,
    'Eliminó ítem de reposición',
    $antesDetalle,
    null,
    $idReposicion,       // 👈 afectó a esta reposición
    'reposicion'
);

/* =========================
   VERIFICAR SI QUEDAN ÍTEMS
========================= */
$check = $conexion->prepare("
    SELECT COUNT(*) 
    FROM reposicion_detalle
    WHERE reposicion_idreposicion = ?
");
$check->execute([$idReposicion]);
$cantidad = (int)$check->fetchColumn();

/* =========================
   SI NO QUEDAN ÍTEMS → CANCELAR
========================= */
if ($cantidad === 0) {

    /* estado antes */
    $antesRepo = [
        'estado' => 'pedido'
    ];

    $upd = $conexion->prepare("
        UPDATE reposicion
        SET estado = 'cancelado'
        WHERE idreposicion = ?
    ");
    $upd->execute([$idReposicion]);

    /* auditoría cancelación automática */
    auditoria(
        $conexion,
        'UPDATE',
        'reposiciones',
        'reposicion',
        $idReposicion,
        'Canceló automáticamente la reposición (sin ítems)',
        $antesRepo,
        ['estado' => 'cancelado'],
        $idReposicion,
        'reposicion'
    );

    echo json_encode([
        'ok' => true,
        'pedido_cancelado' => true
    ]);
    exit;
}

/* =========================
   RESPUESTA FINAL
========================= */
echo json_encode(['ok' => true]);
