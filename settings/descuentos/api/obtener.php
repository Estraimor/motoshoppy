<?php
require_once '../../../conexion/conexion.php';

header('Content-Type: application/json; charset=utf-8');

$id = $_GET['id'] ?? null;

// Respuesta por defecto (para que NUNCA salga "undefined")
$resp = [
  "id" => "",
  "nombre_lista" => "",
  "porcentaje_descuento" => "",
  "activo" => 0
];

try {

  if (!$id || !is_numeric($id)) {
    echo json_encode($resp);
    exit;
  }

  $id = (int)$id;

  $stmt = $conexion->prepare("
    SELECT id, nombre_lista, porcentaje_descuento, activo
    FROM precio_lista
    WHERE id = ?
    LIMIT 1
  ");
  $stmt->execute([$id]);

  $data = $stmt->fetch(PDO::FETCH_ASSOC);

  if ($data) {
    // Devolvemos PLANO, tal como tu JS espera
    echo json_encode($data);
  } else {
    // Si no existe, devolvemos el objeto vacío para evitar undefined
    echo json_encode($resp);
  }

} catch (Throwable $e) {
  // Ante error, devolvemos vacío para que no rompa el modal
  echo json_encode($resp);
}