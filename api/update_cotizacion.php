<?php
session_start();
include '../conexion/conexion.php';
require_once '../settings/auditoria.php';

header('Content-Type: application/json; charset=utf-8');

$usd_ars = $_POST['usd_ars'] ?? null;
$usd_pyg = $_POST['usd_pyg'] ?? null;
$ars_pyg = $_POST['ars_pyg'] ?? null;

if ($usd_ars === null || $usd_pyg === null || $ars_pyg === null) {
    echo json_encode(['ok' => false, 'msg' => 'Datos incompletos']);
    exit;
}

/* Normalizar coma a punto */
$usd_ars = str_replace(',', '.', $usd_ars);
$usd_pyg = str_replace(',', '.', $usd_pyg);
$ars_pyg = str_replace(',', '.', $ars_pyg);

/* ✅ Validación correcta */
if (!is_numeric($usd_ars) || !is_numeric($usd_pyg) || !is_numeric($ars_pyg)) {
    echo json_encode([
        'ok' => false,
        'msg' => 'Formato numérico inválido',
        'debug' => ['usd_ars' => $usd_ars, 'usd_pyg' => $usd_pyg, 'ars_pyg' => $ars_pyg]
    ]);
    exit;
}

$stmt = $conexion->prepare("
    INSERT INTO cotizacion (usd_ars, usd_pyg, ars_pyg, fuente)
    VALUES (?, ?, ?, 'Manual')
");

$ok = $stmt->execute([$usd_ars, $usd_pyg, $ars_pyg]);

if (!$ok) {
    $err = $stmt->errorInfo();
    echo json_encode(['ok' => false, 'msg' => 'Error BD', 'debug' => $err]);
    exit;
}

/* Auditoría */
$id = $conexion->lastInsertId();

auditoria(
    $conexion,
    "INSERT",
    "cotizacion",
    "cotizacion",
    $id,
    "Nueva cotización cargada | USD/ARS: $usd_ars | USD/PYG: $usd_pyg | ARS/PYG: $ars_pyg"
);

echo json_encode(['ok' => true]);
