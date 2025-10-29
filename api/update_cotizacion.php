<?php
include '../conexion/conexion.php';
header('Content-Type: application/json; charset=utf-8');

$usd_ars = $_POST['usd_ars'] ?? null;
$usd_pyg = $_POST['usd_pyg'] ?? null;
$ars_pyg = $_POST['ars_pyg'] ?? null;

if (!$usd_ars || !$usd_pyg || !$ars_pyg) {
  echo json_encode(['ok' => false, 'msg' => 'Datos incompletos']);
  exit;
}

$stmt = $conexion->prepare("INSERT INTO cotizacion (usd_ars, usd_pyg, ars_pyg, fuente) VALUES (?, ?, ?, 'Manual')");
$ok = $stmt->execute([$usd_ars, $usd_pyg, $ars_pyg]);
echo json_encode(['ok' => $ok]);
