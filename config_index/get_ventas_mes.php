<?php
require_once '../conexion/conexion.php';
header('Content-Type: application/json; charset=UTF-8');

// Ventas por mes del año actual (cantidad de ventas o total $)
$anio = date('Y');

// Cambiá "COUNT(*)" por "SUM(v.total)" si querés el total facturado por mes.
$sql = "
  SELECT LPAD(MONTH(fecha),2,'0') AS mes, COUNT(*) AS total
  FROM ventas
  WHERE YEAR(fecha) = :anio
  GROUP BY MONTH(fecha)
  ORDER BY MONTH(fecha)
";
$stmt = $conexion->prepare($sql);
$stmt->execute([':anio' => $anio]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Labels con meses 01..12 para que Chart.js no se rompa si faltan meses
$meses = ['01','02','03','04','05','06','07','08','09','10','11','12'];
$map = array_fill_keys($meses, 0);
foreach ($rows as $r) $map[$r['mes']] = (float)$r['total'];

$labels = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
echo json_encode(['meses'=>$labels, 'totales'=>array_values($map)], JSON_UNESCAPED_UNICODE);
