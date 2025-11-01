<?php
require_once '../conexion/conexion.php';
header('Content-Type: application/json');

$dni = $_GET['dni'] ?? '';
if (!$dni) {
  echo json_encode(['ok' => false, 'msg' => 'DNI vacío']);
  exit;
}

$stmt = $conexion->prepare("SELECT * FROM clientes WHERE dni = ? LIMIT 1");
$stmt->execute([$dni]);
$cli = $stmt->fetch(PDO::FETCH_ASSOC);

if ($cli) {
  echo json_encode(['ok' => true, 'cliente' => $cli]);
} else {
  echo json_encode(['ok' => false, 'msg' => 'No encontrado']);
}
