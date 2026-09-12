<?php
require_once '../settings/bootstrap.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? null;

if (!$id) {
    echo json_encode(['ok' => false, 'error' => 'ID inválido']);
    exit;
}

/* =========================
   OBTENER ESTADO ANTES
========================= */
$antesStmt = $conexion->prepare("
    SELECT idreposicion, estado
    FROM reposicion
    WHERE idreposicion = ?
");
$antesStmt->execute([$id]);
$antes = $antesStmt->fetch(PDO::FETCH_ASSOC);

if (!$antes) {
    echo json_encode(['ok' => false, 'error' => 'Reposición no encontrada']);
    exit;
}

/* =========================
   VALIDAR QUE SE PUEDA CANCELAR
========================= */
if ($antes['estado'] !== 'pedido') {
    echo json_encode([
        'ok' => false,
        'error' => 'Solo se pueden cancelar reposiciones en estado pedido'
    ]);
    exit;
}

/* =========================
   ACTUALIZAR ESTADO
========================= */
$upd = $conexion->prepare("
    UPDATE reposicion
    SET estado = 'cancelado'
    WHERE idreposicion = ?
");
$upd->execute([$id]);

/* =========================
   AUDITORÍA
========================= */
$despues = [
    'estado' => 'cancelado'
];

auditoria(
    $conexion,
    'UPDATE',
    'reposiciones',
    'reposicion',
    $id,
    'Canceló la reposición',
    $antes,
    $despues,
    'reposicion',     // 👈 afectado_tabla
    $id               // 👈 afectado_id
);

/* =========================
   RESPUESTA
========================= */
echo json_encode(['ok' => true]);
