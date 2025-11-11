<?php
require_once '../conexion/conexion.php';
header('Content-Type: application/json; charset=UTF-8');

// Clasificación por estado: óptimo (> mínimo), bajo (1..mínimo), sin (0)
$sql = "
  SELECT 
    SUM(CASE WHEN sp.cantidad_actual = 0 THEN 1 ELSE 0 END) AS sin,
    SUM(CASE WHEN sp.cantidad_actual > 0 AND sp.cantidad_actual <= sp.stock_minimo THEN 1 ELSE 0 END) AS bajo,
    SUM(CASE WHEN sp.cantidad_actual > sp.stock_minimo THEN 1 ELSE 0 END) AS optimo
  FROM stock_producto sp
";
$rows = $conexion->query($sql)->fetch(PDO::FETCH_ASSOC) ?: ['optimo'=>0,'bajo'=>0,'sin'=>0];

echo json_encode([
  'optimo'=>(int)$rows['optimo'],
  'bajo'=>(int)$rows['bajo'],
  'sin'=>(int)$rows['sin']
]);
